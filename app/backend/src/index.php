<?php namespace Main; 
    include "vendor.php";

    use Network\HttpStatus;
    use Network\HttpMethod;
    use Network\HttpHandler;
    use Network\Request;
    use Network\Response;
    use Main\Feature\Auth\AuthGuard;
    use Main\Feature\Auth\AuthController;

    use function Network\{fail, respond, route, stack};

    HttpHandler::run(
        route(HttpMethod::ANY, "/", function (Request $req, Response $res) {
            $res->status = new HttpStatus(307);
            $res->headers->set("Location", "/login");
            return respond($res);
        }),
        route(HttpMethod::ANY, "/register", AuthController::register()),
        route(HttpMethod::ANY, "/login", AuthController::login()),
        route(HttpMethod::ANY, "/logout", AuthController::logout()),
        stack(
            AuthGuard::authenticated(),
            route(HttpMethod::ANY, "/profile", AuthController::register()),
        )
    );
