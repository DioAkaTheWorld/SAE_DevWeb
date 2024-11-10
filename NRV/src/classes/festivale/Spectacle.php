<?php

namespace nrv\festivale;

use nrv\exception\InvalidPropertyNameException;

class Spectacle {

    private int $id; // attribut auto-incrémenté
    private string $titre;
    private string $description;
    private string $chemin_video;
    private string $horaire;
    private string $duree;
    private string $style;

    public function __construct(string $titre, string $description, string $horaire, string $duree,string $style, string $chemin_video = "pas d'image") {
        $this->titre = $titre;
        $this->description = $description;
        $this->chemin_video = $chemin_video;
        $this->horaire = $horaire;
        $this->duree = $duree;
        $this->style = $style;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function setCheminVideo(string $chemin_video): void {
        $this->chemin_video = $chemin_video;
    }


    public function __get(string $attrName) : mixed {
        if (property_exists($this, $attrName)) {
            return $this->$attrName;
        } else {
            return new InvalidPropertyNameException("Propriété $attrName inexistante");
        }
    }
}