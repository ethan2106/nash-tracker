<?php

namespace App\Controller;

use App\Model\ActivityModel;
use App\Model\ObjectifsModel;
use Exception;

/**
 * ActivityController - Gère le suivi des activités physiques.
 * Responsabilités :
 * - Affichage et gestion des activités physiques (CRUD)
 * - Interactions avec ActivityModel pour les données d'activités
 * - Calculs des calories et gestion des totaux.
 */
class ActivityController extends BaseApiController
{
    public function __construct(
        private ActivityModel $activityModel,
        private ObjectifsModel $objectifsModel
    ) {
    }

    /**
     * Affiche la page des activités.
     */
    public function showActivity(): void
    {
        $userId = $this->requireAuth();

        // Récupérer les données pour la vue
        $activitesAujourdhui = $this->activityModel->getActivitesAujourdhui($userId);
        $historique = $this->activityModel->getHistoriqueActivites($userId, 30); // 30 derniers jours
        $totalCaloriesAujourdhui = $this->activityModel->getTotalCaloriesDepenseesAujourdhui($userId);

        // Inclure la vue avec les données
        include __DIR__ . '/../View/activity.php';
    }

    public function ajouterActivite(): array
    {
        try
        {
            $userId = $this->getUserId();
            if ($userId === null)
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            $type = $_POST['type_activite'] ?? '';
            $duree = isset($_POST['duree_minutes']) ? (int)$_POST['duree_minutes'] : 0;
            $calories = isset($_POST['calories']) && $_POST['calories'] !== '' ? (int)$_POST['calories'] : null;

            // Validation
            if (empty($type))
            {
                return ['error' => 'Veuillez sélectionner un type d\'activité'];
            }

            if ($duree <= 0 || $duree > 480) // Max 8h
            {
                return ['error' => 'Durée invalide (1-480 minutes)'];
            }

            if ($calories !== null && ($calories < 0 || $calories > 2000))
            {
                return ['error' => 'Calories invalides (0-2000)'];
            }

            $result = $this->activityModel->ajouterActivite($userId, $type, $duree, $calories);

            if (isset($result['success']))
            {
                // Calculer le bonus de calories après ajout
                $bonus = $this->activityModel->calculerBonusCalories($userId);

                // Invalider le cache du dashboard
                $cache = new \App\Service\CacheService();
                $cache->clearNamespace('dashboard');

                $response = [
                    'success' => true,
                    'message' => 'Activité ajoutée avec succès',
                    'activite_id' => $result['activite_id'],
                    'calories' => $result['calories'],
                    'bonus_calories' => $bonus,
                ];

                return $response;
            } else
            {
                return $result;
            }
        } catch (Exception $e)
        {
            return ['error' => 'Erreur lors de l\'ajout de l\'activité'];
        }
    }

    /**
     * Récupérer les activités du jour.
     */
    public function getActivitesAujourdhui(): array
    {
        try
        {
            $userId = $this->getUserId();
            if ($userId === null)
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            $activitesData = $this->activityModel->getActivitesAujourdhui($userId);

            // Récupérer le poids de l'utilisateur depuis ses objectifs IMC
            $objectifs = $this->objectifsModel->getByUser($userId);
            $userWeight = $objectifs['poids'] ?? null;

            // Ajouter le poids aux données retournées
            if (isset($activitesData['activites']))
            {
                $activitesData['user_weight'] = $userWeight;
            }

            return $activitesData;
        } catch (Exception $e)
        {

            return ['error' => 'Erreur lors de la récupération des activités'];
        }
    }

    /**
     * Récupérer le total des calories dépensées aujourd'hui.
     */
    public function getTotalCaloriesDepensees(): array
    {
        try
        {
            $userId = $this->getUserId();
            if ($userId === null)
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            return $this->activityModel->getTotalCaloriesDepenseesAujourdhui($userId);
        } catch (Exception $e)
        {

            return ['error' => 'Erreur lors du calcul des calories'];
        }
    }

    /**
     * Supprimer une activité.
     */
    public function supprimerActivite(): array
    {
        try
        {
            $userId = $this->getUserId();
            if ($userId === null)
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            $activiteId = isset($_POST['activite_id']) ? (int)$_POST['activite_id'] : 0;

            if ($activiteId <= 0)
            {
                return ['error' => 'ID d\'activité invalide'];
            }

            $result = $this->activityModel->supprimerActivite($userId, $activiteId);

            if (isset($result['success']))
            {
                // Invalider le cache du dashboard
                $cache = new \App\Service\CacheService();
                $cache->clearNamespace('dashboard');

                $response = [
                    'success' => true,
                    'message' => 'Activité supprimée avec succès',
                ];

                return $response;
            } else
            {
                return $result;
            }
        } catch (Exception $e)
        {
            return ['error' => 'Erreur lors de la suppression'];
        }
    }

    /**
     * Récupérer l'historique des activités.
     */
    public function getHistoriqueActivites(): array
    {
        try
        {
            $userId = $this->getUserId();
            if ($userId === null)
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            $jours = isset($_GET['jours']) ? (int)$_GET['jours'] : 7;

            return $this->activityModel->getHistoriqueActivites($userId, $jours);
        } catch (Exception $e)
        {

            return ['error' => 'Erreur lors de la récupération de l\'historique'];
        }
    }

    /**
     * Calculer les calories estimées pour une activité (pour prévisualisation).
     */
    public function calculerCaloriesEstimees(): array
    {
        try
        {
            $type = $_GET['type'] ?? '';
            $duree = isset($_GET['duree']) ? (int)$_GET['duree'] : 0;

            if (empty($type) || $duree <= 0)
            {
                return ['error' => 'Paramètres invalides'];
            }

            $calories = $this->activityModel->calculerCaloriesEstimees($type, $duree);

            return [
                'success' => true,
                'calories_estimees' => $calories,
            ];
        } catch (Exception $e)
        {

            return ['error' => 'Erreur lors du calcul'];
        }
    }
}
