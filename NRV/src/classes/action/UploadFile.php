<?php

namespace nrv\action;

use Exception;

/**
 * Manage the upload of files
 */
abstract class UploadFile {

    /**
     * Image upload
     * @param string $format The file format (jpg or jpeg, without the period)
     * @return string The file name
     * @throws Exception
     */
    public static function uploadImage(string $format): string {
        return self::uploadFile("image", $format);
    }

    /**
     * Video upload
     * @return string The file name
     * @throws Exception
     */
    public static function uploadVideo(): string {
        return self::uploadFile("video", "mp4");
    }

    /**
     * Upload a file
     * @param string $type The type of file (image or video)
     * @param string $format The file format (jpg, jpeg or mp4, without the period)
     * @return string The file name
     * @throws Exception
     */
    private static function uploadFile(string $type, string $format): string {
        // Check if the file is an image or a video and if the format is correct
        if ($type !== "image" && $type !== "video") {
            throw new Exception("Type de fichier incorrect");
        }
        if ($format !== "jpg" && $format !== "jpeg" && $format !== "mp4") {
            throw new Exception("Format de fichier incorrect");
        }
        if ($type === "image") {
            if ($format !== "jpg" && $format !== "jpeg") {
                throw new Exception("Format d'image incorrect");
            }
        }
        if ($type === "video") {
            if ($format !== "mp4") {
                throw new Exception("Format de vidéo incorrect");
            }
        }

        // Define the directory and the file type
        $dir = __DIR__ . "/../../../../medias/" . $type . "s" . "/"; // NRV/medias/images/ ou NRV/medias/videos/
        $fichierType = $type . "/" . $format; // image/jpg, image/jpeg, image/png, video/mp4

        $upload_dir = $dir;
        $file_name = uniqid() . "." . $format;
        $tmp = $_FILES[$type]["tmp_name"];
        $dest = $upload_dir . $file_name;
        // Check if the file is an image or a video, if the format is correct and if the file size is less than 15MB
        if ($_FILES[$type]["type"] === $fichierType && $_FILES[$type]["size"] < 15728640 && move_uploaded_file($tmp, $dest)) {
            return $file_name;
        }
        throw new Exception("Échec de l'upload ou format audio incorrect");
    }
}