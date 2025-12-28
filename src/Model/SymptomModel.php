<?php

namespace App\Model;

use PDO;
use PDOException;

/**
 * SymptomModel - Gestion des symptômes dans la base de données.
 */
class SymptomModel
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Ajouter un symptôme.
     */
    public function addSymptom(int $userId, string $symptomType, int $intensity, string $date, ?string $notes = null): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO symptoms (user_id, symptom_type, intensity, date, notes) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$userId, $symptomType, $intensity, $date, $notes]);
        } catch (PDOException $e) {
            error_log("Erreur ajout symptôme: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer les symptômes d'un utilisateur pour une période.
     */
    public function getSymptoms(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = "SELECT * FROM symptoms WHERE user_id = ?";
        $params = [$userId];

        if ($startDate && $endDate) {
            $query .= " AND date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $query .= " ORDER BY date DESC, created_at DESC";

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur récupération symptômes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Supprimer un symptôme.
     */
    public function deleteSymptom(int $userId, int $symptomId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM symptoms WHERE id = ? AND user_id = ?");
            return $stmt->execute([$symptomId, $userId]);
        } catch (PDOException $e) {
            error_log("Erreur suppression symptôme: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer les types de symptômes disponibles.
     */
    public static function getSymptomTypes(): array
    {
        return [
            'fatigue' => 'Fatigue',
            'douleur_abdominale' => 'Douleur abdominale',
            'nausees' => 'Nausées',
            'perte_appetit' => 'Perte d\'appétit',
            'ballonnements' => 'Ballonnements',
            'constipation' => 'Constipation',
            'diarrhee' => 'Diarrhée',
            'douleurs_articulaires' => 'Douleurs articulaires',
            'maux_tete' => 'Maux de tête',
            'autre' => 'Autre'
        ];
    }
}