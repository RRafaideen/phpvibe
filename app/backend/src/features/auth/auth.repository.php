<?php namespace Main\Feature\Auth;

    use Main\Datasource;
    use Feature\Auth\Models\User;
    use Feature\Auth\AuthPersistence as Persistence;
    
    class AuthRepository implements Persistence {
        private $db; 
        public function __construct(Datasource $db) {
            $this->db = $db;
        }
        
        public function storeUser(User $user): User { }
        public function getUserByEmail(string $email): ?User { }
        public function getUserById(int $uid): User { }
    }
