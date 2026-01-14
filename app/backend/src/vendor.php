<?php namespace Main;

    function libs(string $libname): string {
        $WORKSPACE_ROOT = realpath(dirname(dirname(dirname(dirname(__FILE__)))));
        return realpath($WORKSPACE_ROOT . "/libs" . $libname);
    }

    include libs("/shared/network/HttpHeaders.php");
    include libs("/shared/network/HttpStatus.php");
    include libs("/shared/network/HttpMethod.php");
    include libs("/shared/network/HttpException.php");
    include libs("/shared/network/HttpHandler.php");
    include libs("/shared/network/Cookie.php");
    include libs("/shared/data/Either.php");
    include libs("/shared/data/Validator.php");
    include libs("/shared/text/Html.php");
    include libs("/shared/EventEmitter.php");
    include libs("/shared/FormBuilder.php");
    include libs("/shared/Random.php");
    include libs("/features/auth/AuthService.php");
    include "ui/MessageComponent.php";
    include "ui/FormControlComponent.php";
    include "ui/ViewComponent.php";
    include "adapters/PDOSQLite.php";
    include "adapters/Datasource.php";
    include "features/auth/index.php";
