<?php namespace Main\Feature\Auth;

    use Network\Request;
    use Network\Response;
    use Feature\Auth\AuthService;
    
    class AuthGuard { 
        public static function authenticated(?AuthService $service = null) { 
            return function(Request $req, Response $res) {
              
            };
        }
    }
    