<?php namespace Feature\Auth\Models;    
    
    enum UserRole { 
        case ROLE_USER;
    }

    interface Profile { 
        public int $uid { get; set; }
        public ?string $firstname { get; set; }
        public ?string $lastname { get; set; }
        public string $email { get; set; }
        public int $createdAt { get; set; } // <- timestamp 
        public UserRole $role { get; set; } // <- enum maybe in db later
    }

    class User implements Profile {
        public int $uid;
        public ?string $firstname;
        public ?string $lastname;
        public string $email;
        public string $password;
        public int $createdAt; 
        public UserRole $role = UserRole::ROLE_USER;
    }
