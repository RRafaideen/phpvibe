<?php namespace Feature\Auth\Models;    

    interface Profile { 
        public int $uid { get; set; }
        public ?string $firstname { get; set; }
        public ?string $lastname { get; set; }
        public string $email { get; set; }
        public int $createdAt { get; set; } // <- timestamp 
    }

    class User implements Profile {
        public int $uid;
        public ?string $firstname;
        public ?string $lastname;
        public string $email;
        public string $password;
        public int $createdAt; 
    }
