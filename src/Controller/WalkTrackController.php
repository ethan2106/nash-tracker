<?php

namespace App\Controller;

use App\Model\WalkModel;
use App\Service\GamificationService;
use App\Service\ServiceContainer;
use Exception;

/**
 * WalkTrackController - Contrôleur pour le module WalkTrack.
 *
 * Responsabilités :
 * - CRUD des marches
 * - Gestion des objectifs
 * - Stats et historique
 * - Parcours favoris
 */
class WalkTrackController extends BaseApiController
{
    private WalkModel $walkModel;

    private GamificationService $gamificationService;

    public function __construct()
    {
        require_once __DIR__ . '/../Model/WalkModel.php';
        $this->walkModel = new WalkModel();
        $this->gamificationService = new GamificationService();
    }

    /**
     * Affiche la page WalkTrack.
     */
    public function showWalkTrack(): void
    {
        $userId = $this->requireAuth();

        // Récupérer les données de base pour la vue
        $pageData = $this->getPageData();

        // Extraction des données pour la vue
        $marches = $pageData['marches'] ?? [];
        $totals = $pageData['totals'] ?? [];
        $historique = $pageData['historique'] ?? [];
        $objectifs = $pageData['objectifs'] ?? [];
        $progression = $pageData['progression'] ?? [];
        $streak = $pageData['streak'] ?? 0;
        $totalJours = $pageData['total_jours'] ?? 0;
        $badges = $pageData['badges'] ?? ['earned' => [], 'toEarn' => []];
        $parcours = $pageData['parcours'] ?? [];
        $userWeight = $pageData['user_weight'] ?? 0;

        // Inclure la vue avec les données
        include __DIR__ . '/../View/walktrack.php';
    }

    /**
     * Ajouter une marche.
     */
    public function ajouterMarche(): array
    {
        try
        {
            $userId = $this->validateUserId();
            if ($userId === null)
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            $data = $this->extractWalkData();
            $result = $this->walkModel->ajouterMarche($userId, $data);

            if (isset($result['success']))
            {
                $stats = $this->getUpdatedStats($userId, true);
                return array_merge($result, $stats);
            }

            return $result;
        } catch (Exception $e)
        {
            return $this->handleModelException('ajouterMarche', $e);
        }
    }

    /**
     * Modifier une marche (heures, durée, note).
     */
    public function modifierMarche(): array
    {
        try
        {
            $userId = $this->validateUserId();
            if ($userId === null)
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            $walkId = (int)($_POST['walk_id'] ?? 0);
            $error = $this->validateId($walkId, 'ID marche');
            if ($error) return $error;

            $data = [
                'duration_minutes' => (int)($_POST['duration_minutes'] ?? 0),
                'start_time' => $_POST['start_time'] ?? null,
                'end_time' => $_POST['end_time'] ?? null,
                'note' => $_POST['note'] ?? null,
            ];

            $result = $this->walkModel->modifierMarche($userId, $walkId, $data);

            if (isset($result['success']))
            {
                $stats = $this->getUpdatedStats($userId, false);
                return array_merge($result, $stats);
            }

            return $result;
        } catch (Exception $e)
        {
            return $this->handleModelException('modifierMarche', $e);
        }
    }

    /**
     * Supprimer une marche.
     */
    public function supprimerMarche(): array
    {
        try
        {
            $userId = $this->validateUserId();
            if ($userId === null)
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            $walkId = (int)($_POST['walk_id'] ?? $_GET['walk_id'] ?? 0);
            $error = $this->validateId($walkId, 'ID marche');
            if ($error) return $error;

            $result = $this->walkModel->supprimerMarche($userId, $walkId);

            if (isset($result['success']))
            {
                $stats = $this->getUpdatedStats($userId, false);
                return array_merge($result, $stats);
            }

            return $result;
        } catch (Exception $e)
        {
            return $this->handleModelException('supprimerMarche', $e);
        }
    }

