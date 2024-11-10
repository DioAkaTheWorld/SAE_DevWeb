<?php

namespace nrv\renderer;

use nrv\festivale\Spectacle;

class SpectacleRenderer {

    private Spectacle $spectacle;

    public function __construct(Spectacle $spectacle) {
        $this->spectacle = $spectacle;
    }

    public function renderAsCompact(string $date, string $image): string {
        return <<<FIN
            <li>
                <div>
                    <a href='?action=display-spectacle&id={$this->spectacle->__get('id')}'>{$this->spectacle->__get('titre')}</a>
                </div>
                <span>Date: $date</span>
                <span>Horaire: {$this->spectacle->__get('horaire')}</span>
                <img src="/SAE_DevWeb/medias/images/$image" alt="image spectacle">
            </li>
        FIN;
    }

    public function renderAsLong(array $artistes, array $images): string {
        // En-tête
        $html = <<<FIN
            <h1>{$this->spectacle->__get('titre')}</h1>
            <p><strong>Style :</strong> {$this->spectacle->__get('style')}</p>
            <p><strong>Description :</strong> {$this->spectacle->__get('description')}</p>
            <p><strong>Durée :</strong> {$this->spectacle->__get('duree')}</p>

        FIN;

        // Liste des artistes
        $html .= <<<FIN
            <h2>Artistes</h2>
            <ul>
        FIN;
        foreach ($artistes as $artist) {
            $html .= "<li>{$artist['nom']}</li>";
        }
        $html .= "</ul>";

        // Images du spectacle
        $html .= <<<FIN
                <h2>Images</h2>
                <div>
            FIN;
        if (!empty($images)) {
            foreach ($images as $image) {
                $html .= "<img src='{$image['chemin_fichier']}' alt='Image du spectacle' style='width: 150px; margin: 5px;'>";
            }
            $html .= "</div>";
        } else {
            $html .= "<p>Pas d'images pour ce spectacle</p>";
        }

        // Extrait vidéo
        if (!empty($this->spectacle->__get('chemin_video'))) {
            $html .= <<<FIN
                <h2>Extrait Vidéo</h2>
                <video width='320' height='240' controls>
                    <source src='/SAE_DevWeb/medias/videos/{$this->spectacle->__get('chemin_video')}' type='video/mp4'>
                </video>
            FIN;
        } else {
            $html .= "<p>Pas d'extrait vidéo pour ce spectacle</p>";
        }

        return $html;
    }
}