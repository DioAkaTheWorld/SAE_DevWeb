<?php

namespace nrv\renderer;

class ImageListRenderer {

    public function render(array $images) : string {
        if (empty($images)) {
            return "<p>Aucune image n'est associée à ce spectacle</p>";
        }
        $imageList = "";
        foreach ($images as $image) {
            $imagePath = $image['chemin_fichier'];
            $imageId = $image['id'];
            $imageList .= <<<FIN
            <img src="/SAE_DevWeb/medias/images/$imagePath" alt="image$imageId" class="img-thumbnail">
            <input type="checkbox" id="image$imageId" name="image$imageId">
            FIN;
        }

        return $imageList;
    }
}