    /**
     * Récupérer les marches du jour.
     */
    public function getMarchesAujourdhui(): array
    {
        try
        {
            $userId = $this->validateUserId();
            if ($userId === null)
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            return $this->walkModel->getMarchesAujourdhui($userId);
        } catch (Exception $e)
        {
            return $this->handleModelException('getMarchesAujourdhui', $e);
        }
    }

    /**
     * Récupérer l'historique des marches.
     */
    public function getHistorique(): array
    {
        try
        {
            $userId = $this->validateUserId();
            if ($userId === null)
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            $jours = (int)($_GET['jours'] ?? 7);

            return $this->walkModel->getHistorique($userId, $jours);
        } catch (Exception $e)
        {
            return $this->handleModelException('getHistorique', $e);
        }
    }

    // ================================================================
    // OBJECTIFS
    // ================================================================

    /**
     * Récupérer les objectifs.
     */
    public function getObjectifs(): array
    {
        try
        {
            $userId = $this->validateUserId();
            if ($userId === null)
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            $objectifs = $this->walkModel->getObjectifs($userId);
            $progression = $this->walkModel->getProgression($userId);

            return [
                'success' => true,
                'objectifs' => $objectifs['objectifs'] ?? [],
                'progression' => $progression['progression'] ?? [],
            ];
        } catch (Exception $e)
        {
            return $this->handleModelException('getObjectifs', $e);
        }
    }

    /**
     * Mettre à jour les objectifs.
     */
    public function updateObjectifs(): array
    {
        try
        {
            $userId = $this->validateUserId();
            if ($userId === null)
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            $kmPerDay = (float)($_POST['km_per_day'] ?? 5);
            $daysPerWeek = (int)($_POST['days_per_week'] ?? 4);

            $result = $this->walkModel->updateObjectifs($userId, $kmPerDay, $daysPerWeek);

            if (isset($result['success']))
            {
                $progression = $this->walkModel->getProgression($userId);

                return array_merge($result, [
                    'progression' => $progression['progression'] ?? [],
                ]);
            }

            return $result;
        } catch (Exception $e)
        {
            return $this->handleModelException('updateObjectifs', $e);
        }
    }

    // ================================================================
    // PARCOURS FAVORIS
    // ================================================================

    /**
     * Sauvegarder un parcours favori.
     */
    public function sauvegarderParcours(): array
    {
        try
        {
            $userId = $this->validateUserId();
            if ($userId === null)
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            $name = $_POST['name'] ?? '';
            $distanceKm = (float)($_POST['distance_km'] ?? 0);
            $routePoints = isset($_POST['route_points']) ? json_decode($_POST['route_points'], true) : [];

            if (empty($name))
            {
                return ['error' => 'Nom du parcours requis'];
            }
            if (empty($routePoints))
            {
                return ['error' => 'Tracé du parcours requis'];
            }

            return $this->walkModel->sauvegarderParcours($userId, $name, $distanceKm, $routePoints);
        } catch (Exception $e)
        {
            return $this->handleModelException('sauvegarderParcours', $e);
        }
    }

    /**
     * Récupérer les parcours favoris.
     */
    public function getParcoursFavoris(): array
    {
        try
        {
            $userId = $this->validateUserId();
            if ($userId === null)
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            return $this->walkModel->getParcoursFavoris($userId);
        } catch (Exception $e)
        {
            return $this->handleModelException('getParcoursFavoris', $e);
        }
    }

    /**
     * Supprimer un parcours favori.
     */
    public function supprimerParcours(): array
    {
        try
        {
            $userId = $this->validateUserId();
            if ($userId === null)
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            $routeId = (int)($_POST['route_id'] ?? $_GET['route_id'] ?? 0);
            $error = $this->validateId($routeId, 'ID parcours');
            if ($error) return $error;

            return $this->walkModel->supprimerParcours($userId, $routeId);
        } catch (Exception $e)
        {
            return $this->handleModelException('supprimerParcours', $e);
        }
    }

    // ================================================================
    // STATS & GAMIFICATION
    // ================================================================

