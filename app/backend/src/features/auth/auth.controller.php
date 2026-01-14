<?php namespace Main\Feature\Auth;

    use DateTime;

    use Data\Validator;
    use Form\FormBuilder;
    use Network\HttpMethod;
    use Network\Cookie;
    use Network\Request;
    use Network\Response;
    use Feature\Auth\AuthService;

    use function Random\{gernerateRandomCode};
    use function Network\{fail, respond};


    class AuthController {
        public static function register(?AuthService $service = null) { 
            $passwordRegexp = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{4,8}$/";
            $form = FormBuilder::group([
                "email" => FormBuilder::control("", [Validator::isEmail()]),
                "password" => FormBuilder::control("", [Validator::matchWith($passwordRegexp)]),
                "csrf" => FormBuilder::control("", []),
                "honney" => FormBuilder::control("", []),
            ]);
            return function(Request $req, Response $res) use ($form) {
                function render($req, $res, $form) {
                    $csrf = gernerateRandomCode();
                    $form->getControl("csrf")->setValue($csrf);
                    $expires = new DateTime();
                    $expires->setTimestamp(time() + 60 * 60);
                    $options = Cookie::options([ "httpOnly" => true, "expires" => $expires ]);
                    $res->cookies[] = new Cookie("csrf_token", $csrf, $options);
                    $res->status->code = 200;
                    $res->body = AuthView::register($form);
                    return $res;
                }

                if($req->method == HttpMethod::GET) return respond(render($req, $res, $form));
                if($req->method == HttpMethod::POST) { 
                    $csrf = $req->cookies["csrf_token"] ?? "";
                    $form->getControl("csrf")->addValidator(Validator::matchWith("/^{$csrf}$/"));
                    $form->setValue($req->form());
                    if($form->invalid()) {
                        if($form->getControl("csrf")->invalid()) {
                            $res->status->code = 302;
                            $res->headers->set("Location", "/register");
                        } else {
                            $form->reset((object) [ "validate" => false ]);
                            $res = render($req, $res, $form);
                            $res->status->code = 400;
                        }
                    } else {
                        $res->status->code = 302;
                        $service->signUpWithEmailPassword($data->email, $data->password);
                        $res->headers->set("Location", "/login");
                    }
                    return respond($res);
                }
            };
        }

        public static function login(?AuthService $service = null) {
            $form = FormBuilder::group([
                "email" => FormBuilder::control("", [Validator::isEmail()]),
                "password" => FormBuilder::control("", [Validator::isString()]),
            ]);
            return function(Request $req, Response $res) use ($form) {
                if($req->method == HttpMethod::POST) {
                    $form->setValue($req->form());
                    if($form->invalid()) {
                        $form->reset();
                        $res->status->code = 400;
                        $res->body = AuthView::login($form);
                    } else {
                        $result = $service->signInWithEmailPassword($data->email, $data->password);
                        $res->cookies[] = $this->generateSessionCookie($result->access_token, $result->expires);
                        $res->status->code = 302;
                        $res->headers->set("Location", "/");
                    }
                    return respond($res);
                }
                if($req->method == HttpMethod::GET) { 
                    $res->status->code = 200;
                    $res->body = AuthView::login($form);
                    return respond($res);    
                }            
            };
        }

        public static function logout() {
            return function(Request $req, Response $res) {
                $cookie = $req->cookie["__host_token"];
                if($cookie != null) $res->cookies[] = $this->generateSessionCookie($cookie, time());
                $res->status->code = 307;
                $res->headers->set("Location", "/login");
                return respond($res);
            };
        }

        private function generateSessionCookie(string $token, int $expires): Cookie {
            $expires = new DateTime();
            $expires->setTimestamp($expires); 
            $options = Cookie::options([ "httpOnly" => true, "expires" => $expires ]);
            return new Cookie("__host_token", $token, $options); 
        }
    }
