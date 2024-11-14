<?php
declare(strict_types=1);

namespace nrv\action;

use nrv\auth\AuthnProvider;
use nrv\auth\Authz;
use nrv\auth\User;
use nrv\exception\AuthnException;
use nrv\exception\AuthzException;
use nrv\exception\InvalidPropertyNameException;

/**
 * Action to register a new user
 */
class RegisterAction extends Action {

    /**
     * Shows the registration form
     * @return string The HTML code of the registration form
     */
    public function executeGet(): string {
        $html = "";
        if (AuthnProvider::isSignedIn()) {
            // We check if the user is an admin to display the button to register a staff member
            try {
                $autz = new Authz($_SESSION['user']);
                $autz->checkRole(User::ADMIN);
                $html = "<input type='submit' name='register' class='btn btn-primary mx-3' value='Inscription staff'>";
            } catch (AuthzException|InvalidPropertyNameException) {
                // Nothing to do
            }
        }

        return <<<FIN
        <h2 class="p-2">Inscription</h2>
                <hr>
                <form class="row g-3" action="?action=register" method="post">
                <div class="col-sm-12 col-lg-3">
                    <label for="email" class="mb-2">Email<span class="text-danger">*</span>: </label>
                    <input class="form-control" type="email" placeholder="exemple@mail.com" name="email" id="email" required>
                </div>
                <div class="col-sm-12 col-lg-3">    
                    <label for="mdp" class="mb-2">Mot de passe<span class="text-danger">*</span>: </label>
                    <input class="form-control" type="password" placeholder="Mot de passe" name="mdp" id="mdp" required>
                    <small class="form-text text-muted">
                        Au minimum 10 ćaractères, 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial
                    </small>
                </div>
                <div>
                    <input type="submit" name="register" class="btn btn-primary" value="Inscription">
                    $html
                </div>
                </form>
        FIN;
    }

    /**
     * Processes the registration form
     * @return string The HTML code of the result
     */
    public function executePost(): string {
        $html = "";
        if (AuthnProvider::isSignedIn()) {
            // We check if the user is an admin to display the button to register a staff member
            try {
                $autz = new Authz($_SESSION['user']);
                $autz->checkRole(User::ADMIN);
                $html = "<input type='submit' name='register' class='btn btn-primary mx-3' value='Inscription staff'>";
            } catch (AuthzException|InvalidPropertyNameException) {
                // Nothing to do
            }
        }

        // Validation of the email and password
        $action = $_POST['register']; // We check which button was clicked
        $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
        $passwd = filter_var($_POST["mdp"], FILTER_SANITIZE_STRING);

        // We check which role the user wants to register
        if ($action === "Inscription staff") {
            $role = User::STAFF;
        } else {
            $role = User::STANDARD_USER;
        }

        // We register the user
        try {
            AuthnProvider::register($email, $passwd, $role);
            $html .= <<<FIN
            <div class="alert alert-success my-5" role="alert">
                Inscription réussie ! Vous pouvez maintenant vous <a href="?action=log-in">connecter</a>.
            </div>
            FIN;
        } catch (AuthnException $e) {
            $html = $this->executeGet();
            $html .= <<<FIN
            <div class="alert alert-danger my-5" role="alert">
                {$e->getMessage()}
            </div>
            FIN;
        }

        return $html;
    }
}