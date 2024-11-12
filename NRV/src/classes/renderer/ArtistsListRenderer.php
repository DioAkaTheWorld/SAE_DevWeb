<?php

namespace nrv\renderer;

class ArtistsListRenderer {

    public function render(array $artists) : string {
        $artistList = "";
        foreach ($artists as $artist) {
            $artistName = $artist['nom'];
            $artistId = $artist['id'];
            $artistList .= <<<FIN
            <label for="artiste$artistId">$artistName</label>
            <input type="checkbox" id="artiste$artistId" name="artiste$artistId" value="$artistName">

            FIN;
        }

        return $artistList;
    }
}