    /**
     * Récupérer toutes les données pour la page WalkTrack.
     */
    public function getPageData(): array
    {
        try
        {
            $userId = $this->validateUserId();
            if ($userId === null)
            {
                return ['error' => 'Utilisateur non connecté'];
            }

            // Marches du jour
            $todayData = $this->walkModel->getMarchesAujourdhui($userId);

            // Historique 7 jours
            $historique = $this->walkModel->getHistorique($userId, 7);

            // Objectifs et progression
            $objectifs = $this->walkModel->getObjectifs($userId);
            $progression = $this->walkModel->getProgression($userId);

            // Gamification
            $gamificationStats = $this->walkModel->getGamificationStats($userId);
            $streak = $gamificationStats['streak'];
            $totalJours = $gamificationStats['total_jours'];

            // Badges via GamificationService (méthode spécifique WalkTrack)
            $badges = $this->gamificationService->computeWalkTrackBadges(
                $gamificationStats['streak'],
                $gamificationStats['total_jours'],
                $gamificationStats['total_marches'],
                $gamificationStats['total_km'],
                $gamificationStats['parcours_saved']
            );

            // Parcours favoris
            $parcours = $this->walkModel->getParcoursFavoris($userId);

            // Poids utilisateur pour calcul calories
            $userWeight = $this->walkModel->getPoidsUtilisateur($userId);

            return [
                'success' => true,
                'marches' => $todayData['marches'] ?? [],
                'totals' => $todayData['totals'] ?? [],
                'historique' => $historique['historique'] ?? [],
                'objectifs' => $objectifs['objectifs'] ?? [],
                'progression' => $progression['progression'] ?? [],
                'streak' => $streak,
                'total_jours' => $totalJours,
                'badges' => $badges,
                'parcours' => $parcours['parcours'] ?? [],
                'user_weight' => $userWeight,
            ];
        } catch (Exception $e)
        {
            return $this->handleModelException('getPageData', $e);
        }
    }

    // ================================================================
    // HELPERS PRIVÉS
    // ================================================================

    /**
     * Valider et retourner l'ID utilisateur connecté.
     */
    private function validateUserId(): ?int
    {
        return $this->getUserId();
    }

    /**
     * Valider un ID et retourner une erreur si invalide.
     */
    private function validateId(int $id, string $fieldName): ?array
    {
        if ($id <= 0)
        {
            return ['error' => ucfirst($fieldName) . ' invalide'];
        }
        return null;
    }

    /**
     * Extraire les données d'une marche depuis POST.
     */
    private function extractWalkData(): array
    {
        return [
            'walk_type' => $_POST['walk_type'] ?? 'marche',
            'distance_km' => $_POST['distance_km'] ?? 0,
            'duration_minutes' => $_POST['duration_minutes'] ?? 0,
            'route_points' => isset($_POST['route_points']) ? json_decode($_POST['route_points'], true) : null,
            'note' => $_POST['note'] ?? null,
            'walk_date' => $_POST['walk_date'] ?? date('Y-m-d'),
            'start_time' => $_POST['start_time'] ?? null,
            'end_time' => $_POST['end_time'] ?? null,
        ];
    }

    /**
     * Récupérer les statistiques mises à jour après modification.
     */
    private function getUpdatedStats(int $userId, bool $includeStreak = false): array
    {
        $todayData = $this->walkModel->getMarchesAujourdhui($userId);
        $progression = $this->walkModel->getProgression($userId);

        $stats = [
            'totals' => $todayData['totals'] ?? [],
            'progression' => $progression['progression'] ?? [],
        ];

        if ($includeStreak)
        {
            $streak = $this->walkModel->getStreak($userId);
            $stats['streak'] = $streak;
        }

        return $stats;
    }

    /**
     * Gérer uniformément les exceptions du modèle.
     */
    private function handleModelException(string $methodName, Exception $e): array
    {
        error_log('Erreur WalkTrackController::' . $methodName . ': ' . $e->getMessage());
        return ['error' => 'Erreur lors de ' . strtolower(str_replace('get', 'la récupération de ', str_replace(['ajouter', 'modifier', 'supprimer', 'update'], ['l\'ajout de ', 'la modification de ', 'la suppression de ', 'la mise à jour de '], $methodName)))];
    }
}
