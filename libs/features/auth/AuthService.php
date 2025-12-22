<?php namespace Feature\Auth;
    include "AuthError.php";
    include "models/User.php";
    include "shared/Password.php";
    include "shared/JWT.php";

    use Feature\Auth\Models\User;
    use Feature\Auth\Models\UserRole;
    use Feature\Auth\Models\Profile;
    use Feature\Auth\Errors\UserAlreadyExist;
    use Feature\Auth\Errors\UserNotFound;
    use Feature\Auth\Errors\PasswordOrEmailDoesntMatch;
    use Feature\Auth\Shared\Password;
    use Feature\Auth\Shared\JWT;

    class AuthService { 
        private AuthPersistence $db;
        private AuthMailer $mailer;

        public function __construct(AuthPersistence $db, AuthMailer $mailer) {
            $this->db = $db;
            $this->mailer = $mailer;
        }

        public function signUpWithEmailPassword(string $email, string $password): void {
            $exist = $this->db->getUserByEmail($email);
            if($exist != null) throw new UserAlreadyExist();
            $user = new User();
            $user->email = $email;
            $user->password = Password::hash($password);
            $user->createdAt = time();
            $this->db->storeUser($user);
            $this->mailer->sendWelcomeEmail($user);
        }

        public function signInWithEmailPassword(string $email, string $password): object {
            $user = $this->db->getUserByEmail($email);
            if($exist == null) {
                $user = new User();
                $user->password = "FAKE_HASH"; // <- prevent timming attacks
            }
            if(!Password::verify($user->password, $password)) throw new PasswordOrEmailDoesntMatch();
            $expires = time() + 60 * 60 * 1;
            $accessToken = JWT::sign($expires, (object) [ "uid" => $user->uid, "exp" => $expires ]);
            return (object) ["access_token" => $accessToken, "expires" => $expires];
        }
        

        public function changePassword(int $uid, string $password): void {
            $user = $this->getUserById($uid);
            $user->password = Password::hash($password);
            $this->db->storeUser($user);
        }

        // Todo - move into member 
        public function editProfile(Profile $profile): void {
            $user = $this->getUserById($profile->uid);
            $user->firstname = $profile->firstname;
            $user->lastname = $profile->lastname;
            $user->role = $profile->role ?? UserRole::ROLE_USER;
            $this->db->storeUser($user);
        }
        
        public function getUserFromToken(string $token): Profile { 
            return $this->getProfile(JWT::verify($token)->uid);
        }

        public function getProfile(int $uid): Profile {
            $user = $this->getUserById($uid);
            $profile = (object) [
                "uid" => $user->uid, "firstname" => $user->firstname, 
                "lastname" => $user->lastname, "email" => $user->email, 
                "createdAt" => $user->createdAt, "role" => $user->role ];
            return $profile;
        }

        public function sendPasswordResetEmail(string $email): void {  
            throw new Exception("Not implemented");
        }
        
        public function verifyPasswordResetCode(int $uid, string $code): bool { 
            throw new Exception("Not implemented");
        }

        public function confirmPasswordReset(string $code, string $email, string $password) { 
            throw new Exception("Not implemented");
        }

        private function getUserById(int $uid): User {
            $user = $this->db->getUserById($uid);
            if($user != null) return $user;
            throw new UserNotFound();
        }
    }


    interface AuthPersistence {
        public function storeUser(User $user): User;
        public function getUserByEmail(string $email): ?User;
        public function getUserById(int $uid): User ;
    }

    interface AuthMailer {
        public function sendWelcomeEmail(User $user): void;
        public function sendPasswordReset(User $user): void;
        public function sendConfirmationPasswordChange(User $user): void;
    }
