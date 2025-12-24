<?php

namespace App\Model;

use Exception;
use PDO;

/**
 * ActivityModel - Modèle pour la gestion des activités physiques.
 * Responsabilités :
 * - CRUD des activités physiques
 * - Calculs des calories dépensées
 * - Gestion des totaux quotidiens.
 */
class ActivityModel extends BaseModel
{

    // Coefficients réalistes basés sur MET (kcal/min pour une personne de 70kg)
    // Valeurs ajustées pour être plus crédibles et médicalement validées
    private const CALORIE_COEFFICIENTS = [
        'marche' => 4.2,    // Marche modérée (3-5 km/h) - ~250 kcal/h
        'course' => 7.0,    // Course légère (8-9 km/h) - ~420 kcal/h
        'velo' => 5.5,      // Vélo modéré - ~330 kcal/h
        'natation' => 6.0,  // Natation modérée - ~360 kcal/h
        'yoga' => 2.5,      // Yoga/Hatha - ~150 kcal/h
        'musculation' => 3.5, // Musculation légère - ~210 kcal/h
        'danse' => 4.5,     // Danse - ~270 kcal/h
        'tennis' => 5.5,    // Tennis double - ~330 kcal/h
        'football' => 6.5,  // Football modéré - ~390 kcal/h
        'basketball' => 6.0, // Basketball - ~360 kcal/h
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Ajouter une activité physique.
     */
    public function ajouterActivite($userId, $type, $dureeMinutes, $calories = null)
    {
        file_put_contents(__DIR__ . '/../../storage/acti_debug.log', date('Y-m-d H:i:s') . " Model ajouterActivite called with userId=$userId, type=$type, duree=$dureeMinutes, calories=" . ($calories ?? 'null') . "\n", FILE_APPEND);
        try
        {
            // Calculer les calories si non fournies
            if ($calories === null || $calories <= 0)
            {
                $calories = $this->calculerCaloriesEstimees($type, $dureeMinutes);
                file_put_contents(__DIR__ . '/../../storage/acti_debug.log', "Calculated calories: $calories\n", FILE_APPEND);
            }

            file_put_contents(__DIR__ . '/../../storage/acti_debug.log', "Executing INSERT with calories=$calories\n", FILE_APPEND);
            $stmt = $this->db->prepare('
                INSERT INTO activites_physiques (user_id, type_activite, duree_minutes, calories_depensees, date_heure)
                VALUES (?, ?, ?, ?, datetime(\'now\'))
            ');

            $stmt->execute([$userId, $type, $dureeMinutes, $calories]);
            $activiteId = $this->db->lastInsertId();
            file_put_contents(__DIR__ . '/../../storage/acti_debug.log', "Insert successful, activite_id=$activiteId\n", FILE_APPEND);

            $result = [
                'success' => true,
                'activite_id' => $activiteId,
                'calories' => $calories,
            ];
            file_put_contents(__DIR__ . '/../../storage/acti_debug.log', "Returning: " . json_encode($result) . "\n", FILE_APPEND);
            return $result;
        } catch (Exception $e)
        {
            file_put_contents(__DIR__ . '/../../storage/acti_debug.log', "Model exception: " . $e->getMessage() . "\n", FILE_APPEND);
            error_log('Erreur ajout activité: ' . $e->getMessage());

            return ['error' => 'Erreur lors de l\'ajout de l\'activité'];
        }
    }

    /**
     * Calculer les calories estimées pour une activité.
     */
    public function calculerCaloriesEstimees($type, $dureeMinutes)
    {
        $coefficient = self::CALORIE_COEFFICIENTS[$type] ?? 8; // Défaut marche

        return (int)($coefficient * $dureeMinutes);
    }

    /**
     * Récupérer les activités du jour.
     */
    public function getActivitesAujourdhui($userId)
    {
        try
        {
            $today = date('Y-m-d');
            $stmt = $this->db->prepare('
                SELECT id, type_activite, duree_minutes, calories_depensees, date_heure
                FROM activites_physiques
                WHERE user_id = ? AND date_heure BETWEEN ? AND ?
                ORDER BY date_heure DESC
            ');

            $stmt->execute([$userId, $today . ' 00:00:00', $today . ' 23:59:59']);
            $activites = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalCalories = array_sum(array_column($activites, 'calories_depensees'));

            return [
                'success' => true,
                'activites' => $activites,
                'total_calories' => $totalCalories,
            ];
        } catch (Exception $e)
        {
            error_log('Erreur récupération activités: ' . $e->getMessage());

            return ['error' => 'Erreur lors de la récupération des activités'];
        }
    }

    /**
     * Récupérer le total des calories dépensées aujourd'hui.
     */
    public function getTotalCaloriesDepenseesAujourdhui($userId)
    {
        try
        {
            $today = date('Y-m-d');
            $stmt = $this->db->prepare('
                SELECT COALESCE(SUM(calories_depensees), 0) as total_calories
                FROM activites_physiques
                WHERE user_id = ? AND date_heure BETWEEN ? AND ?
            ');

            $stmt->execute([$userId, $today . ' 00:00:00', $today . ' 23:59:59']);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'total_calories' => (int)$result['total_calories'],
            ];
        } catch (Exception $e)
        {
            error_log('Erreur calcul total calories: ' . $e->getMessage());

            return ['error' => 'Erreur lors du calcul des calories'];
        }
    }

    /**
     * Supprimer une activité.
     */
    public function supprimerActivite($userId, $activiteId)
    {
        file_put_contents(__DIR__ . '/../../storage/acti_debug.log', date('Y-m-d H:i:s') . " Model supprimerActivite called with userId=$userId, activiteId=$activiteId\n", FILE_APPEND);
        try
        {
            file_put_contents(__DIR__ . '/../../storage/acti_debug.log', "Executing DELETE\n", FILE_APPEND);
            $stmt = $this->db->prepare('
                DELETE FROM activites_physiques
                WHERE id = ? AND user_id = ?
            ');

            $stmt->execute([$activiteId, $userId]);
            $affected = $stmt->rowCount();
            file_put_contents(__DIR__ . '/../../storage/acti_debug.log', "DELETE executed, affected rows: $affected\n", FILE_APPEND);

            if ($affected > 0) {
                $result = ['success' => true];
            } else {
                $result = ['error' => 'Activité non trouvée ou déjà supprimée'];
            }
            file_put_contents(__DIR__ . '/../../storage/acti_debug.log', "Returning: " . json_encode($result) . "\n", FILE_APPEND);
            return $result;
        } catch (Exception $e)
        {
            file_put_contents(__DIR__ . '/../../storage/acti_debug.log', "Model exception: " . $e->getMessage() . "\n", FILE_APPEND);
            error_log('Erreur suppression activité: ' . $e->getMessage());

            return ['error' => 'Erreur lors de la suppression'];
        }
    }

    /**
     * Calculer le surplus de calories à ajouter aux cals dispo si total dépensé > 500.
     */
    public function calculerBonusCalories($userId)
    {
        $total = $this->getTotalCaloriesDepenseesAujourdhui($userId);
        $totalCalories = $total['success'] ? $total['total_calories'] : 0;

        // Si on dépasse 500 kcal, le surplus devient calories dispo
        if ($totalCalories > 500)
        {
            return $totalCalories - 500;
        }

        return 0;
    }

    /**
     * Récupérer l'historique des activités sur une période.
     */
    public function getHistoriqueActivites($userId, $jours = 7)
    {
        try
        {
            $stmt = $this->db->prepare("
                SELECT DATE(date_heure) as date,
                       SUM(duree_minutes) as total_duree,
                       SUM(calories_depensees) as total_calories,
                       COUNT(*) as nombre_activites
                FROM activites_physiques
                WHERE user_id = ? AND date_heure >= date('now', '-' || ? || ' days')
                GROUP BY DATE(date_heure)
                ORDER BY date DESC
            ");

            $stmt->execute([$userId, $jours]);
            $historique = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'historique' => $historique,
            ];
        } catch (Exception $e)
        {
            error_log('Erreur historique activités: ' . $e->getMessage());

            return ['error' => 'Erreur lors de la récupération de l\'historique'];
        }
    }
}
