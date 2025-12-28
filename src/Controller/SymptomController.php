<?php

namespace App\Controller;

use App\Model\SymptomModel;

/**
 * SymptomController - Gestion des symptômes.
 */
class SymptomController extends BaseApiController
{
    private SymptomModel $symptomModel;

    public function __construct()
    {
        $this->symptomModel = new SymptomModel();
    }

    /**
     * Afficher la page des symptômes.
     */
    public function showSymptoms(): void
    {
        $userId = $this->requireAuth();

        // Récupérer les symptômes des 30 derniers jours
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $symptoms = $this->symptomModel->getSymptoms($userId, $startDate, $endDate);

        $symptomTypes = SymptomModel::getSymptomTypes();

        require_once __DIR__ . '/../View/symptoms.php';
    }

    /**
     * API: Ajouter un symptôme.
     */
    public function addSymptom(): array
    {
        $userId = $this->requireAuth();

        $symptomType = $_POST['symptom_type'] ?? '';
        $intensity = (int)($_POST['intensity'] ?? 0);
        $date = $_POST['date'] ?? date('Y-m-d');
        $notes = $_POST['notes'] ?? null;

        if (empty($symptomType) || $intensity < 1 || $intensity > 10) {
            return ['success' => false, 'message' => 'Données invalides'];
        }

        $success = $this->symptomModel->addSymptom($userId, $symptomType, $intensity, $date, $notes);

        return [
            'success' => $success,
            'message' => $success ? 'Symptôme ajouté' : 'Erreur lors de l\'ajout'
        ];
    }

    /**
     * API: Supprimer un symptôme.
     */
    public function deleteSymptom(): array
    {
        $userId = $this->requireAuth();

        $symptomId = (int)($_POST['symptom_id'] ?? 0);

        if ($symptomId <= 0) {
            return ['success' => false, 'message' => 'ID invalide'];
        }

        $success = $this->symptomModel->deleteSymptom($userId, $symptomId);

        return [
            'success' => $success,
            'message' => $success ? 'Symptôme supprimé' : 'Erreur lors de la suppression'
        ];
    }

    /**
     * API: Récupérer les symptômes.
     */
    public function getSymptoms(): array
    {
        $userId = $this->requireAuth();

        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

        $symptoms = $this->symptomModel->getSymptoms($userId, $startDate, $endDate);

        return ['success' => true, 'symptoms' => $symptoms];
    }
}