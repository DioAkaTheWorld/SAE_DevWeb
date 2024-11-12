<?php

namespace nrv\renderer;

use nrv\festivale\Soiree;

class SoireeRenderer {

    private Soiree $soiree;

    public function __construct(Soiree $soiree) {
        $this->soiree = $soiree;
    }

    /**
     * Affiche les détails complets de la soirée, incluant les informations sur le lieu et les spectacles associés.
     *
     * @param array $lieuDetails Tableau contenant les informations sur le lieu.
     * @param array $tarifs Tableau contenant les détails des tarifs pour la soirée.
     * @param array $spectacles Tableau d'objets de type spectacle associés à cette soirée.
     * @return string Le HTML généré pour l'affichage des détails de la soirée.
     */
    public function renderAsDetail(array $lieuDetails, array $spectacles,array $artistes): string {
        // Détails de base de la soirée
        $html = <<<HTML
            <h1>{$this->soiree->__get('nom')}</h1>
            <p><strong>Thématique :</strong> {$this->soiree->__get('thematique')}</p>
            <p><strong>Date :</strong> {$this->soiree->__get('date')}</p>
            <p><strong>Horaire :</strong> {$this->soiree->__get('horaire_debut')} - {$this->soiree->__get('horaire_fin')}
            <p><strong>Tarif :</strong> {$this->soiree->__get('tarif')}</p>
        HTML;

        // Informations sur le lieu
        $html .= <<<HTML
            <h2>Lieu</h2>
            <p><strong>Nom :</strong> {$lieuDetails['nom']}</p>
            <p><strong>Adresse :</strong> {$lieuDetails['adresse']}</p>
        HTML;

        // Liste des artistes
        $html .= <<<FIN
            <h2>Artistes</h2>
            <ul>
        FIN;
        foreach ($artistes as $artist) {
            $html .= "<li>{$artist['nom']}</li>";
        }
        $html .= "</ul>";

        // Liste des spectacles associés à la soirée
        $html .= "<h2>Spectacles</h2><ul>";
        foreach ($spectacles as $spectacle) {
            $html .= <<<HTML
                <li>
                    <h3>{$spectacle['titre']}</h3>
                    <p><strong>Style :</strong> {$spectacle['style']}</p>
                    <p><strong>Description :</strong> {$spectacle['description']}</p>
            HTML;

            // Vidéo si disponible
            if (!empty($spectacle['chemin_video'])) {
                $html .= <<<HTML
                    <video width="320" height="240" controls>
                        <source src="/SAE_DevWeb/medias/videos/{$spectacle['chemin_video']}" type="video/mp4">
                    </video>
                HTML;
            } else {
                $html .= "<p>Pas d'extrait vidéo pour ce spectacle</p>";
            }
            $html .= "</li>";
        }
        $html .= "</ul>";

        return $html;
    }
}
