<?php

namespace nrv\renderer;

use nrv\repository\NrvRepository;

class SpectacleFiltersListRenderer {

    public function render() : string {
        // Récupère la liste des styles de spectacles
        $repo = NrvRepository::getInstance();
        $styles = $repo->getListeStyleSpectacle();
        $dates = $repo->getListeDateSpectacle();

        $html = <<<FIN
            <select name="filtre" onchange="location = this.value;">;

        FIN;

        // Ajoute les options de date
        $html .= <<<FIN
            <option value="?action=display-all-spectacles">Filtrer</option>
            <option value="?action=display-all-spectacles">-- par date --</option>
        FIN;
        foreach ($dates as $date) {
            $date = $date['date'];
            $html .= <<<FIN
                    <option value="?action=display-spectacles-by-date&date=$date">$date</option>

            FIN;
        }

        // Ajoute les options de style
        $html .= <<<FIN
            <option value="?action=display-all-spectacles">-- par style --</option>
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