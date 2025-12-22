<?php namespace Main\Feature\Auth;

    use DateTime;

    use Prelude\Validator;
    use Network\HttpMethod;
    use Network\Cookie;
    use Network\Request;
    use Network\Response;
    use Feature\Auth\AuthService;
    use function Network\{fail, respond};


    class AuthController {
        public static function register(?AuthService $service = null) { 
            return function(Request $req, Response $res) {
                if(!in_array($req->method, [HttpMethod::GET, HttpMethod::POST])) return; 
                $data = $req->method == HttpMethod::POST ? $req->form() : null;
                $messages = null;
                
                if($data != null) {
                    $schema = [ 
                        "email" => [Validator::isEamil()],
                        "password" => [Validator::matchWith("/^(?=.*/\d)(?=.*[a-z])(?=.*[A-Z]).{4,8}$/")] // <- simple owasp pattern
                    ];
                    $messages = Validator::validate($schema, $data);
                }

                if($req->method == HttpMethod::GET || $messages != null) {
                    $res->status->code = 200;
                    $res->body = AuthView::register($messages);
                    return respond($res);
                }

                $service->signUpWithEmailPassword($data->email, $data->password);
                $res->status->code = 302;
                $res->headers->set("Location", "/login");
                return respond($res);
            };
        }

        public static function login(?AuthService $service = null) { 
            return function(Request $req, Response $res) {
                if(!in_array($req->method, [HttpMethod::GET, HttpMethod::POST])) return; 

                $data = $req->method == HttpMethod::POST ? $req->form() : null;
                $messages = null;
                if($data != null) {
                    $schema = [ 
                        "email" => [Validator::isEamil()],
                        "password" => [Validator::isString()]
                    ];
                    $messages = Validator::validate($schema, $data);
                }

                if($req->method == HttpMethod::GET || $messages != null) {
                    $res->status->code = 200;
                    $res->body = AuthView::login();
                    return respond($res);
                }

                
                $result = $service->signInWithEmailPassword($data->email, $data->password);
                $res->cookies[] = $this->generateSessionCookie($result->access_token, $result->expires);
                $res->status->code = 302;
                $res->headers->set("Location", "/");
            
                return respond($res);
            };
        }

        public static function logout() {
            return function(Request $req, Response $res) {
                $expires = new DateTime ();
                $cookie = $req->cookie->get("__host_token");
                if($cookie != null) $res->cookies[] = $this->generateSessionCookie($cookie, time());
                $res->status->code = 307;
                $res->headers->set("Location", "/login");
                return respond($res);
            };
        }

        private function generateSessionCookie(string $token, int $expires): Cookie { 
            $expires = DateTime::setTimestamp($expires); 
            $options = Cookie::options([ "httpOnly" => true, "expires" => $expires ]);
            return new Cookie("__host_token", $token, $options); 
        }
    }
