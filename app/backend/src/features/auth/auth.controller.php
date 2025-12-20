<?php namespace Main\Feature\Auth;


    use Network\Request;
    use Network\Response;
    use Feature\Auth\AuthService;
    use function Network\{fail, respond};


    class AuthController {
        public static function register(?AuthService $service = null) { 
            return function(Request $req, Response $res) {
                $res->body = AuthView::register();
                return respond($res);
            };
        }
        public static function login(?AuthService $service = null) { 
            return function(Request $req, Response $res) {
                $res->body = AuthView::login();
                return respond($res);
            };
        }
        public static function logout(?AuthService $service = null) { 
            return function(Request $req, Response $res) {
                $res->body = "Not implemented";
                return respond($res);
            };
        }
    }
