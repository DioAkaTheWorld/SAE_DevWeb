<?php

namespace nrv\action;

use nrv\auth\User;
use nrv\exception\InvalidPropertyNameException;
use nrv\festivale\Soiree;
use nrv\repository\NrvRepository;

class AddSoireeAction extends Action {

    /**
     * @throws InvalidPropertyNameException
     */
    public function executeGet(): string {
        $check = $this->checkUser(User::STAFF);
        if ($check !== "") {
            return $check;
        }

        return <<<FIN
        <h2 class="p-2">Ajouter une soirée</h2>
        <hr>
        <form action="?action=add-soiree" method="post">
            <div>
                <label for="nom" class="form-label">Nom*</label>
                <input type="text" class="form-control" id="nom" name="nom">
            </div>
            <div>
                <label for="thematique" class="form-label">Thématique*</label>
                <input type="text" class="form-control" id="thematique" name="thematique">
            </div>
            <div>
                <label for="date" class="form-label">Date*</label>
                <input type="date" class="form-control" id="date" name="date">
            </div>
            <div>
                <label for="horaire_debut" class="form-label">Horaire de début*</label>
                <input type="time" class="form-control" id="horaire_debut" name="horaire_debut">
            </div>
            <div>
                <label for="horaire_fin" class="form-label">Horaire de fin*</label>
                <input type="time" class="form-control" id="horaire_fin" name="horaire_fin">
            </div>
            <!--            à implémenter-->
            <div>
                <label for="id_lieu" class="form-label">ID lieu*</label>
                <input type="number" class="form-control" id="id_lieu" name="id_lieu">
            </div>
            <!--            à implémenter-->
            <button type="submit" class="btn btn-primary">Ajouter</button>
        FIN;

    }

    /**
     * @throws InvalidPropertyNameException
     */
    public function executePost(): string {
        $check = $this->checkUser(User::STAFF);
        if ($check !== "") {
            return $check;
        }

        // Récupérer les données du formulaire et les valider
        $nom = trim($_POST['nom']);
        $thematique = trim($_POST['thematique']);
        $date = trim($_POST['date']);
        $horaire_debut = trim($_POST['horaire_debut']);
        $horaire_fin = trim($_POST['horaire_fin']);
        $id_lieu = trim($_POST['id_lieu']);

        $errors = [];

        // Validation des données
        if (empty($nom)) {
            $errors[] = "Nom de soirée manquant.";
        }

        if (empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors[] = "La date est requise et doit être au format AAAA-MM-JJ.";
        }

        if (empty($horaire_debut) || !preg_match('/^(2[0-3]|[01]?[0-9]):[0-5][0-9]$/', $horaire_debut)) {
            $errors[] = "L'horaire de début est requis et doit être au format HH:MM.";
        }

        if (empty($horaire_fin) || !preg_match('/^(2[0-3]|[01]?[0-9]):[0-5][0-9]$/', $horaire_fin)) {
            $errors[] = "L'horaire de fin est requis et doit être au format HH:MM.";
        }

        if (empty($id_lieu) || !is_numeric($id_lieu)) {
            $errors[] = "Le lieu est requis et doit être un identifiant valide.";
        }

        if (empty($errors)) {
            $soiree = new Soiree($nom, $thematique, $date, $horaire_debut, $horaire_fin, $id_lieu);
            $repo = NrvRepository::getInstance();
            $repo->ajouterSoiree($soiree);
        }

        return <<<FIN
        <div>
            Soirée ajoutée avec succès.
        </div>
        FIN;

    }
}