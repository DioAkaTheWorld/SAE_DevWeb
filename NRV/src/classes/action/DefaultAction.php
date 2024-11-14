<?php
declare(strict_types=1);

namespace nrv\action;

/**
 * Default action
 */
class DefaultAction extends Action {

    /**
     * Home page
     * @return string HTML of the home page
     */
    public function executeGet(): string {
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
     * Home page
     * @return string HTML of the home page
     */
    public function executePost(): string{
        return $this->executeGet();
    }
}