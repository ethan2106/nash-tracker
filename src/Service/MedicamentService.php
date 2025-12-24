<?php

namespace App\Service;

use App\Model\MedicamentModel;
use App\Model\PriseMedicamentModel;

class MedicamentService
{
    private MedicamentModel $medicamentModel;
    private PriseMedicamentModel $priseModel;

    public function __construct(
        MedicamentModel $medicamentModel,
        PriseMedicamentModel $priseModel
    ) {
        $this->medicamentModel = $medicamentModel;
        $this->priseModel = $priseModel;
    }

    /**
     * Récupère tous les médicaments actifs avec heures_prise décodées.
     */
    public function getAllActiveMedicaments(): array
    {
        $medicaments = $this->medicamentModel->getAllActifs();

        // Decode heures_prise JSON pour chaque médicament
        foreach ($medicaments as &$med) {
            $med['heures_prise'] = json_decode($med['heures_prise'], true) ?? [];
        }

        return $medicaments;
    }

    /**
     * Récupère un médicament par ID avec heures_prise décodées.
     */
    public function getMedicamentById(int $id): ?array
    {
        $medicament = $this->medicamentModel->getById($id);

        if ($medicament) {
            $medicament['heures_prise'] = json_decode($medicament['heures_prise'], true) ?? [];
            $medicament['type'] = $medicament['type'] ?? 'regulier';
        }

        return $medicament;
    }

    /**
     * Récupère les médicaments avec leurs prises pour une date donnée.
     */
    public function getMedicamentsWithPrisesForDate(string $date): array
    {
        $medicaments = $this->medicamentModel->getAllActifs();
        $medicamentIds = array_column($medicaments, 'id');
        $prises = $this->priseModel->getPrisesForMedicamentsDate($medicamentIds, $date);

        $result = [];
        foreach ($medicaments as $med) {
            $result[$med['id']] = [
                'nom' => $med['nom'],
                'type' => $med['type'] ?? 'regulier',
                'heures_prise' => json_decode($med['heures_prise'], true) ?? [],
                'prises' => $prises[$med['id']] ?? [],
            ];
        }

        return $result;
    }

    /**
     * Marque une prise de médicament comme prise.
     */
    public function marquerPrise(int $medicamentId, string $date, string $periode): array
    {
        $this->validatePeriode($periode);
        $this->validateMedicamentExistsAndActive($medicamentId);

        $success = $this->priseModel->marquerPris($medicamentId, $date, $periode);

        if ($success) {
            $prises = $this->priseModel->getPrisesForDate($medicamentId, $date);
            return ['success' => true, 'prises' => $prises];
        }

        return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
    }

    /**
     * Annule une prise de médicament.
     */
    public function annulerPrise(int $medicamentId, string $date, string $periode): array
    {
        $this->validatePeriode($periode);
        $this->validateMedicamentExistsAndActive($medicamentId);

        $success = $this->priseModel->annulerPris($medicamentId, $date, $periode);

        if ($success) {
            $prises = $this->priseModel->getPrisesForDate($medicamentId, $date);
            return ['success' => true, 'prises' => $prises];
        }

        return ['success' => false, 'message' => 'Erreur lors de l\'annulation'];
    }

    /**
     * Crée un nouveau médicament.
     */
    public function createMedicament(array $data): ?array
    {
        $id = $this->medicamentModel->create($data);

        if ($id) {
            return $this->getMedicamentById($id);
        }

        return null;
    }

    /**
     * Met à jour un médicament existant.
     */
    public function updateMedicament(int $id, array $data): ?array
    {
        $existing = $this->medicamentModel->getById($id);
        if (!$existing) {
            return null;
        }

        $success = $this->medicamentModel->update($id, $data);

        if ($success) {
            return $this->getMedicamentById($id);
        }

        return null;
    }

    /**
     * Supprime un médicament.
     */
    public function deleteMedicament(int $id): bool
    {
        $existing = $this->medicamentModel->getById($id);
        if (!$existing) {
            return false;
        }

        return $this->medicamentModel->delete($id);
    }

    /**
     * Récupère l'historique des prises pour une période.
     */
    public function getHistoriquePrises(int $userId, string $startDate, string $endDate): array
    {
        $historique = $this->priseModel->getHistorique($userId, $startDate, $endDate);
        $stats = $this->priseModel->getStats($userId, $startDate, $endDate);

        return [
            'historique' => $historique,
            'stats' => $stats
        ];
    }

    /**
     * Récupère les médicaments actifs avec leurs prises pour une date.
     */
    public function getMedicamentsJour(string $date): array
    {
        $medicaments = $this->medicamentModel->getAllActifs();
        $medicamentIds = array_column($medicaments, 'id');
        $prises = $this->priseModel->getPrisesForMedicamentsDate($medicamentIds, $date);

        foreach ($medicaments as &$med) {
            $medId = $med['id'];
            $med['prises'] = $prises[$medId] ?? [];
            $med['heures_prise'] = json_decode($med['heures_prise'], true) ?? [];
        }

        return $medicaments;
    }

    /**
     * Valide qu'une période est valide.
     */
    private function validatePeriode(string $periode): void
    {
        if (!in_array($periode, ['matin', 'midi', 'soir', 'nuit'])) {
            throw new \InvalidArgumentException('Période invalide');
        }
    }

    /**
     * Valide qu'un médicament existe et est actif.
     */
    private function validateMedicamentExistsAndActive(int $medicamentId): void
    {
        $medicament = $this->medicamentModel->getById($medicamentId);
        if (!$medicament || !$medicament['actif']) {
            throw new \InvalidArgumentException('Médicament non trouvé ou inactif');
        }
    }
}