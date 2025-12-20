<?php namespace Network;

    enum HttpMethod : string {
        case GET = "GET";
        case POST = "POST";
        case PUT = "PUT";
        case PATCH = "PATCH";
        case DELETE = "DELETE";
        case HEAD = "HEAD";
        case OPTIONS = "OPTIONS";
        case ANY = "**";
        
        public static function fromString(string $str): HttpMethod { 
            switch(strtoupper($str)) {
                case "GET": return HttpMethod::GET;
                case "POST": return HttpMethod::POST;
                case "PUT": return HttpMethod::PUT;
                case "PATCH": return HttpMethod::PATCH;
                case "DELETE": return HttpMethod::DELETE;
                case "HEAD": return HttpMethod::HEAD;
                case "OPTIONS": return HttpMethod::OPTIONS;
                case "**": return HttpMethod::ANY;
                default: throw new Exception("Unexpected method. Cannot parse " . $str . ".");
            }
        }
    }


