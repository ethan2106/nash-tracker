<?php

namespace App\Repository;

use App\Model\Database;
use PDO;

/**
 * MealRepository - Implémentation des opérations sur les repas.
 */
class MealRepository implements MealRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Récupère un repas par date et type pour un utilisateur.
     */
    public function getMealByDateAndType(int $userId, string $date, string $mealType): ?array
    {
        try
        {
            $stmt = $this->db->prepare('
                SELECT * FROM repas
                WHERE user_id = ? AND date_heure BETWEEN ? AND ? AND type_repas = ?
                ORDER BY date_heure DESC
                LIMIT 1
            ');
            $stmt->execute([$userId, $date . ' 00:00:00', $date . ' 23:59:59', $mealType]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ?: null;
        } catch (\Exception $e)
        {
            error_log('Erreur lors de la récupération du repas: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Crée un repas avec un aliment en transaction atomique.
     */
    public function createMealWithFood(int $userId, string $mealType, int $foodId, float $quantity): int|false
    {
        try
        {
            $this->db->beginTransaction();

            // Créer le repas
            $mealId = $this->createMeal($userId, $mealType);
            if (!$mealId)
            {
                $this->db->rollBack();

                return false;
            }

            // Ajouter l'aliment
            $result = $this->addFoodToMeal($mealId, $foodId, $quantity);
            if (!$result)
            {
                $this->db->rollBack();

                return false;
            }

            $this->db->commit();

            return $mealId;

        } catch (\Exception $e)
        {
            $this->db->rollBack();
            error_log('Erreur lors de la création du repas avec aliment: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Ajoute un aliment à un repas existant.
     */
    public function addFoodToMeal(int $mealId, int $foodId, float $quantity): bool
    {
        try
        {
            // Vérifier la quantité
            if ($quantity <= 0)
            {
                return false;
            }

            // Vérifier si l'aliment existe
            $stmt = $this->db->prepare('SELECT id FROM aliments WHERE id = ?');
            $stmt->execute([$foodId]);
            if (!$stmt->fetch())
            {
                return false;
            }

            // Vérifier si le repas existe
            $stmt = $this->db->prepare('SELECT id FROM repas WHERE id = ?');
            $stmt->execute([$mealId]);
            if (!$stmt->fetch())
            {
                return false;
            }

            // Ajouter à repas_aliments
            $stmt = $this->db->prepare('
                INSERT INTO repas_aliments (repas_id, aliment_id, quantite_g)
                VALUES (?, ?, ?)
            ');
            $result = $stmt->execute([$mealId, $foodId, $quantity]);

            if (!$result)
            {
                return false;
            }

            // Mettre à jour le timestamp du repas s'il est d'aujourd'hui
            try
            {
                $today = date('Y-m-d');
                $update = $this->db->prepare('UPDATE repas SET date_heure = datetime(\'now\') WHERE id = ? AND date_heure BETWEEN ? AND ?');
                $update->execute([$mealId, $today . ' 00:00:00', $today . ' 23:59:59']);
            } catch (\Exception $e)
            {
                // Ignorer l'erreur de mise à jour du timestamp
            }

            return true;
        } catch (\Exception $e)
        {
            error_log('Erreur lors de l\'ajout d\'aliment au repas: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Récupère les repas d'un utilisateur pour une date donnée.
     */
    public function getMealsByDate(int $userId, string $date): array
    {
        try
        {
            $stmt = $this->db->prepare('
                SELECT r.*, COUNT(ra.id) AS aliment_count,
                       SUM((a.calories_100g * ra.quantite_g) / 100) AS calories_total,
                       SUM((a.proteines_100g * ra.quantite_g) / 100) AS proteines_total,
                       SUM((a.glucides_100g * ra.quantite_g) / 100) AS glucides_total,
                       SUM((a.lipides_100g * ra.quantite_g) / 100) AS lipides_total,
                       SUM((a.fibres_100g * ra.quantite_g) / 100) AS fibres_total,
                       SUM((a.sucres_100g * ra.quantite_g) / 100) AS sucres_total,
                       SUM((a.acides_gras_satures_100g * ra.quantite_g) / 100) AS acides_gras_satures_total,
                       SUM((a.sodium_100g * ra.quantite_g) / 100) AS sodium_total
                FROM repas r
                LEFT JOIN repas_aliments ra ON r.id = ra.repas_id
                LEFT JOIN aliments a ON ra.aliment_id = a.id
                WHERE r.user_id = ? AND DATE(r.date_heure) = ?
                GROUP BY r.id
                ORDER BY r.date_heure DESC
            ');
            $stmt->execute([$userId, $date]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e)
        {
            error_log('Erreur lors de la récupération des repas: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Supprime un aliment d'un repas.
     */
    public function removeFoodFromMeal(int $mealId, int $foodId): bool
    {
        try
        {
            $stmt = $this->db->prepare('
                DELETE FROM repas_aliments
                WHERE repas_id = ? AND aliment_id = ?
            ');
            $stmt->execute([$mealId, $foodId]);

            return $stmt->rowCount() > 0;
        } catch (\Exception $e)
        {
            error_log('Erreur lors de la suppression d\'aliment du repas: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Crée un nouveau repas (méthode privée).
     */
    private function createMeal(int $userId, string $mealType = 'repas', ?string $dateTime = null): int|false
    {
        try
        {
            $dateTime = $dateTime ?? date('Y-m-d H:i:s');

            $stmt = $this->db->prepare('
                INSERT INTO repas (user_id, type_repas, date_heure)
                VALUES (?, ?, ?)
            ');
            $result = $stmt->execute([$userId, $mealType, $dateTime]);

            if ($result)
            {
                return (int)$this->db->lastInsertId();
            }

            return false;
        } catch (\Exception $e)
        {
            error_log('Erreur lors de la création du repas: ' . $e->getMessage());

            return false;
        }
    }
}
