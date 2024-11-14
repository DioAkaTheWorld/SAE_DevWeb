<?php

namespace nrv\festival;

use nrv\exception\InvalidPropertyNameException;

class Soiree {

    private int $id; // attribut auto-incrémenté
    private string $nom;
    private string $thematique;
    private string $date;
    private string $horaire_debut;
    private string $horaire_fin;
    private int $id_lieu;

    private int $tarif;

    public function __construct(string $nom, string $thematique, string $date, string $horaire_debut, string $horaire_fin, int $id_lieu, int $tarif) {
        $this->nom = $nom;
        $this->thematique = $thematique;
        $this->date = $date;
        $this->horaire_debut = $horaire_debut;
        $this->horaire_fin = $horaire_fin;
        $this->id_lieu = $id_lieu;
        $this->tarif = $tarif;
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