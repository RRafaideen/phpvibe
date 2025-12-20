<?php namespace Network;

    use Exception;
    use Data\Either\Either;
    use function Data\Either\{right, left};

    class HttpHandler { 
        public static function run(callable ...$stacks): void { 
            run(...$stacks);
        }
    }

    function stack(callable ...$handlers): callable {
        return function(Request $req, Response $res) use ($handlers): RespondType {
            foreach ($handlers as $handler) {
                $result = $handler($req, $res);
                // var_dump(get_class($result));
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
            $pathinfo = explode('/', $req->pathname);
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

    function run(callable ...$stacks): void {
        $headers = new HttpHeaders(getallheaders());
        $method = HttpMethod::fromString($_SERVER["REQUEST_METHOD"]);
        $url = parse_url($_SERVER["REQUEST_URI"]);
        $params = [];
        $queryString = [];
        parse_str($url["url"] ?? "", $queryString);
        
        $pathname = $url["path"];
        if(preg_match("/\/$/", $pathname)) $pathname = substr($pathname, 0, strlen($pathname) -1);

        $request = new Request($headers, $method, $pathname, $params, $queryString);
        $response = new Response();
        runStack($stacks, $request, $response)
            ->fold(
                fn($err) => !($err instanceof HttpException) ? throw $err : $err->toResponse(),
                fn($res) => sendResponse($res)
            );
        exit;
    }

    function respond(Response $res): RespondType {
        return new RespondSuccess($res);
    }
    
    function fail($err): RespondType { 
        return new RespondFail($err);
    } 


    class HttpException extends Exception {
        public readonly HttpStatus $status;
        public function __construct(
            string $message = "Internal server error",
            int $statusCode = 500,
            ?string $statusMessage = null
        ) {
            $this->status = new HttpStatus($statusCode, $statusMessage);
            $this->message = $message;
        }

        public function toResponse(): Response { 
            $response = new Response();
            $response->status = $this->status;
            $response->body = $this->message;
        }
    } 

    /* ---------- Internal ---------- */ 

    class Request { 
        public readonly array $vault;
        public readonly array $queryString;
        public readonly string $pathname;
        public readonly HttpHeaders $headers;
        public readonly HttpMethod $method;
        public array $params;
        
        public function __construct(
            HttpHeaders $headers,
            HttpMethod $method,
            string $pathname,
            array $params = [],
            array $queryString = [],
        ) { 
            $this->headers = $headers;
            $this->method = $method;
            $this->pathname = $pathname;
            $this->params = $params;
            $this->queryString = $queryString;
            $this->vault = [];
        }

        public function body(int $size = 2 * 1024 * 1024): string {
            $lenght = intval($this->headers->get("Content-Length") ?? "0");
            if($lenght <= 0) return null;
            if($lenght > $size) throw new HttpException("Content to large", 213);
            $body = file_get_contents("php://input");
            return is_string($body) ? $body : null;
        }
    }

    class Response { 
        public HttpStatus $status;
        public HttpHeaders $headers;
        public $body;
        public function __construct(
            HttpStatus $status = new HttpStatus(404),
            HttpHeaders $headers = new HttpHeaders()) { 
            $this->headers = $headers;
            $this->status = $status;
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
    
    
