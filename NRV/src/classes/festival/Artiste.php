<?php

namespace nrv\festival;

use nrv\exception\InvalidPropertyNameException;

class Artiste {

    private int $id; // Variable auto-incrémentée
    private string $nom;

    public function __construct(string $nom) {
        $this->nom = $nom;
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