<?php namespace Main\Feature\Auth;

    use Feature\Auth\Models\User;
    use Feature\Auth\AuthMailer as Mailer;

    class AuthMailer implements Mailer { 
        
        public function sendWelcomeEmail(User $user): void { }
        public function sendPasswordReset(User $user): void { } 
        public function sendConfirmationPasswordChange(User $user): void { }

    }