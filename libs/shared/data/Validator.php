<?php namespace Data; 

    class Validator { 

        public static function isString(bool $optional = false) {
            return function($value) use ($optional) {
                if($optional && $value == null) return;
                if(gettype($value) == "string") return;
                return "Value should be string";
            };
        }

        public static function matchWith(string $regexp, bool $optional = false) {
            return function($value) use ($regexp, $optional) {
                if($optional && $value == null) return;
                if(preg_match($regexp, $value)) return;
                return "Value doesn't match";
            };   
        }
        
        public static function isEmail(bool $optional = false) {
            return function($value) use ($optional) {
                $regexp = "/^((?!\.)[\w\-_.]*[^.])(@\w+)(\.\w+(\.\w+)?[^.\W])$/m";
                $message = self::matchWith($regexp, $optional)($value);
                if($message != null) return "Email not valid";
            };
        }
    
        public static function validate(array $scheme, $data) {
            $errors = [];
            foreach ($scheme as $key => $validators) {
                if(!array_key_exists($key, $errors)) $errors[$key] = [];
                if(!array_key_exists($key, $data))  $errors[$key][] = "Missing property {$key}";
                $messages = array_reduce($validators, function($acc, $callable) use ($data) { 
                    $message = $callable($data->{$key});
                    if(gettype($message) != "string") return $acc;
                    $acc[] = $message;
                    return $acc;
                }, []);
                $errors[$key] = array_merge($errors[$key], $messages);
            }
            // serait plus sex avec Either...
            return count($errors) ? ((object) $errors) : null;
        }
    }

