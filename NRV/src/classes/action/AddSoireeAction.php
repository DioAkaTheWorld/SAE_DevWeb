<?php

namespace nrv\action;

use nrv\auth\User;
use nrv\exception\InvalidPropertyNameException;
use nrv\festival\Soiree;
use nrv\repository\NrvRepository;

/**
 * Class to add a party
 */
class AddSoireeAction extends Action {

    /**
     * Displays the form to add a party
     * @return string The HTML code of the form
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
            <div class="mb-3">
                <label for="nom" class="form-label">Nom<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div class="mb-3">
            <div>
                <label for="thematique" class="form-label">Thématique<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="thematique" name="thematique" required>
            </div class="mb-3">
            <div>
                <label for="date" class="form-label">Date<span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="date" name="date" required>
            </div>
            <div class="mb-3">
                <label for="horaire_debut" class="form-label">Horaire de début<span class="text-danger">*</span></label>
                <input type="time" class="form-control" id="horaire_debut" name="horaire_debut" required>
            </div>
            <div class="mb-3">
                <label for="horaire_fin" class="form-label">Horaire de fin<span class="text-danger">*</span></label>
                <input type="time" class="form-control" id="horaire_fin" name="horaire_fin" required>
            </div>
            <!--            à implémenter-->
            <div class="mb-3">
                <label for="id_lieu" class="form-label">ID lieu<span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="id_lieu" name="id_lieu" required>
            </div>
            <!--            à implémenter-->
            <div class="mb-3">
                <label for="tarif" class="form-label">Tarif<span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="tarif" name="tarif" required>
            </div>
            <button type="submit" class="btn btn-primary">Ajouter</button>
        FIN;

    }

    /**
     * Processes the form submission to add a party
     * @return string The HTML code of the result
     * @throws InvalidPropertyNameException
     */
    public function executePost(): string {
        $check = $this->checkUser(User::STAFF);
        if ($check !== "") {
            return $check;
        }

        // Get the data from the form and sanitize it
        $nom = filter_var($_POST['nom'], FILTER_SANITIZE_STRING);
        $thematique = filter_var($_POST['thematique'], FILTER_SANITIZE_STRING);
        $date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
        $horaire_debut = filter_var($_POST['horaire_debut'], FILTER_SANITIZE_STRING);
        $horaire_fin = filter_var($_POST['horaire_fin'], FILTER_SANITIZE_STRING);
        $id_lieu = filter_var($_POST['id_lieu'], FILTER_VALIDATE_INT);
        $tarif = filter_var($_POST['tarif'], FILTER_VALIDATE_FLOAT);

        $errors = [];

        // Check the data
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

        if (empty($tarif)) {
            $errors[] = "Le tarif est requis.";
        }

        if (empty($errors)) {
            $soiree = new Soiree($nom, $thematique, $date, $horaire_debut, $horaire_fin, $id_lieu, $tarif);
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