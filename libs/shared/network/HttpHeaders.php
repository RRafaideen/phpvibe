<?php namespace Network;
    use Iterator;
    
    class HttpHeaders implements Iterator { 
        private $headers = [];
        public function __construct($headers = []) {
            $this->headers = $headers;
        }
        
        public function set(string $name, string $value): HttpHeaders {
            $this->headers[$name] = $value;
            return $this;
        }

        public function get(string $name): ?string {
            foreach (array_keys($this->headers) as $key) {
                if(preg_match("/^" . strtolower($name) . "$/i", $key))
                    return $this->headers[$key];
            }
            return null;
        }

        public function all(): array { 
            return $this->headers;
        }

        public function clear(): void { 
            $this->headers = [];
        }

        public function del(string $name): void {
            unset($this->headers[$name]);
        }

        public function rewind(): void {
            reset($this->headers);
        }

        public function current(): string {
            return current($this->headers);
        }

        public function key(): string {
            return key($this->headers);
        }

        public function next(): void {
            next($this->headers);
        }

        public function valid(): bool {
            return key($this->headers) != null;
        }
    }