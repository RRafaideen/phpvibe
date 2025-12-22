<?php namespace Network;
    use DateTimeInterface;

    enum SameSite : string {
        case Lax = "Lax";
        case None = "None";
        case Strict = "Strict";
    }

    interface CookieOptions { 
        public ?DateTimeInterface $expires { get; set; }
        public ?int    $maxAge { get; set; }
        public ?string $sameSite { get; set; }
        public ?bool   $httpOnly { get; set; }
        public ?bool   $secure { get; set; }
        public ?string $path { get; set; }
        public ?string $domain { get; set; }
    }

    class Cookie {
        public string $value;
        public string $name;
        public CookieOptions $options;

        public function __construct(string $name, string $value, ?CookieOptions $options = null) {
            $this->name = $name; 
            $this->value = $value; 
            $this->options = $options ?? self::options([
                "httpOnly" => true,
                "secure" => false
            ]);
        }


        public static function options(array $options = []) {
            return new class($options) implements CookieOptions {
                public ?DateTimeInterface $expires;
                public ?int    $maxAge;            
                public ?string $sameSite;          
                public ?bool   $httpOnly;          
                public ?bool   $secure;            
                public ?string $path;              
                public ?string $domain;
                
                public function __construct($options) {
                    $this->expires = $options["expires"] ?? null;
                    $this->maxAge = $options["maxAge"] ?? null;
                    $this->sameSite = $options["sameSite"]?? null;
                    $this->httpOnly = $options["httpOnly"] ?? null;
                    $this->secure = $options["secure"] ?? null;
                    $this->path = $options["path"] ?? null;
                    $this->domain = $options["domain"] ?? null;
                }
            };
        }

        public static function render(Cookie $cookie) {
            $secure   = $cookie->options->secure   ? "Secure"                                                     : null;
            $httpOnly = $cookie->options->httpOnly ? "HttpOnly"                                                   : null;
            $sameSite = $cookie->options->sameSite ? "SameSite={$cookie->options->sameSite}"                      : null;
            $path     = $cookie->options->path     ? "Path={$cookie->options->path}"                              : null;
            $domain   = $cookie->options->domain   ? "Domain={$cookie->options->domain}"                          : null;
            $expires  = $cookie->options->expires  ? $cookie->options->expires->format(DateTimeInterface::COOKIE) : null;
            $maxAge   = $cookie->options->maxAge   ? strval($cookie->options->maxAge)                             : null;

            $options = array_filter([$sameSite, $path, $domain, $secure, $httpOnly, $expires, $maxAge], fn($x) => $x != null);
            $options = join("; ", $options);
            return "{$cookie->name}={$cookie->value}; {$options}";
        }
    }

