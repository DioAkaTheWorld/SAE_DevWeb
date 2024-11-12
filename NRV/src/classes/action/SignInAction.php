<?php
declare(strict_types=1);

namespace nrv\action;

use nrv\auth\AuthnProvider;
use nrv\exception\AuthnException;

/**
 * Action permettant de se connecter
 */
class SignInAction extends Action {

    /**
     * Méthode exécutée lors d'une requête GET
     * @return string le formulaire de connexion
     */
    public function executeGet(): string {
        // Vérification de l'authentification
        if (AuthnProvider::isSignedIn()) {
            return <<<FIN
            <div class="alert alert-info my-5" role="alert">
                    Vous êtes déjà connecté
            FIN;
        }

        // Formulaire de connexion
        return <<<FIN
        <h2 class="p-2">Se connecter</h2>
                <hr>
                <form class="row g-3" action="?action=sign-in" method="POST">
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
     * Méthode exécutée lors d'une requête POST
     * @return string le message de connexion ou le formulaire de connexion avec un message d'erreur
     */
    public function executePost(): string{
        // Vérification de l'authentification
        if (AuthnProvider::isSignedIn()) {
            return <<<FIN
            <div class="alert alert-info my-5" role="alert">
                    Vous êtes déjà connecté
            FIN;
        }

        // Traitement de la connexion
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