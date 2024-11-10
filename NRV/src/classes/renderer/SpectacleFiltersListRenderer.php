<?php

namespace nrv\renderer;

use nrv\repository\NrvRepository;

class SpectacleFiltersListRenderer {

    public function render() : string {
        // Récupère la liste des styles de spectacles
        $repo = NrvRepository::getInstance();
        $styles = $repo->getListeStyleSpectacle();

        // Génère le code HTML
        $html = <<<FIN
            <select name="style" onchange="location = this.value;">;
                <option value="">Choisissez un style</option>
        FIN;

        foreach ($styles as $style) {
            $style = $style['style'];
            $html .= <<<FIN
                    <option value="?action=display-spectacles-by-style&style=$style">$style</option>

            FIN;
        }

        $html .= "</select>";

        return $html;
    }

}