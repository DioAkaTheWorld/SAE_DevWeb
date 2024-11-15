<?php

namespace nrv\renderer;

use nrv\exception\InvalidPropertyNameException;
use nrv\festival\Spectacle;

/**
 * SpectacleRenderer class
 *
 * Renders a spectacle
 */
class SpectacleRenderer {

    /** @var Spectacle spectacle */
    private Spectacle $spectacle;
    /** @var string path of the images */
    private string $imagesPath = "/SAE_DevWeb/medias/images/";
    /** @var string path of the videos */
    private string $videosPath = "/SAE_DevWeb/medias/videos/";

    /**
     * Constructor
     * @param Spectacle $spectacle spectacle
     */
    public function __construct(Spectacle $spectacle) {
        $this->spectacle = $spectacle;
    }

    /**
     * Renders the spectacle as a compact HTML string
     * @param string $date The date of the spectacle
     * @param string $image The image of the spectacle
     * @return string The HTML code of the spectacle
     * @throws InvalidPropertyNameException
     */
    public function renderAsCompact(string $date, string $image): string {
        // Format the date, the condition is necessary to avoid 01/01/1970 when the date is unknown
        if($date !== "Date inconnue") {
            $date = date('d/m/Y', strtotime($date));
        }

        // Display the image of the spectacle, if there is no image, display a default image
        if ($image === "pas d'image") {
            $img = "<img src='{$this->imagesPath}ppp.jpg' alt='image spectacle'>";
        } else {
            $img = "<img src='$this->imagesPath$image' alt='image spectacle'>";
        }

        return <<<FIN
            <div class="col">
                <div class="card border border-secondary border-4 rounded" style="width: 18rem;">
                    $img
                    <div class="card-body">
                        <h5 class="card-title">{$this->spectacle->__get('titre')}</h5>
                        <p class="card-text">Date: $date</p>
                        <p class="card-text">Horaire: {$this->spectacle->__get('horaire')}</p>
                        <a href="?action=display-spectacle&id={$this->spectacle->__get('id')}" class="btn btn-primary">Afficher</a>
                    </div>
                </div>
            </div>
        FIN;
    }

    /**
     * Renders the spectacle as a long HTML string
     * @param array $artistes Array of artist objects associated with this spectacle.
     * @param array $images Array of image objects associated with this spectacle.
     * @return string The HTML code of the spectacle
     * @throws InvalidPropertyNameException
     */
    public function renderAsLong(array $artistes, array $images): string {
        $html = <<<FIN
            <h1>Spectacle: {$this->spectacle->__get('titre')}</h1>
            <hr>
            <h2>Style</h2>
            <p>{$this->spectacle->__get('style')}</p>
            <h2>Description</h2>
            <p>{$this->spectacle->__get('description')}</p>
            <h2>Durée</h2>
            <p>{$this->spectacle->__get('duree')}</p>

        FIN;

        // Artists associated with the spectacle
        $html .= <<<FIN
            <h2>Artistes</h2>
            <ul>
        FIN;
        foreach ($artistes as $artist) {
            $html .= "<li>{$artist['nom']}</li>";
        }
        $html .= "</ul>";

        // Images
        $html .= <<<FIN
                <h2>Images</h2>
                <ul class="list-inline">
            FIN;
        if (!empty($images)) {
            foreach ($images as $image) {
                $html .= <<<FIN
                    <li class="list-inline-item m-3">
                        <div class="card text-center" style="width: 18rem;">
                            <img src='$this->imagesPath{$image['chemin_fichier']}' alt='Image du spectacle' class="img-thumbnail">
                        </div>
                    </li>

                FIN;
            }
            $html .= "</ul>";
        } else {
            $html .= "<p>Pas d'images pour ce spectacle</p>";
        }

        // Video
        $html .= <<<FIN
                <h2>Vidéo</h2>
                <div>
            FIN;
        if (!empty($this->spectacle->__get('chemin_video')) && $this->spectacle->__get('chemin_video') !== "aucune video") {
            $html .= <<<FIN
                <video width='320' height='240' controls>
                    <source src='$this->videosPath{$this->spectacle->__get('chemin_video')}' type='video/mp4'>
                </video>
            </div>
            FIN;
        } else {
            $html .= "<p>Pas d'extrait vidéo pour ce spectacle</p></div>";
        }

        return $html;
    }
}