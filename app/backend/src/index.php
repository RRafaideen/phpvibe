<?php namespace Main; 
    include "vendor.php";
    include "migrations.php";

    use PDO;
    
    use Form\FormBuilder;
    use Data\Validator;

    use Network\HttpStatus;
    use Network\HttpMethod;
    use Network\HttpHandler;
    use Network\HttpException;
    use Network\Request;
    use Network\Response;
    
    use Main\Feature\Auth\AuthGuard;
    use Main\Feature\Auth\AuthController;
    use Main\Feature\Auth\AuthMailer;
    use Main\Feature\Auth\AuthRepository;
    
    use Feature\Auth\AuthService;
    use Feature\Auth\Shared\JWT;

    
    use function Network\{fail, respond, route, stack};


    $pdo = PDOSQLite::open(".database.sqlite");
    applyMigrations($pdo);

    $db = new SQLDS($pdo);
    $authService = new AuthService(new AuthRepository($db), new AuthMailer());
    
    JWT::$SECRET = "be3f0504-9d14-4e57-aeeb-f4362ac4de31";

    HttpHandler::handle(
        route(HttpMethod::ANY, "/register", AuthController::register()),
        route(HttpMethod::ANY, "/login", AuthController::login()),
        route(HttpMethod::ANY, "/logout", AuthController::logout()),
        stack(
            AuthGuard::authenticated(),
            route(HttpMethod::ANY, "/", fn(Request $req, Response $res) => respond($res)),
        )
    )
    ->onError(fn($err, $req, $res) => handleError($err, $req, $res))
    ->start();

    
    function handleError($err, $req, $res) {
        switch (true) {
            case $err instanceof HttpException:
                return respond($err->toResponse());   
            default: 
                $res->status = 500;
                $res->body = "Internal server error";
                break;
        }
        return respond($res);
    }