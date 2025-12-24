<?php

namespace App\Model;

/**
 * HistoriqueMesuresModel
 * Gestion de l'historique des mesures poids/IMC pour les graphiques d'évolution.
 */
class HistoriqueMesuresModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Sauvegarder une mesure (poids/IMC) pour un utilisateur
     * Met à jour si une mesure existe déjà pour la même date.
     */
    public function saveMesure(int $userId, float $poids, float $imc, float $taille, ?string $date = null): bool
    {
        $date = $date ?? date('Y-m-d');
        $now = (new \DateTime())->format('Y-m-d H:i:s');

        try
        {
            // Vérifier si une mesure existe déjà pour cette date
            $checkStmt = $this->pdo->prepare('SELECT id FROM historique_mesures WHERE user_id = ? AND date_mesure = ?');
            $checkStmt->execute([$userId, $date]);
            $existingId = $checkStmt->fetchColumn();

            if ($existingId)
            {
                // Mettre à jour la mesure existante
                $sql = 'UPDATE historique_mesures SET poids = ?, imc = ?, taille = ?, updated_at = ? WHERE id = ?';
                $stmt = $this->pdo->prepare($sql);

                return $stmt->execute([$poids, $imc, $taille, $now, $existingId]);
            } else
            {
                // Insérer une nouvelle mesure
                $sql = 'INSERT INTO historique_mesures (user_id, date_mesure, poids, imc, taille) VALUES (?, ?, ?, ?, ?)';
                $stmt = $this->pdo->prepare($sql);

                return $stmt->execute([$userId, $date, $poids, $imc, $taille]);
            }
        } catch (\PDOException $e)
        {
            error_log('Erreur sauvegarde mesure: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Récupérer l'historique des mesures pour un utilisateur
     * Trié par date croissante.
     */
    public function getHistorique(int $userId, int $limit = 100, int $offset = 0): array
    {
        $sql = 'SELECT date_mesure, poids, imc, taille, created_at
                FROM historique_mesures
                WHERE user_id = ?
                ORDER BY date_mesure ASC
                LIMIT ? OFFSET ?';

        try
        {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $limit, $offset]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e)
        {
            error_log('Erreur récupération historique: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Récupérer les mesures sur une période donnée.
     */
    public function getMesuresPeriode(int $userId, string $dateDebut, string $dateFin): array
    {
        $sql = 'SELECT date_mesure, poids, imc, taille
                FROM historique_mesures
                WHERE user_id = ? AND date_mesure BETWEEN ? AND ?
                ORDER BY date_mesure ASC';

        try
        {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $dateDebut, $dateFin]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e)
        {
            error_log('Erreur récupération mesures période: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Supprimer une mesure spécifique.
     */
    public function deleteMesure(int $userId, string $date): bool
    {
        $sql = 'DELETE FROM historique_mesures WHERE user_id = ? AND date_mesure = ?';

        try
        {
            $stmt = $this->pdo->prepare($sql);

            return $stmt->execute([$userId, $date]);
        } catch (\PDOException $e)
        {
            error_log('Erreur suppression mesure: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Récupérer le nombre total de mesures pour un utilisateur.
     */
    public function getTotalMesures(int $userId): int
    {
        $sql = 'SELECT COUNT(*) FROM historique_mesures WHERE user_id = ?';

        try
        {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);

            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e)
        {
            error_log('Erreur récupération total mesures: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Vérifier si une mesure existe pour une date donnée.
     */
    public function mesureExists(int $userId, string $date): bool
    {
        $sql = 'SELECT COUNT(*) FROM historique_mesures WHERE user_id = ? AND date_mesure = ?';

        try
        {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $date]);

            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e)
        {
            error_log('Erreur vérification mesure: ' . $e->getMessage());

            return false;
        }
    }
}
