<?php namespace Feature\Auth\Errors;

    use Exception;

    abstract class AuthError extends Exception {}

    class UserAlreadyExist extends AuthError {}
    
    class UserNotFound extends AuthError {}

    class PasswordOrEmailDoesntMatch extends AuthError {}
