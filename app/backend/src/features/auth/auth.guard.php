<?php namespace Main\Feature\Auth;

    use Network\Cookie;
    use Network\Request;
    use Network\Response;
    use Network\HttpException;
    use Feature\Auth\AuthService;
    use function Network\{respond};

    class AuthGuard { 
        public static function authenticated(?AuthService $service = null) { 
            return function(Request $req, Response $res) {
                $authorization = $req->headers->get("Authorization");
                if($authorization == null) {
                    $res->status->code = 307;
                    $res->headers->set("Location", "/login");
                    return respond($res);
                }
                $fragments = explode(" ", $authorization);
                if($fragments[0] != "Bearer") throw new HttpEHttpException("Unexpected authorization header", 403);
                $req->vault["profile"] = $service->getUserFromToken($fragments[1]);
            };
        }
    }
    