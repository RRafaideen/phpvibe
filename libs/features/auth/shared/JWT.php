<?php namespace Feature\Auth\Shared;
    
    use Exception;

    abstract class JwtError extends Exception {}

    class JwtExpire extends JwtError {}
    
    class JwtSignatureNotMatch extends JwtError {}

    class JwtAlgorithmNotSupported extends JwtError {}

    class JwtHeader { 
        public string $type = "JWT";
        public string $algorithm = "HS256";
        public function __construct(
            string $type = "JWT", 
            string $algorithm = "HS256") {
                $this->type = $type;
                $this->algorithm = $algorithm;
                if(!in_array($this->algorithm, ["HS256", "HS512"])) throw new JwtAlgorithmNotSupported();
            }
            
        public static function getHmacAlgo(JwtHeader $header): string {
            if($header->algorithm == "HS256") return "sha256";
            if($header->algorithm == "HS512") return "sha512";
            throw new JwtAlgorithmNotSupported();
        }
    }

    class JWT {
        public static string $SECRET = "super_secret"; // <- Don't do this in production please !

        public static function sign(JWT $jwt): string {
            $algorithm = JwtHeader::getHmacAlgo($jwt->header);
            $header = json_encode((object) ["typ" => $jwt->header->type, "alg" => $jwt->header->algorithm ]);
            if($jwt->payload->{"iat"} == null) $jwt->payload->{"iat"} = time(); 
            $payload = json_encode($jwt->payload);
            $content = self::base64UrlEncode($header) .  "." . self::base64UrlEncode($payload);
            $signature = self::base64UrlEncode(hash_hmac($algorithm,  $content, JWT::$SECRET, true));
            return $content . "." . $signature;
        }

        public static function verify(string $token): object{
            $fragments = explode('.', $token);
            $header = json_decode(base64_decode($fragments[0]));
            $payload = json_decode(base64_decode($fragments[1]));
            if($payload->exp != null && $payload->exp < time()) throw new JwtExpire();
            $header = new JwtHeader($header->typ, $header->alg); // reasign with different type :(
            $algorithm = JwtHeader::getHmacAlgo($header);
            $content = $fragments[0] .  "." . $fragments[1];
            $signature = self::base64UrlEncode(hash_hmac($algorithm, $content, JWT::$SECRET, true));
            if($signature != $fragments[2]) throw new JwtSignatureNotMatch();
            return self::decode($token);
        }

        public static function decode(string $token): object {
            return json_decode(base64_decode(explode('.', $token)[1]));
        }

        
        public JwtHeader $header;
        public object $payload;

        public function __construct($payload = (object) []) {
            $this->header = new JwtHeader();
            $this->payload = $payload;
        } 

        private static function base64UrlEncode(string $str): string {
            return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($str));
        }
    }
