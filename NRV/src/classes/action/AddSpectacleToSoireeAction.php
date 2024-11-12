<?php

namespace nrv\action;

use Exception;
use nrv\exception\InvalidPropertyValueException;
use nrv\repository\NrvRepository;

class AddSpectacleToSoireeAction extends Action
{
    /**
     * Affiche le formulaire pour ajouter un spectacle à une soirée
     *
     * @return string Le code HTML du formulaire
     */
    public function executeGet(): string
    {
        $repo = NrvRepository::getInstance();
        $soirees = $repo->findAllSoirees();
        $spectacles = $repo->findAllSpectacles();

        // Commence le contenu HTML pour le formulaire
        $res = <<<FIN
        <h2 class="p-2">Ajouter un Spectacle à une Soirée</h2>
        <hr>
        <form method="POST" action="?action=add-spectacle-to-soiree">
            <div>
                <label for="id_soiree" class="form-label">Sélectionnez une Soirée*</label>
                <select name="id_soiree" id="id_soiree" class="form-control" required>
                    <option value="">-- Choisir une soirée --</option>
        FIN;

        // Ajoute les options pour les soirées
        foreach ($soirees as $soiree) {
            $res .= "<option value='{$soiree['id']}'>{$soiree['nom']} - {$soiree['date']}</option>";
        }

        $res .= <<<FIN
                </select>
            </div>
            <div>
                <label for="id_spectacle" class="form-label">Sélectionnez un Spectacle*</label>
                <select name="id_spectacle" id="id_spectacle" class="form-control" required>
                    <option value="">-- Choisir un spectacle --</option>
        FIN;

        // Ajoute les options pour les spectacles
        foreach ($spectacles as $spectacle) {
            $res .= "<option value='{$spectacle['id']}'>{$spectacle['titre']}</option>";
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
     * Traite la soumission du formulaire pour ajouter un spectacle à une soirée
     *
     * @return string Retourne une redirection ou un message d'erreur si la redirection échoue
     */
    public function executePost(): string
    {
        $repo = NrvRepository::getInstance();

        // Récupération et validation des données du formulaire
        $id_soiree = filter_var($_POST['id_soiree'], FILTER_SANITIZE_NUMBER_INT);
        $id_spectacle = filter_var($_POST['id_spectacle'], FILTER_SANITIZE_NUMBER_INT);

        try {
            if (empty($id_soiree)) {
                throw new InvalidPropertyValueException("Soirée non sélectionnée.");
            }
            if (empty($id_spectacle)) {
                throw new InvalidPropertyValueException("Spectacle non sélectionné.");
            }

            // Ajout de l'association dans la base de données
            $success = $repo->addSpectacleToSoiree($id_soiree, $id_spectacle);
            if (!$success) {
                throw new Exception("Erreur lors de l'ajout du spectacle à la soirée.");
            }

            // Mettre un indicateur dans la session pour signaler la mise à jour dans le header
            $_SESSION['update_header'] = "Le spectacle a été ajouté à la soirée avec succès !";

        } catch (InvalidPropertyValueException $e) {
            return $this->executeGet() . "<div class='alert alert-danger'>{$e->getMessage()}</div>";
        } catch (Exception $e) {
            return $this->executeGet() . "<div class='alert alert-danger'>Erreur : {$e->getMessage()}</div>";
        }

        // Redirection vers l'index pour voir la mise à jour
        header("Location: index.php");
        exit();
    }
}
