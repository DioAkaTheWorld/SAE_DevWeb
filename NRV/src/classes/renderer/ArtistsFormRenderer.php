<?php

namespace nrv\renderer;

class ArtistsFormRenderer {

    public function render(array $artists) : string {
        if (empty($artists)) {
            return "<p>Aucun artiste n'est associé à ce spectacle</p>";
        }

        $artistList = "";
        foreach ($artists as $artist) {
            $artistName = $artist['nom'];
            $artistId = $artist['id'];
            $artistList .= <<<FIN
            <label class="form-check-label" for="artiste$artistId">$artistName</label>
            <input class="form-check-input" type="checkbox" id="artiste$artistId" name="artiste$artistId" value="$artistName">

            FIN;
        }

        return $artistList;
    }
}