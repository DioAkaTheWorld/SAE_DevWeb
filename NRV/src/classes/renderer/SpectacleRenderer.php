<?php

namespace nrv\renderer;

use nrv\festival\Spectacle;

class SpectacleRenderer {

    private Spectacle $spectacle;

    public function __construct(Spectacle $spectacle) {
        $this->spectacle = $spectacle;
    }

    public function renderAsCompact(string $date, string $image): string {
        // Condition pour éviter d'afficher 01/01/1970
        if($date !== "Date inconnue") {
            $date = date('d/m/Y', strtotime($date));
        }

        // Utilise une image par défaut si aucune image ne correspond au spectacle
        if ($image === "pas d'image") {
            $img = "<img src='/SAE_DevWeb/medias/images/ppp.jpg' alt='image spectacle'>";
        } else {
            $img = "<img src='/SAE_DevWeb/medias/images/$image' alt='image spectacle'>";
        }

        return <<<FIN
            <li class="list-inline-item text-center m-3">
                <div class="card border border-secondary border-4 rounded" style="width: 18rem;">
                    $img
                    <div class="card-body">
                        <h5 class="card-title">{$this->spectacle->__get('titre')}</h5>
                        <p class="card-text">Date: $date</p>
                        <p class="card-text">Horaire: {$this->spectacle->__get('horaire')}</p>
                        <a href="?action=display-spectacle&id={$this->spectacle->__get('id')}" class="btn btn-primary">Afficher</a>
                    </div>
                </div>
            </li>
        FIN;
    }

    public function renderAsLong(array $artistes, array $images): string {
        // En-tête
        $html = <<<FIN
            <h1>{$this->spectacle->__get('titre')}</h1>
            <hr>
            <h2>Style</h2>
            <p>{$this->spectacle->__get('style')}</p>
            <h2>Description</h2>
            <p>{$this->spectacle->__get('description')}</p>
            <h2>Durée</h2>
            <p>{$this->spectacle->__get('duree')}</p>

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
                <ul class="list-inline">
            FIN;
        if (!empty($images)) {
            foreach ($images as $image) {
                $html .= <<<FIN
                    <li class="list-inline-item m-3">
                        <div class="card text-center" style="width: 18rem;">
                            <img src='/SAE_DevWeb/medias/images/{$image['chemin_fichier']}' alt='Image du spectacle' class="img-thumbnail">
                        </div>
                    </li>

                FIN;
            }
            $html .= "</ul>";
        } else {
            $html .= "<p>Pas d'images pour ce spectacle</p>";
        }

        // Extrait vidéo
        $html .= <<<FIN
                <h2>Vidéo</h2>
                <div>
            FIN;
        if (!empty($this->spectacle->__get('chemin_video')) && $this->spectacle->__get('chemin_video') !== "aucune video") {
            $html .= <<<FIN
                <video width='320' height='240' controls>
                    <source src='/SAE_DevWeb/medias/videos/{$this->spectacle->__get('chemin_video')}' type='video/mp4'>
                </video>
            </div>
            FIN;
        } else {
            $html .= "<p>Pas d'extrait vidéo pour ce spectacle</p></div>";
        }

        return $html;
    }
}