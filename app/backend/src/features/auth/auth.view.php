<?php namespace Main\Feature\Auth;

    use Form\FormGroup;
    use Main\UI\MessageComponent; 
    use Main\UI\MessageType; 
    use Main\UI\ViewComponent;
    use Main\UI\FormControlComponent;


    class AuthView { 
        public static function login(FormGroup $form): string {
            $email = new FormControlComponent()
                ->label("Email")
                ->type("email")
                ->name("email")
                ->attribute("placeholder","Enter your email")
                ->attribute("required", true)
                ->value($form->getControl("email")->getValue())
                ->errors($form->getControl("email")->getErrors());
            $password = new FormControlComponent()
                ->label("Mot de passe")
                ->type("password")
                ->name("password")
                ->attribute("required", true)
                ->value($form->getControl("password")->getValue())
                ->errors($form->getControl("password")->getErrors());

            return new ViewComponent()
                ->title("Se connecter")
                ->render(<<<HTML
                    <form action="login" method="POST">
                        {$email->render()}
                        {$password->render()}
                        <button type="submit">Connexion</button>
                        <a href="register"><button type="button">S'enregistrer</button></a> 
                    </form>
                HTML);
        }
        
        public static function register(FormGroup $form): string {
            $honney = "<input type=\"hidden\" name=\"honney\" />";    
            $csrf = "<input type=\"hidden\" name=\"csrf\" value=\"{$form->getControl("csrf")->getValue()}\" />";
            $email = new FormControlComponent()
                ->label("Email")
                ->type("email")
                ->name("email")
                ->attribute("placeholder","Enter your email")
                ->attribute("required", true)
                ->value($form->getControl("email")->getValue())
                ->errors($form->getControl("email")->getErrors());
            $password = new FormControlComponent()
                ->label("Mot de passe")
                ->type("password")
                ->name("password")
                ->attribute("required", true)
                ->value($form->getControl("password")->getValue())
                ->errors($form->getControl("password")->getErrors());

            return new ViewComponent()
                ->title("S'enregistrer")
                ->render(<<<HTML
                    <form action="register" method="POST">
                        {$csrf}
                        {$email->render()}
                        {$password->render()}
                        {$honney}        
                        <button type="submit">S'enregistrer</button>
                        <a href="login"><button type="button">Se connecter</button></a> 
                    </form>
                HTML);
        }
    }
