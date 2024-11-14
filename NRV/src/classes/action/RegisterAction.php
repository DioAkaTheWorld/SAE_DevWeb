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
 * Action pour l'inscription d'un utilisateur.
 */
class RegisterAction extends Action {

    /**
     * Méthode exécutée en cas de requête GET.
     * @return string Le formulaire d'inscription.
     */
    public function executeGet(): string {
        $html = "";
        if (AuthnProvider::isSignedIn()) {
            // On ajoute un deuxième bouton pour ajouter un compte staff si l'utilisateur est un admin
            try {
                $autz = new Authz($_SESSION['user']);
                $autz->checkRole(User::ADMIN);
                $html = "<input type='submit' name='register' class='btn btn-primary mx-3' value='Inscription staff'>";
            } catch (AuthzException|InvalidPropertyNameException $e) {
                // On ne fait rien
            }
        }

        // Formulaire d'inscription
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
     * Méthode exécutée en cas de requête POST.
     * @return string un message de succès ou d'échec.
     */
    public function executePost(): string {
        // Inscription de l'utilisateur
        $action = $_POST['register']; // Correspond au nom de l'input submit utilisé
        $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
        $passwd = filter_var($_POST["mdp"], FILTER_SANITIZE_STRING);

        // Vérification du bouton submit utilisé
        if ($action === "Inscription staff") {
            $role = User::STAFF;
        } else {
            $role = User::STANDARD_USER;
        }

        try {
            AuthnProvider::register($email, $passwd, $role);
            $html = <<<FIN
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