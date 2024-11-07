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
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab accusantium aliquam aliquid atque cumque enim incidunt labore laudantium, molestiae, nihil nostrum numquam omnis perferendis, quibusdam quo sed temporibus vel veniam?</p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Cupiditate ea eos facere libero, nemo nobis officia quam quasi! Ab adipisci aspernatur assumenda distinctio ex hic ipsa neque omnis reprehenderit tempore.</p>
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