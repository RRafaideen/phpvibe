<?php namespace Main\Feature\Auth;

    use Main\UI\Template;

    class AuthView { 
        public static function login(?object $data = null): string {
            $buttons = <<<TPL
                <button type="submit">Connexion</button>
                <a href="/register">
                    <button type="button">S'enregistrer</button>
                </a> 
                TPL;
            return Template::render((object) [
                "title" => "Se connecter",
                "body" => self::authForm("/login", $buttons)
            ]);
        }
        
        public static function register(?object $data = null): string {
            $buttons = <<<TPL
                <button type="submit">S'enregister</button>
                <a href="/login">
                    <button type="button">Se connecter</button>
                </a> 
                TPL;
            return  Template::render((object) [
                "title" => "S'enregiter",
                "body" => self::authForm("/register", $buttons)
            ]);                
        }

        private static function authForm(string $action, string $buttons): string {
            return <<<TPL
                    <form action="{$action}" method="post">
                        <label>
                            <span>Email : <span>*</span></span>
                            <input name="email" type="email" placeholder="Enter your email" required/>
                        </label>
                        <label>
                            <span>Password : <span>*</span></span>
                            <input name="password" type="password" required/>
                        </label>
                        {$buttons}
                    </form>
                TPL;   
        }
    }
