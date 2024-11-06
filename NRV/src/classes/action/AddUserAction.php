<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;

/**
 * Action pour l'inscription d'un utilisateur.
 */
class AddUserAction extends Action {

    /**
     * Méthode exécutée en cas de requête GET.
     * @return string Le formulaire d'inscription.
     */
    public function executeGet(): string {
        // Formulaire d'inscription
        return <<<FIN
        <h2 class="p-2">Inscription</h2>
                <hr>
                <form class="row g-3" action="?action=add-user" method="post">
                <div class="col-sm-12 col-lg-3">
                    <label for="email" class="mb-2">Email<span class="text-danger">*</span>: </label>
                    <input class="form-control" type="email" placeholder="exemple@mail.com" name="email" id="email" required>
                </div>
                <div class="col-sm-12 col-lg-3">    
                    <label for="mdp" class="mb-2">Mot de passe<span class="text-danger">*</span>: </label>
                    <input class="form-control" type="password" placeholder="Mot de passe" name="mdp" id="mdp" required>
                    <small class="form-text text-muted">
                        Le mot de passe doit contenir au moins 10 caractères.
                    </small>
                </div>
                <div>
                    <input type="submit" class="btn btn-primary" value="Inscription">
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
        $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
        $passwd = filter_var($_POST["mdp"], FILTER_SANITIZE_STRING);
        try {
            AuthnProvider::register($email, $passwd);
            $html = <<<FIN
            <div class="alert alert-success my-5" role="alert">
                Inscription réussie ! Vous pouvez maintenant vous <a href="?action=sign-in">connecter</a>.
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