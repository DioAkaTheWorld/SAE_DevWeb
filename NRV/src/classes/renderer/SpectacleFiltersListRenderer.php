<?php

namespace nrv\renderer;

use nrv\repository\NrvRepository;

class SpectacleFiltersListRenderer {

    public function render() : string {
        // Récupère la liste des styles de spectacles
        $repo = NrvRepository::getInstance();
        $styles = $repo->getListeStyleSpectacle();
        $dates = $repo->getListeDateSpectacle();
        $lieus = $repo->getListeLieuSpectacle();

        $html = <<<FIN
            <h5>Filtrer les spectacles</h5>
            <select class="form-select" name="filtre" onchange="location = this.value;">;
            <option value="?action=display-all-spectacles">Ne pas filtrer</option>

        FIN;

        // Ajoute les options de date
        $html .= <<<FIN
            <option value="?action=display-all-spectacles">-- par date --</option>
        FIN;
        foreach ($dates as $date) {
            $date = $date['date'];
            $dateFormatted = date('d/m/Y', strtotime($date));
            $html .= <<<FIN
                    <option value="?action=display-spectacles-by-date&date=$date">$dateFormatted</option>

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

        // Ajoute les options de lieu
        $html .= <<<FIN
            <option value="?action=display-all-spectacles">-- par lieu --</option>
        FIN;
        foreach ($lieus as $lieu) {
            $lieu = $lieu['nom'];
            $html .= <<<FIN
                    <option value="?action=display-spectacles-by-lieu&lieu=$lieu">$lieu</option>

            FIN;
        }

        $html .= "</select>";

        return $html;
    }

}