<?php

namespace nrv\action;

use nrv\renderer\SpectacleFiltersListRenderer;
use nrv\renderer\SpectaclesListRenderer;

class DisplayAllSpectaclesAction extends Action {

    public function executeGet(): string {
        $filtersRenderer = new SpectacleFiltersListRenderer();
        $spectaclesListRenderer = new SpectaclesListRenderer();

        // Liste des filtres
        $res = <<<FIN
        <h2 class="p-2">Liste des spectacles</h2>
        <div>
            {$filtersRenderer->render()}
        </div>
        <hr>
        FIN;

        // Liste des spectacles
        return $res . $spectaclesListRenderer->render();

    }

    public function executePost(): string {
        return $this->executeGet();
    }
}