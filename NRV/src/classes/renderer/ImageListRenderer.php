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
            <div class="card text-center m-3" style="width: 18rem">
              <img src="/SAE_DevWeb/medias/images/$imagePath" alt="image$imageId" class="img-thumbnail">
              <div class="card-body">
                <input class="form-check-input" type="checkbox" id="image$imageId" name="image$imageId">
              </div>
            </div>

            FIN;
        }

        return $imageList;
    }
}