<?php
declare(strict_types=1);

namespace nrv\action;

use nrv\festivale\Soiree;
use nrv\renderer\SoireeRenderer;
use nrv\repository\NrvRepository;
use Exception;

class DisplayDetailSoireeAction extends Action {

    public function executeGet(): string {
        // Vérifier si l'ID de la soirée est spécifié
        if (!isset($_GET['id'])) {
            http_response_code(400);
            return <<<FIN
            <div class="container d-flex flex-column justify-content-center align-items-center h3">
                        <h2 class="h1">Erreur 400</h2>
                        ID de la soirée manquant
                    </div>
            FIN;
        }

        $soireeId = (int)$_GET['id'];

        // Obtenir les détails de la soirée depuis le dépôt
        try {
            $repository = NrvRepository::getInstance();
            $soireeDetails = $repository->getSoireeDetails($soireeId);
            $lieuDetails = $repository->getLieuDetails($soireeDetails['id_lieu']);
            $artistes = $repository->getArtistsFromSpectacle($soireeId);
            $spectacles = $repository->getSoireeSpectacles($soireeId);
        } catch (Exception $e) {
            return "<p>Erreur lors de la récupération des informations de la soirée : {$e->getMessage()}</p>";
        }

        // Créer un objet Soiree avec les détails récupérés
        $soireeObjet = new Soiree($soireeDetails['nom'], $soireeDetails['thematique'], $soireeDetails['date'], $soireeDetails['horaire_debut'], $soireeDetails['horaire_fin'], $soireeDetails['id_lieu'], $soireeDetails['tarif']);
        $soireeObjet->setId($soireeId);

        // Créer un renderer pour la soirée et générer le contenu HTML détaillé
        $soireeRenderer = new SoireeRenderer($soireeObjet);

        return $soireeRenderer->renderAsDetail($lieuDetails, $spectacles,$artistes);
    }

    public function executePost(): string {
        return $this->executeGet();
    }
}
