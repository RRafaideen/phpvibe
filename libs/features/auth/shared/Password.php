<?php namespace Feature\Auth\Shared;

    class Password {
        public static function hash(string $password): string {
            return password_hash($password, PASSWORD_BCRYPT);
        }
        public static function verify(string $hash, string $password): bool {
            return password_verify($password, $hash);
        }
    }

