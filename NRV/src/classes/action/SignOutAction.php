<?php

namespace iutnc\deefy\action;

/**
 * Action permettant de se déconnecter
 */
class SignOutAction extends Action {

    /**
     * Méthode exécutée lors d'une requête GET
     * @return string le formulaire de déconnexion
     */
    public function executeGet(): string {
        // Vérification de l'authentification
        if ($this->checkAuthentication() !== "") {
            return $this->checkAuthentication();
        }

        // Formulaire de déconnexion
        return <<<FIN
        <div class="container d-flex flex-column justify-content-center align-items-center h3">
                <h2 class="h1">Êtes-vous sûr ?</h2>
                <form action="?action=sign-out" method="POST">
                    <div class="submit">
                        <input type="submit" class="btn btn-danger" value="Valider">
                    </div>
                </form>
            </div>
        FIN;
    }

    /**
     * Méthode exécutée lors d'une requête POST
     * @return string le message de déconnexion
     */
    public function executePost(): string {
        // Vérification de l'authentification
        if ($this->checkAuthentication() !== "") {
            return $this->checkAuthentication();
        }

        // Déconnexion
        session_destroy();
        header('Location:Index.php');
        return <<<FIN
        <div class="alert alert-success my-5" role="alert">
            Vous avez bien été déconnecté.
        FIN;
    }
}