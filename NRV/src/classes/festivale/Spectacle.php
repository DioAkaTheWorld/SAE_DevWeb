<?php

namespace nrv\festivale;

use nrv\exception\InvalidPropertyNameException;

class Spectacle {

    private int $id; // attribut auto-incrémenté
    private string $titre;
    private string $description;
    private string $url;
    private string $horaire;
    private string $style;

    public function __construct(string $titre, string $description, string $url, string $horaire, string $style) {
        $this->titre = $titre;
        $this->description = $description;
        $this->url = $url;
        $this->horaire = $horaire;
        $this->style = $style;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }



    public function __get(string $attrName) : mixed {
        if (property_exists($this, $attrName)) {
            return $this->$attrName;
        } else {
            return new InvalidPropertyNameException("Propriété $attrName inexistante");
        }
    }
}