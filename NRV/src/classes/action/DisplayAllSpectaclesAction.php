<?php

namespace nrv\action;

use nrv\exception\InvalidPropertyNameException;
use nrv\renderer\SpectacleFiltersListRenderer;
use nrv\renderer\SpectaclesListRenderer;

/**
 * Action to display all the spectacles
 */
class DisplayAllSpectaclesAction extends Action {

    /**
     * Displays the list of all the spectacles
     * @return string The HTML code of the list
     * @throws InvalidPropertyNameException
     */
    public function executeGet(): string {
        $filtersRenderer = new SpectacleFiltersListRenderer();
        $spectaclesListRenderer = new SpectaclesListRenderer();

        // Create the filters list
        $res = <<<FIN
        <h2 class="p-2">Liste des spectacles</h2>
        <div class="col-3">
            {$filtersRenderer->render()}
        </div>
        <hr>

        FIN;

        // Create the list of spectacles
        return $res . $spectaclesListRenderer->renderAllSpectacleList();
    }

    /**
     * Displays the list of all the spectacles
     * @return string The HTML code of the list
     * @throws InvalidPropertyNameException
     */
    public function executePost(): string {
        return $this->executeGet();
    }
}