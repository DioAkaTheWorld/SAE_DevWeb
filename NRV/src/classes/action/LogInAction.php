<?php
declare(strict_types=1);

namespace nrv\action;

use nrv\auth\AuthnProvider;
use nrv\exception\AuthnException;

/**
 * Action to log in
 */
class LogInAction extends Action {

    /**
     * Shows the login form
     * @return string The HTML code of the login form
     */
    public function executeGet(): string {
        // Check if the user is already signed in
        if (AuthnProvider::isSignedIn()) {
            return <<<FIN
            <div class="alert alert-info my-5" role="alert">
                    Vous êtes déjà connecté
            FIN;
        }

        return <<<FIN
        <h2 class="p-2">Se connecter</h2>
                <hr>
                <form class="row g-3" action="?action=log-in" method="POST">
                    <div class="col-sm-12 col-lg-3">
                        <label for="email" class="mb-2">Email<span class="text-danger">*</span>: </label>
                        <input class="form-control" type="email" placeholder="exemple@mail.com" name="email" id="email" required>
                    </div>
                    <div class="col-sm-12 col-lg-3">
                        <label for="mdp" class="mb-2">Mot de passe<span class="text-danger">*</span>: </label>
                        <input class="form-control" type="password" placeholder="Mot de passe" name="mdp" id="mdp" required>
                    </div>
                    <div>
                        <input type="submit" class="btn btn-primary" value="Connexion">
                    </div>
                </form>
        FIN;
    }

    /**
     * Processes the login form
     * @return string The HTML code of the result
     */
    public function executePost(): string{
        // Check if the user is already signed in
        if (AuthnProvider::isSignedIn()) {
            return <<<FIN
            <div class="alert alert-info my-5" role="alert">
                    Vous êtes déjà connecté
            FIN;
        }

        // Check if the email and password are provided and valid
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $mdp = filter_var($_POST['mdp'], FILTER_SANITIZE_STRING);
        try {
            AuthnProvider::signin($email, $mdp);
            $html = <<<FIN
            <div class="alert alert-success my-5" role="alert">
                Vous êtes connecté
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