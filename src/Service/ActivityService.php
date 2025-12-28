<?php

namespace App\Service;

use Exception;
use PDO;

/**
 * ActivityService - Gère la récupération des activités utilisateur.
 * Responsabilités :
 * - Récupération des activités récentes (repas, médicaments, activités physiques)
 * - Comptage total des activités.
 */
class ActivityService
{
    public function __construct(
        private PDO $db,
        private CacheService $cache
    ) {
    }

    /**
     * Retourne le nombre total d'activités pour un utilisateur.
     */
    public function getRecentActivitiesCount(int $userId): int
    {
        try
        {
            $sql = '
                SELECT COUNT(*) AS total FROM (
                    SELECT r.id AS id, r.date_heure AS date
                    FROM repas r
                    WHERE r.user_id = :userId1
                    UNION ALL
                    SELECT ap.id AS id, ap.date_heure AS date
                    FROM activites_physiques ap
                    WHERE ap.user_id = :userId3
                ) t
            ';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':userId1', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':userId3', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int)($row['total'] ?? 0);
        } catch (Exception $e)
        {
            error_log('Erreur count activités: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Récupère les activités récentes de l'utilisateur.
     */
    public function getRecentActivities(int $userId, int $limit = 5, int $offset = 0): array
    {
        try
        {
            $sql = "
                SELECT type, id, date, description, valeur, unite FROM (
                    SELECT
                        'repas' AS type,
                        r.id AS id,
                        r.date_heure AS date,
                        CONCAT('Repas: ', r.type_repas) AS description,
                        COALESCE(SUM(ra.quantite_g * a.calories_100g / 100), 0) AS valeur,
                        'kcal' AS unite
                    FROM repas r
                    LEFT JOIN repas_aliments ra ON r.id = ra.repas_id
                    LEFT JOIN aliments a ON ra.aliment_id = a.id
                    WHERE r.user_id = :userId1
                    GROUP BY r.id, r.date_heure, r.type_repas
                    UNION ALL
                    SELECT
                        'activite' AS type,
                        ap.id AS id,
                        ap.date_heure AS date,
                        CONCAT('Activité: ', ap.type_activite, ' (', ap.duree_minutes, ' min, ', ap.calories_depensees, ' kcal)') AS description,
                        ap.duree_minutes AS valeur,
                        'min' AS unite
                    FROM activites_physiques ap
                    WHERE ap.user_id = :userId3
                ) t
                ORDER BY date DESC
                LIMIT :limit OFFSET :offset
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':userId1', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':userId3', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            return $activities;
        } catch (Exception $e)
        {
            error_log('Erreur récupération activités récentes: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Retourne les calories dépensées lors d'activités physiques pour une date donnée.
     */
    public function getCaloriesBurnedByDate(int $userId, string $date): float
    {
        $namespace = 'activity';
        $key = 'calories_' . $userId . '_' . $date;

        $cached = $this->cache->get($namespace, $key);
        if ($cached !== null)
        {
            return $cached;
        }

        try
        {
            $stmt = $this->db->prepare('
                SELECT COALESCE(SUM(calories_depensees), 0) AS total_calories
                FROM activites_physiques
                WHERE user_id = ? AND date_heure BETWEEN ? AND ?
            ');
            $stmt->bindValue(1, $userId, PDO::PARAM_INT);
            $stmt->bindValue(2, $date . ' 00:00:00', PDO::PARAM_STR);
            $stmt->bindValue(3, $date . ' 23:59:59', PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $value = (float)($result['total_calories'] ?? 0);
        } catch (Exception $e)
        {
            error_log('Erreur récupération calories dépensées: ' . $e->getMessage());
            $value = 0.0;
        }

        $this->cache->set($namespace, $key, $value, \App\Service\CacheService::TTL_SHORT);

        return $value;
    }
}
