<?php namespace Data\Either;

    abstract class Either {
        protected $value;
        abstract public function map(callable $callable): Either;
        abstract public function then(callable $callable): Either;
        public function __construct($value) {
            $this->pure($value);
        }
        public function pure($value): Either {
            if(!is_callable($value)) $this->value = fn() => $value;
            else $this->value = $value;
            return $this;
        }
        public function fold(callable $left,  callable $right): mixed {
            $fn = $this->value;
            if($this instanceof Left) return $left($fn()); 
            if($this instanceof Right) return $right($fn());
        }
    }

    class Left extends Either {
        public function map(callable $callable): Either {
            return $this;
        }
        public function then(callable $callable): Either {
            return $this;
        }
    }

    class Right extends Either {
        public function map(callable $callable): Either {
            return new Right(fn() => $callable(($this->value)()));
        }
        public function then(callable $callable): Either {
            return new Right(fn() => $callable(($this->value)()))
                        ->fold(fn($left) => $left, fn($right) => $right);
        }
    }
    
    function right($value): Either {
        return new Right($value);
    }

    function left($value): Either { 
        return new Left($value);
    }


