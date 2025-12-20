<?php namespace Main;

    function libs(string $libname): string {
        $WORKSPACE_ROOT = realpath(dirname(dirname(dirname(dirname(__FILE__)))));
        return realpath($WORKSPACE_ROOT . "/libs" . $libname);
    }

    include libs("/shared/network/HttpHeaders.php");
    include libs("/shared/network/HttpStatus.php");
    include libs("/shared/network/HttpHandler.php");
    include libs("/shared/network/HttpMethod.php");
    include libs("/shared/data/Either.php");
    include libs("/features/auth/auth.service.php");
    include "Template.php";
    include "features/auth/index.php";
