<?php namespace Feature\Auth\Errors;

    use Exception;

    abstract class UserError extends Exception {}

    class UserAlreadyExist extends UserError {}
    
    class UserNotFound extends UserError {}

    class PasswordOrEmailDoesntMatch extends UserError {}
