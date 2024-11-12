<?php

namespace nrv\action;

use nrv\auth\User;
use nrv\exception\InvalidPropertyNameException;

/**
 * Action permettant de se déconnecter
 */
class SignOutAction extends Action {

    /**
     * Méthode exécutée lors d'une requête GET
     * @return string le formulaire de déconnexion
     * @throws InvalidPropertyNameException
     */
    public function executeGet(): string {
        $check = $this->checkUser(User::STANDARD_USER);
        if ($check !== "") {
            return $check;
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
     * @throws InvalidPropertyNameException
     */
    public function executePost(): string {
        $check = $this->checkUser(User::STANDARD_USER);
        if ($check !== "") {
            return $check;
        }

        // Déconnexion
        session_destroy();
        header('Location:index.php');
        return <<<FIN
        <div class="alert alert-success my-5" role="alert">
            Vous avez bien été déconnecté.
        FIN;
    }
}