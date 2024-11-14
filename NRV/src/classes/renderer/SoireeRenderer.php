<?php

namespace nrv\renderer;

use nrv\festival\Soiree;

class SoireeRenderer {

    private Soiree $soiree;

    public function __construct(Soiree $soiree) {
        $this->soiree = $soiree;
    }

    /**
     * Affiche les détails complets de la soirée, incluant les informations sur le lieu et les spectacles associés.
     *
     * @param array $lieuDetails Tableau contenant les informations sur le lieu.
     * @param array $spectacles Tableau d'objets de type spectacle associés à cette soirée.
     * @param array $artistes Tableau d'objets de type artiste associés à cette soirée.
     * @return string Le HTML généré pour l'affichage des détails de la soirée.
     */
    public function renderAsLong(array $lieuDetails, array $spectacles, array $artistes): string {
        // Condition pour éviter d'afficher 01/01/1970
        $date = $this->soiree->__get('date');
        if($date !== "Date inconnue") {
            $date = date('d/m/Y', strtotime($date));
        }
        // Détails de base de la soirée
        $html = <<<HTML
            <h1>Soirée: {$this->soiree->__get('nom')}</h1>
            <hr>
            <h2>Thématique</h2>
            <p>{$this->soiree->__get('thematique')}</p>
            <h2>Date</h2>
            <p>$date</p>
            <h2>Horaire</h2>
            <p>{$this->soiree->__get('horaire_debut')} - {$this->soiree->__get('horaire_fin')}</p>
            <h2>Tarif</h2>
            <p>{$this->soiree->__get('tarif')}€</p>
            <h2>Lieu</h2>
            <ul>
                <li><strong>Nom</strong>: {$lieuDetails['nom']}</li>
                <li><strong>Adresse</strong>: {$lieuDetails['adresse']}</li>
            </ul>
            <h2>Artistes</h2>
            <ul>
        HTML;

        // Liste des artistes
        foreach ($artistes as $artist) {
            $html .= "<li>{$artist['nom']}</li>";
        }
        $html .= "</ul>";

        // Liste des spectacles associés à la soirée
        $html .= <<<FIN
            <div>
                <h2>Spectacles</h2>
                <ul>
            
        FIN;

        $spectaclesListRenderer = new SpectaclesListRenderer();
        $html .= $spectaclesListRenderer->renderSpectacleList($spectacles);

        return $html;
    }
}
