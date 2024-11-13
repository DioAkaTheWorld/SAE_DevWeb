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
        return <<<HTML
            <section class="hero" id="home">
                <div class="hero-content">
                    <h1>NRV Rock Festival</h1>
                    <p>Bienvenue à Nancy pour le festival de rock NRV ! Un événement épique qui célèbre la passion du rock et de la musique live dans les lieux les plus emblématiques de la ville. Rejoignez-nous pour deux semaines inoubliables de performances puissantes, avec des artistes de renommée et des groupes locaux qui feront vibrer les rues de Nancy.</p>
                    <a class="btn btn-danger" href="?action=display-all-spectacles">Découvrir le programme</a>
                </div>
            </section>
            HTML;

    }

    /**
     * Méthode exécutée en cas de requête POST
     * @return string HTML de la page d'accueil
     */
    public function executePost(): string{
        return $this->executeGet();
    }
}