<?php

namespace nrv\renderer;

/**
 * ArtistsFormRenderer class
 *
 * Class used to render the form to associate artists with a show
 */
class ArtistsFormRenderer {

    /**
     * Renders the form to associate artists with a show
     *
     * @param array $artists The list of artists to render
     * @return string The HTML code of the form
     */
    public function render(array $artists) : string {
        if (empty($artists)) {
            return "<p>Aucun artiste n'est associé à ce spectacle</p>";
        }

        $artistList = "";
        foreach ($artists as $artist) {
            $artistName = $artist['nom'];
            $artistId = $artist['id'];
            $artistList .= <<<FIN
            <label class="form-check-label m-2" for="artiste$artistId">$artistName</label>
            <input class="form-check-input m-2" type="checkbox" id="artiste$artistId" name="artiste$artistId" value="$artistName">

            FIN;
        }

        return $artistList;
    }
}