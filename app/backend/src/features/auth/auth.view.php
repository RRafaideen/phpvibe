<?php namespace Main\Feature\Auth;

    use Main\UI\Message; 
    use Main\UI\MessageType; 
    use Main\UI\Template; 
    use Main\UI\FormControl; 

    class AuthView { 
        public static function login(object $messages = new stdClass()): string {
            $buttons = <<<TPL
                <button type="submit">Connexion</button>
                <a href="/register"><button type="button">S'enregistrer</button></a> 
                TPL;
            return Template::render((object) [
                "title" => "Se connecter",
                "body" => self::authForm("/login", $buttons, $messages)
            ]);
        }
        
        public static function register(object $messages = new stdClass()): string {
            $buttons = <<<TPL
                <button type="submit">S'enregister</button>
                <a href="/login"><button type="button">Se connecter</button></a> 
                TPL;
            return  Template::render((object) [
                "title" => "S'enregiter",
                "body" => self::authForm("/register", $buttons, $messages)
            ]);                
        }

        private static function authForm(string $action, string $buttons, object $messages): string {
            $email = new FormControl("Email", "email", "email", ["placeholder" => "Enter your email", "required" => true]);
            $password = new FormControl("Mot de passe", "password", "password", ["required" => true]);
            $email = FormControl::render($email);
            $password = FormControl::render($password);

            if(property_exists($messages, "email")) $email->messages = [new Message(MessageType::Error, "Erreur de saisie sur l'email.")];
            if(property_exists($messages, "password")) $password->messages = [new Message(MessageType::Error, "Erreur de saisie sur le mot de passe.")];  

            return "<form action=\"{$action}\" method=\"post\">{$email}{$password}{$buttons}</form>";
        }
    }
