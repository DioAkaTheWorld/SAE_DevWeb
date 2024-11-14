<?php

namespace nrv\action;

use Exception;
use nrv\exception\InvalidPropertyValueException;
use nrv\repository\NrvRepository;

/**
 * Class to add a spectacle to a party
 */
class AddSpectacleToSoireeAction extends Action {

    /**
     * Displays the form to add a spectacle to a party
     * @return string The HTML code of the form
     */
    public function executeGet(): string {
        $repo = NrvRepository::getInstance();
        $soirees = $repo->findAllSoirees();
        $spectacles = $repo->findAllSpectacles();

        $res = <<<FIN
        <h2 class="p-2">Ajouter un Spectacle à une Soirée</h2>
        <hr>
        <form method="POST" action="?action=add-spectacle-to-soiree">
            <div class="mb-3 col-3">
                <label for="id_spectacle" class="form-label">Sélectionnez un spectacle<span class="text-danger">*</span></label>
                <select name="id_spectacle" id="id_spectacle" class="form-select" required>
                    <option value="">-- Choisir un spectacle --</option>
        FIN;

        // Add the options for the spectacles
        foreach ($spectacles as $spectacle) {
            $res .= "<option value='{$spectacle['id']}'>{$spectacle['titre']}</option>";
        }

        $res .= <<<FIN
                </select>
            </div>
            <div class="mb-3 col-3">
                <label for="id_soiree" class="form-label">Sélectionnez une soirée<span class="text-danger">*</span></label>
                <select name="id_soiree" id="id_soiree" class="form-select" required>
                    <option value="">-- Choisir une soirée --</option>
        FIN;

        // Add the options for the parties
        foreach ($soirees as $soiree) {
            $res .= "<option value='{$soiree['id']}'>{$soiree['nom']} - {$soiree['date']}</option>";
        }

        $res .= <<<FIN
                </select>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Ajouter</button>
        </form>
        FIN;

        return $res;
    }

    /**
     * Adds a spectacle to a party
     * @return string The HTML code of the result
     */
    public function executePost(): string {
        $repo = NrvRepository::getInstance();

        // Get the data from the form and sanitize it
        $id_soiree = filter_var($_POST['id_soiree'], FILTER_SANITIZE_NUMBER_INT);
        $id_spectacle = filter_var($_POST['id_spectacle'], FILTER_SANITIZE_NUMBER_INT);

        // Check if the party and the spectacle are selected
        try {
            if (empty($id_soiree)) {
                throw new InvalidPropertyValueException("Soirée non sélectionnée.");
            }
            if (empty($id_spectacle)) {
                throw new InvalidPropertyValueException("Spectacle non sélectionné.");
            }

            // Add the spectacle to the party
            $success = $repo->addSpectacleToSoiree($id_soiree, $id_spectacle);
            if (!$success) {
                throw new Exception("Ce spectacle est déjà associé à la soirée sélectionnée.");
            }
        } catch (InvalidPropertyValueException|Exception $e) {
            return $this->executeGet() . "<div class='alert alert-danger my-3'>{$e->getMessage()}</div>";
        }

        return $this->executeGet() . "<div class='alert alert-success my-3'>Spectacle ajouté à la soirée avec succès.</div>";
    }
}
