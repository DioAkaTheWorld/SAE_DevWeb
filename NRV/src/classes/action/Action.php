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
 * Classe absraite Action qui gère les actions GET et POST
 */
abstract class Action {

    /** @var string|mixed|null méthode HTTP */
    protected ?string $http_method = null;
    /** @var string|mixed|null nom de domaine */
    protected ?string $hostname = null;
    /** @var string|mixed|null nom du script */
    protected ?string $script_name = null;

    /**
     * Constructeur
     */
    public function __construct(){
        $this->http_method = $_SERVER['REQUEST_METHOD'];
        $this->hostname = $_SERVER['HTTP_HOST'];
        $this->script_name = $_SERVER['SCRIPT_NAME'];
    }

    /**
     * Méthode magique __invoke
     * @return string le résultat de l'exécution à renvoyer dans le html
     */
    public function __invoke() : string {
        return $this->execute();
    }

    /**
     * Exécute l'action en fonction de la méthode HTTP
     * @return string le résultat de l'exécution à renvoyer dans le html
     */
    public function execute() : string {
        if ($this->http_method === 'GET') {
            return $this->executeGet();
        } else {
            return $this->executePost();
        }
    }

    /**
     * Vérifie si l'utilisateur est authentifié et a le rôle nécessaire
     * @param int $role rôle nécessaire
     * @return string message d'erreur ou message vide
     * @throws InvalidPropertyNameException
     */
    protected function checkUser(int $role) : string {
        // Test de la connexion
        if (!AuthnProvider::isSignedIn()) {
            http_response_code(401);
            return <<<FIN
            <div class="container d-flex flex-column justify-content-center align-items-center h3">
                        <h2 class="h1">Erreur 401</h2>
                        Vous n'êtes pas connecté
                    </div>
            FIN;
        }

        // Test du rôle
        if ($role !== User::STANDARD_USER && $role !== User::STAFF && $role !== User::ADMIN) {
            http_response_code(500);
            return <<<FIN
            <div class="container d-flex flex-column justify-content-center align-items-center h3">
                        <h2 class="h1">Erreur
                        Rôle invalide
                    </div>  
            FIN;
        }

        try {
            $authz = new Authz(AuthnProvider::getSignedInUser());
            $authz->checkRole($role);
        } catch (AuthzException|AuthnException $e) {
            return "<div class='container'>Erreur : {$e->getMessage()}</div>";
        }

        return "";
    }

    abstract public function executeGet() : string;
    abstract public function executePost() : string;

}