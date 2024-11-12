<?php
declare(strict_types=1);

namespace nrv\action;

/**
 * Action par défaut
 */
class DefaultAction extends Action {

    /**
     * Méthode exécutée en cas de requête GET
     * @return string HTML de la page d'accueil
     */
    public function executeGet(): string {
        // Message de bienvenue
        return <<<FIN
        <h2 class="h3 text-center" >Bienvenue sur NRV !</h2>
                <div class="text-center my-4">
                    
                </div>
        FIN;

    }

    /**
     * Méthode exécutée en cas de requête POST
     * @return string HTML de la page d'accueil
     */
    public function executePost(): string{
        return $this->executeGet();
    }
}