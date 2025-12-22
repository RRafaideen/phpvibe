<?php namespace Network;
    
    use Exception;

    class HttpException extends Exception {
        public readonly HttpStatus $status;
        public function __construct(
            string $message = "Internal server error",
            int $statusCode = 500,
            ?string $statusMessage = null
        ) {
            $this->status = new HttpStatus($statusCode, $statusMessage);
            $this->message = $message;
        }

        public function toResponse(): Response { 
            $response = new Response();
            $response->status = $this->status;
            $response->body = $this->message;
        }
    } 
