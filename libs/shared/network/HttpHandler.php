<?php namespace Network;

    use Data\Either\Either;
    use function Data\Either\{right, left};

    class HttpHandler { 
        public static function handle(callable ...$stacks) {
            return new class($stacks) { 
                private readonly array $stacks;
                private object $subscriptions;

                public function __construct(array $stacks) {
                    $this->stacks = $stacks;
                    $this->subscriptions = (object) ["error" => []];
                }

                public function onError(callable $callable) { 
                    $this->subscriptions->error[] = $callable;
                    return $this;
                }

                public function start(): void {
                    $headers = new HttpHeaders(getallheaders());
                    $method = HttpMethod::fromString($_SERVER["REQUEST_METHOD"]);
                    $url = parse_url($_SERVER["REQUEST_URI"]);
                    $cookies = $_COOKIE;
                    $params = [];
                    $queryString = [];
                    parse_str($url["url"] ?? "", $queryString);
                    
                    $pathname = $url["path"];
                    $request = new Request($headers, $method, $pathname, $params, $queryString, $cookies);
                    $response = new Response();
                    runStack($this->stacks, $request, $response)
                        ->fold(
                            function($err) use ($request, $response) {
                                $response = null;
                                foreach ($subscriptions->error as $callable) {
                                    $result = $callable($err, $request, $response);
                                    if($result instanceof Response) $response = $result;
                                }
                                if($response instanceof Response) return sendResponse($response);
                                throw $err; // <- not handle
                            },
                            fn($res) => sendResponse($res)
                        );
                }

            };
        }
    }

    function stack(callable ...$handlers): callable {
        return function(Request $req, Response $res) use ($handlers): RespondType {
            foreach ($handlers as $handler) {
                $result = $handler($req, $res);
                if(!($result instanceof RespondType)) $result = new RespondNext();
                if(!($result instanceof RespondNext)) return $result;
            }
            return new RespondNext();
        };
    }

    function route(HttpMethod $method, string $pattern, callable ...$handlers): callable {
        if(preg_match("/\/$/", $pattern)) $pattern = substr($pattern, 0, strlen($pattern) -1);
        $fragments = explode('/', $pattern);
        return function(Request $req, Response $res) use ($method, $pattern, $handlers, $fragments): RespondType {
            $pathname = preg_match("/\/$/", $req->pathname)
                ? substr($req->pathname, 0, strlen($req->pathname) -1)
                : $req->pathname;
            $pathinfo = explode('/', $pathname);
            $checkMethod = $method != HttpMethod::ANY && $req->method != $method;
            $checkSize = count($pathinfo) != count($fragments);
            if($checkMethod || $checkSize) return new RespondNext();
            
            foreach ($fragments as $index => $frag) {
                $value = $pathinfo[$index];
                if(!preg_match("/^:/", $frag) && strtolower($value) != strtolower($frag)) return new RespondNext();
                $key = substr($frag, 1, strlen($frag));
                $req->params[$key] = $value;
            }
            return stack(...$handlers)($req, $res);
        };
    }

    function respond(Response $res): RespondType {
        return new RespondSuccess($res);
    }
    
    function fail($err): RespondType { 
        return new RespondFail($err);
    } 

    function responseBuilder(HttpStatus $status, HttpHeaders $headers, string $body) {
        $response = new Response(); 
        $response->status = $status;
        $response->headers = $headers;
        $response->body = $body;
        return $response;
    }

    /* ---------- Internal ---------- */ 

    class Request { 
        private ?string $_body = null;
        public readonly array $vault;
        public readonly array $queryString;
        public readonly string $pathname;
        public readonly HttpHeaders $headers;
        public readonly HttpMethod $method;
        public readonly array $cookies;
        public array $params;
        
        
        public function __construct(
            HttpHeaders $headers,
            HttpMethod $method,
            string $pathname,
            array $params = [],
            array $queryString = [],
            array $cookies = [],
        ) { 
            $this->headers = $headers;
            $this->method = $method;
            $this->pathname = $pathname;
            $this->params = $params;
            $this->queryString = $queryString;
            $this->cookies = $cookies;
            $this->vault = [];
        }
 
        public function body(int $size = 2 * 1024 * 1024): string {
            if($this->_body != null) return $this->_body;
            $lenght = intval($this->headers->get("Content-Length") ?? "0");
            if($lenght <= 0) return null;
            if($lenght > $size) throw new HttpException("Content to large", 213);
            $body = file_get_contents("php://input");
            return $this->_body = (is_string($body) ? $body : null);
        }

        public function json(int $size = 2 * 1024 * 1024) {
            $contentType = $this->headers->get("Content-Type") ?? "";
            if(preg_match("/^application\/json/i", $contentType)) return json_decode($this->body($size));
            throw new HttpHttpException("Content-Type doesn't match", 400);
        }
    
        public function form(int $size = 2 * 1024 * 1024) {
            $contentType = $this->headers->get("Content-Type") ?? "";
            $data = [];
            if(!preg_match("/^application\/x-www-form-urlencoded/i", $contentType)) throw new HttpHttpException("Content-Type doesn't match", 400);
            parse_str($this->body($size), $data);   
            return $data;
        }
    }

    class Response { 
        public HttpStatus $status;
        public HttpHeaders $headers;
        public array $cookies;
        public $body;
        public function __construct(
            HttpStatus $status = new HttpStatus(404),
            HttpHeaders $headers = new HttpHeaders()) { 
            $this->headers = $headers;
            $this->status = $status;
            $this->cookies = [];
        }
    }

    abstract class RespondType {
        public $value;
        public function __construct($value) {
            $this->value = $value;
        }
    }
    class RespondSuccess extends RespondType { }
    class RespondFail extends RespondType { }
    class RespondNext extends RespondType {
        public function __construct() { 
            $this->value = null;
        }
    }

    function sendResponse(Response $res): void {
        $protocol = $_SERVER['SERVER_PROTOCOL'];
        header($protocol . " " . $res->status->code . " " .  $res->status->message);
        foreach ($res->headers as $name => $value) header($name . ": " . $value);
        foreach ($res->cookies as $cookie) header("Set-Cookie: " . Cookie::render($cookie));
        print($res->body);
        exit;
    }

    function runStack(array $fns, Request $req, Response $res, $init = new RespondNext()) {
        // custom data struct only for that ? Maybe it's a lil' overkill. 
        if(count($fns) == 0) return right($res);
        return right($init)->then(function($init) use ($fns, $req, $res) { 
            try {
                    $result = $fns[0]($req, $res);
                    if(!($result instanceof RespondType)) $result = new RespondNext(); // if you return some poop ! 💩
                    if($result instanceof RespondFail) return left($result->value);
                    if($result instanceof RespondSuccess) return right($result->value);
                    return runStack(array_slice($fns, 1), $req, $res, $result); 
                } catch(Exception $err) {
                    return left(new RespondFail($err));
                }
            });
    }
    

    
