<?php

namespace nrv\action;

use nrv\auth\User;
use nrv\exception\InvalidPropertyNameException;

/**
 * Log out action
 */
class LogOutAction extends Action {

    /**
     * Shows the logout form
     * @return string The HTML code of the logout form
     * @throws InvalidPropertyNameException
     */
    public function executeGet(): string {
        // Check if the user is connected
        $check = $this->checkUser(User::STANDARD_USER);
        if ($check !== "") {
            return $check;
        }

        return <<<FIN
        <div class="container d-flex flex-column justify-content-center align-items-center h3">
                <h2 class="h1">Êtes-vous sûr ?</h2>
                <form action="?action=log-out" method="POST">
                    <div class="submit">
                        <input type="submit" class="btn btn-danger" value="Valider">
                    </div>
                </form>
            </div>
        FIN;
    }

    /**
     * Processes the logout form
     * @return string The HTML code of the result
     * @throws InvalidPropertyNameException
     */
    public function executePost(): string {
        // Check if the user is connected
        $check = $this->checkUser(User::STANDARD_USER);
        if ($check !== "") {
            return $check;
        }

        // Destroy the session
        session_destroy();
        header('Location:index.php');
        // Return a success message (not displayed)
        return <<<FIN
        <div class="alert alert-success my-5" role="alert">
            Vous avez bien été déconnecté.
        FIN;
    }
}