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
 * Abstract class Action that represents an action to be executed
 */
abstract class Action {

    /** @var string|mixed|null HTTP method */
    protected ?string $http_method = null;
    /** @var string|mixed|null domain name */
    protected ?string $hostname = null;
    /** @var string|mixed|null script name */
    protected ?string $script_name = null;

    /**
     * Constructor
     */
    public function __construct(){
        $this->http_method = $_SERVER['REQUEST_METHOD'];
        $this->hostname = $_SERVER['HTTP_HOST'];
        $this->script_name = $_SERVER['SCRIPT_NAME'];
    }

    /**
     * Magic method to call the object as a function
     * @return string the result of the execution to be sent in the html
     */
    public function __invoke() : string {
        return $this->execute();
    }

    /**
     * Execute the action
     * @return string the result of the execution to be sent in the html
     */
    public function execute() : string {
        if ($this->http_method === 'GET') {
            return $this->executeGet();
        } else {
            return $this->executePost();
        }
    }

    /**
     * Check if the user is connected and has the right role
     * @param int $role the role to check
     * @return string the error message if any
     * @throws InvalidPropertyNameException
     */
    protected function checkUser(int $role) : string {
        // Test the connection
        if (!AuthnProvider::isSignedIn()) {
            http_response_code(401);
            return <<<FIN
            <div class="container d-flex flex-column justify-content-center align-items-center h3">
                        <h2 class="h1">Erreur 401</h2>
                        Vous n'êtes pas connecté
                    </div>
            FIN;
        }

        // Test the role
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