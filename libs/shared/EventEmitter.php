<?php namespace Event;

    class EventEmitter { 
        private $events = [];
        
        public function on(string $event, callable $callable): callable {
            if(!array_key_exists($event, $this->events)) $this->events[$event] = [];
            $this->$events[$event][] = $callables;
            return function () use ($event, $callable): void {
                $index = array_search($callable, $this->events[$event]);
                if($index > -1) array_splice($this->events[$event], 1, $index);
            };
        }

        public function once(string $event, callable $callable): void {
            $this->on($event, $callable)();
        }

        public function emit(string $event, mixed $args = null): void {
            if(!array_key_exists($event, $this->events)) return;
            foreach ($this->events[$event] as $callable) $callable($args);
        } 
    }
