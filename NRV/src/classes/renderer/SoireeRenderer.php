<?php

namespace nrv\renderer;

use nrv\exception\InvalidPropertyNameException;
use nrv\festival\Soiree;

/**
 * SoireeRenderer class
 *
 * Generates HTML for displaying a Soiree object
 */
class SoireeRenderer {

    /** @var Soiree object to display */
    private Soiree $soiree;

    /**
     * Constructor
     * @param Soiree $soiree Soiree object to display
     */
    public function __construct(Soiree $soiree) {
        $this->soiree = $soiree;
    }

    /**
     * Renders the Soiree object as a long HTML string
     * @param array $lieuDetails Array of the location details associated with this party.
     * @param array $spectacles Array of spectacle objects associated with this party.
     * @param array $artistes Array of artist objects associated with this party.
     * @return string The HTML code of the Soiree object
     * @throws InvalidPropertyNameException
     */
    public function renderAsLong(array $lieuDetails, array $spectacles, array $artistes): string {
        // Format the date, the condition is necessary to avoid 01/01/1970 when the date is unknown
        $date = $this->soiree->__get('date');
        if($date !== "Date inconnue") {
            $date = date('d/m/Y', strtotime($date));
        }

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

        // Artists associated with the party
        foreach ($artistes as $artist) {
            $html .= "<li>{$artist['nom']}</li>";
        }
        $html .= "</ul>";

        // Spectacles associated with the party
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
