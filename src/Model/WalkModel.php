<?php

namespace App\Model;

use Exception;
use PDO;

/**
 * WalkModel - Modèle pour la gestion des marches (WalkTrack).
 *
 * Responsabilités :
 * - CRUD des marches
 * - Gestion des objectifs personnalisés
 * - Calculs des calories (basés sur MET)
 * - Gestion des parcours favoris
 * - Statistiques et historique
 */
class WalkModel
{
    private $db;

    // Valeurs MET (Metabolic Equivalent of Task) pour les activités de marche
    // Source: Compendium of Physical Activities
    private const MET_VALUES = [
        'marche' => 3.5,        // Marche normale 4-5 km/h
        'marche_rapide' => 5.0, // Marche rapide 6-7 km/h
    ];

    // Poids par défaut si non renseigné (kg)
    private const DEFAULT_WEIGHT = 70;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ================================================================
    // CRUD MARCHES
    // ================================================================

    /**
     * Ajouter une marche.
     */
    public function ajouterMarche(int $userId, array $data): array
    {
        try
        {
            $type = $data['walk_type'] ?? 'marche';
            $distance = (float)($data['distance_km'] ?? 0);
            $duration = (int)($data['duration_minutes'] ?? 0);
            $routePoints = $data['route_points'] ?? null;
            $note = $data['note'] ?? null;
            $walkDate = $data['walk_date'] ?? date('Y-m-d');
            $startTime = !empty($data['start_time']) ? $data['start_time'] : null;
            $endTime = !empty($data['end_time']) ? $data['end_time'] : null;

            // Validation
            if ($distance <= 0 || $distance > 100)
            {
                return ['error' => 'Distance invalide (0.1 - 100 km)'];
            }
            if ($duration <= 0 || $duration > 600)
            {
                return ['error' => 'Durée invalide (1 - 600 minutes)'];
            }

            // Calculer les calories avec le poids de l'utilisateur
            $calories = $this->calculerCalories($type, $duration, $userId);

            // Encoder les points du parcours en JSON
            $routePointsJson = $routePoints ? json_encode($routePoints) : null;

            $stmt = $this->db->prepare('
                INSERT INTO walks (user_id, walk_type, distance_km, duration_minutes, calories_burned, route_points, note, walk_date, start_time, end_time)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $stmt->execute([
                $userId,
                $type,
                $distance,
                $duration,
                $calories,
                $routePointsJson,
                $note,
                $walkDate,
                $startTime,
                $endTime,
            ]);

            return [
                'success' => true,
                'walk_id' => (int)$this->db->lastInsertId(),
                'calories' => $calories,
                'message' => 'Marche ajoutée avec succès',
            ];
        } catch (Exception $e)
        {
            error_log('Erreur ajout marche: ' . $e->getMessage());

            return ['error' => 'Erreur lors de l\'ajout de la marche'];
        }
    }

    /**
     * Modifier une marche (heures, durée, note).
     */
    public function modifierMarche(int $userId, int $walkId, array $data): array
    {
        try
        {
            $duration = (int)($data['duration_minutes'] ?? 0);
            $startTime = !empty($data['start_time']) ? $data['start_time'] : null;
            $endTime = !empty($data['end_time']) ? $data['end_time'] : null;
            $note = $data['note'] ?? null;

            // Validation
            if ($duration <= 0 || $duration > 600)
            {
                return ['error' => 'Durée invalide (1 - 600 minutes)'];
            }

            // Récupérer le type de marche pour recalculer les calories
            $stmt = $this->db->prepare('SELECT walk_type FROM walks WHERE id = ? AND user_id = ?');
            $stmt->execute([$walkId, $userId]);
            $walk = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$walk)
            {
                return ['error' => 'Marche non trouvée'];
            }

            // Recalculer les calories avec la nouvelle durée
            $calories = $this->calculerCalories($walk['walk_type'], $duration, $userId);

            // Mise à jour
            $stmt = $this->db->prepare('
                UPDATE walks 
                SET duration_minutes = ?, 
                    calories_burned = ?,
                    start_time = ?, 
                    end_time = ?, 
                    note = ?,
                    updated_at = datetime(\'now\')
                WHERE id = ? AND user_id = ?
            ');

            $stmt->execute([
                $duration,
                $calories,
                $startTime,
                $endTime,
                $note,
                $walkId,
                $userId,
            ]);

            if ($stmt->rowCount() === 0)
            {
                return ['error' => 'Marche non trouvée ou non modifiée'];
            }

            return [
                'success' => true,
                'calories' => $calories,
                'message' => 'Marche modifiée avec succès',
            ];
        } catch (Exception $e)
        {
            error_log('Erreur modification marche: ' . $e->getMessage());

            return ['error' => 'Erreur lors de la modification'];
        }
    }

    /**
     * Supprimer une marche.
     */
    public function supprimerMarche(int $userId, int $walkId): array
    {
        try
        {
            $stmt = $this->db->prepare('
                DELETE FROM walks WHERE id = ? AND user_id = ?
            ');
            $stmt->execute([$walkId, $userId]);

            if ($stmt->rowCount() === 0)
            {
                return ['error' => 'Marche non trouvée'];
            }

            return ['success' => true, 'message' => 'Marche supprimée'];
        } catch (Exception $e)
        {
            error_log('Erreur suppression marche: ' . $e->getMessage());

            return ['error' => 'Erreur lors de la suppression'];
        }
    }

    /**
     * Récupérer les marches du jour.
     */
    public function getMarchesAujourdhui(int $userId): array
    {
        try
        {
            $today = date('Y-m-d');
            $stmt = $this->db->prepare('
                SELECT id, walk_type, distance_km, duration_minutes, calories_burned, route_points, note, walk_date, start_time, end_time, created_at
                FROM walks
                WHERE user_id = ? AND walk_date = ?
                ORDER BY created_at DESC
            ');
            $stmt->execute([$userId, $today]);
            $marches = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Décoder les route_points JSON
            foreach ($marches as &$marche)
            {
                if ($marche['route_points'])
                {
                    $marche['route_points'] = json_decode($marche['route_points'], true);
                }
            }

            // Calculer les totaux
            $totalKm = array_sum(array_column($marches, 'distance_km'));
            $totalDuration = array_sum(array_column($marches, 'duration_minutes'));
            $totalCalories = array_sum(array_column($marches, 'calories_burned'));

            return [
                'success' => true,
                'marches' => $marches,
                'totals' => [
                    'distance_km' => round($totalKm, 2),
                    'duration_minutes' => $totalDuration,
                    'calories' => $totalCalories,
                    'count' => count($marches),
                ],
            ];
        } catch (Exception $e)
        {
            error_log('Erreur récupération marches: ' . $e->getMessage());

            return ['error' => 'Erreur lors de la récupération des marches'];
        }
    }

    /**
     * Récupérer l'historique des marches sur X jours.
     */
    public function getHistorique(int $userId, int $jours = 7): array
    {
        try
        {
            $stmt = $this->db->prepare("
                SELECT 
                    walk_date as date,
                    SUM(distance_km) as total_km,
                    SUM(duration_minutes) as total_duree,
                    SUM(calories_burned) as total_calories,
                    COUNT(*) as nombre_marches
                FROM walks
                WHERE user_id = ? AND walk_date >= date('now', '-' || ? || ' days')
                GROUP BY walk_date
                ORDER BY walk_date DESC
            ");
            $stmt->execute([$userId, $jours]);
            $historique = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'historique' => $historique,
            ];
        } catch (Exception $e)
        {
            error_log('Erreur historique marches: ' . $e->getMessage());

            return ['error' => 'Erreur lors de la récupération de l\'historique'];
        }
    }

    // ================================================================
    // OBJECTIFS
    // ================================================================

    /**
     * Récupérer les objectifs de l'utilisateur.
     */
    public function getObjectifs(int $userId): array
    {
        try
        {
            $stmt = $this->db->prepare('
                SELECT km_per_day, days_per_week FROM walk_objectives WHERE user_id = ?
            ');
            $stmt->execute([$userId]);
            $objectifs = $stmt->fetch(PDO::FETCH_ASSOC);

            // Valeurs par défaut si pas encore configuré
            if (!$objectifs)
            {
                $objectifs = [
                    'km_per_day' => 5.00,
                    'days_per_week' => 4,
                ];
            }

            return [
                'success' => true,
                'objectifs' => $objectifs,
            ];
        } catch (Exception $e)
        {
            error_log('Erreur récupération objectifs: ' . $e->getMessage());

            return ['error' => 'Erreur lors de la récupération des objectifs'];
        }
    }

    /**
     * Mettre à jour les objectifs de l'utilisateur.
     */
    public function updateObjectifs(int $userId, float $kmPerDay, int $daysPerWeek): array
    {
        try
        {
            // Validation
            if ($kmPerDay <= 0 || $kmPerDay > 50)
            {
                return ['error' => 'Objectif km/jour invalide (0.1 - 50 km)'];
            }
            if ($daysPerWeek < 1 || $daysPerWeek > 7)
            {
                return ['error' => 'Nombre de jours invalide (1 - 7)'];
            }

            $now = (new \DateTime())->format('Y-m-d H:i:s');

            // Upsert (insert or update)
            // Vérifier si un objectif existe déjà pour cet utilisateur
            $checkStmt = $this->db->prepare('SELECT id FROM walk_objectives WHERE user_id = ?');
            $checkStmt->execute([$userId]);
            $existingId = $checkStmt->fetchColumn();

            if ($existingId)
            {
                // Mettre à jour l'objectif existant
                $stmt = $this->db->prepare('UPDATE walk_objectives SET km_per_day = ?, days_per_week = ?, updated_at = ? WHERE user_id = ?');
                $stmt->execute([$kmPerDay, $daysPerWeek, $now, $userId]);
            } else
            {
                // Insérer un nouvel objectif
                $stmt = $this->db->prepare('INSERT INTO walk_objectives (user_id, km_per_day, days_per_week) VALUES (?, ?, ?)');
                $stmt->execute([$userId, $kmPerDay, $daysPerWeek]);
            }

            return [
                'success' => true,
                'message' => 'Objectifs mis à jour',
            ];
        } catch (Exception $e)
        {
            error_log('Erreur mise à jour objectifs: ' . $e->getMessage());

            return ['error' => 'Erreur lors de la mise à jour des objectifs'];
        }
    }

    /**
     * Calculer la progression vers les objectifs.
     */
    public function getProgression(int $userId): array
    {
        try
        {
            $objectifs = $this->getObjectifs($userId);
            if (!$objectifs['success'])
            {
                return $objectifs;
            }

            $kmPerDay = (float)$objectifs['objectifs']['km_per_day'];
            $daysPerWeek = (int)$objectifs['objectifs']['days_per_week'];

            // Stats du jour
            $today = $this->getMarchesAujourdhui($userId);
            $todayKm = $today['success'] ? $today['totals']['distance_km'] : 0;

            // Stats de la semaine (lundi à aujourd'hui)
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(DISTINCT walk_date) as jours_actifs,
                    SUM(distance_km) as total_km
                FROM walks
                WHERE user_id = ? AND walk_date >= date('now', '-7 days', 'weekday 0')
            ");
            $stmt->execute([$userId]);
            $weekStats = $stmt->fetch(PDO::FETCH_ASSOC);

            $weekKm = (float)($weekStats['total_km'] ?? 0);
            $joursActifs = (int)($weekStats['jours_actifs'] ?? 0);

            // Calcul des progressions
            $progressionJour = $kmPerDay > 0 ? min(100, round(($todayKm / $kmPerDay) * 100)) : 0;
            $objectifSemaine = $kmPerDay * $daysPerWeek;
            $progressionSemaine = $objectifSemaine > 0 ? min(100, round(($weekKm / $objectifSemaine) * 100)) : 0;

            return [
                'success' => true,
                'progression' => [
                    'jour' => [
                        'actuel' => round($todayKm, 2),
                        'objectif' => $kmPerDay,
                        'pourcentage' => $progressionJour,
                        'atteint' => $todayKm >= $kmPerDay,
                    ],
                    'semaine' => [
                        'actuel' => round($weekKm, 2),
                        'objectif' => $objectifSemaine,
                        'pourcentage' => $progressionSemaine,
                        'jours_actifs' => $joursActifs,
                        'jours_objectif' => $daysPerWeek,
                        'atteint' => $weekKm >= $objectifSemaine,
                    ],
                ],
            ];
        } catch (Exception $e)
        {
            error_log('Erreur calcul progression: ' . $e->getMessage());

            return ['error' => 'Erreur lors du calcul de la progression'];
        }
    }

    // ================================================================
    // PARCOURS FAVORIS
    // ================================================================

    /**
     * Sauvegarder un parcours favori.
     */
    public function sauvegarderParcours(int $userId, string $name, float $distanceKm, array $routePoints): array
    {
        try
        {
            if (empty($name) || strlen($name) > 100)
            {
                return ['error' => 'Nom du parcours invalide'];
            }

            $stmt = $this->db->prepare('
                INSERT INTO walk_routes (user_id, name, distance_km, route_points)
                VALUES (?, ?, ?, ?)
            ');
            $stmt->execute([$userId, $name, $distanceKm, json_encode($routePoints)]);

            return [
                'success' => true,
                'route_id' => (int)$this->db->lastInsertId(),
                'message' => 'Parcours sauvegardé',
            ];
        } catch (Exception $e)
        {
            error_log('Erreur sauvegarde parcours: ' . $e->getMessage());

            return ['error' => 'Erreur lors de la sauvegarde du parcours'];
        }
    }

    /**
     * Récupérer les parcours favoris de l'utilisateur.
     */
    public function getParcoursFavoris(int $userId): array
    {
        try
        {
            $stmt = $this->db->prepare('
                SELECT id, name, distance_km, route_points, created_at
                FROM walk_routes
                WHERE user_id = ?
                ORDER BY name ASC
            ');
            $stmt->execute([$userId]);
            $parcours = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Décoder les route_points JSON
            foreach ($parcours as &$p)
            {
                $p['route_points'] = json_decode($p['route_points'], true);
            }

            return [
                'success' => true,
                'parcours' => $parcours,
            ];
        } catch (Exception $e)
        {
            error_log('Erreur récupération parcours: ' . $e->getMessage());

            return ['error' => 'Erreur lors de la récupération des parcours'];
        }
    }

    /**
     * Supprimer un parcours favori.
     */
    public function supprimerParcours(int $userId, int $routeId): array
    {
        try
        {
            $stmt = $this->db->prepare('
                DELETE FROM walk_routes WHERE id = ? AND user_id = ?
            ');
            $stmt->execute([$routeId, $userId]);

            if ($stmt->rowCount() === 0)
            {
                return ['error' => 'Parcours non trouvé'];
            }

            return ['success' => true, 'message' => 'Parcours supprimé'];
        } catch (Exception $e)
        {
            error_log('Erreur suppression parcours: ' . $e->getMessage());

            return ['error' => 'Erreur lors de la suppression'];
        }
    }

    // ================================================================
    // UTILITAIRES
    // ================================================================

    /**
     * Récupérer le poids de l'utilisateur depuis son profil (méthode publique).
     */
    public function getPoidsUtilisateur(int $userId): float
    {
        return $this->getUserWeight($userId);
    }

    /**
     * Récupérer le poids de l'utilisateur depuis son profil.
     * Cherche d'abord dans objectifs_nutrition (IMC), puis profiles.
     */
    private function getUserWeight(int $userId): float
    {
        try
        {
            // D'abord chercher dans objectifs_nutrition (données IMC)
            $stmt = $this->db->prepare('
                SELECT poids FROM objectifs_nutrition 
                WHERE user_id = ? AND poids > 0 
                ORDER BY created_at DESC LIMIT 1
            ');
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && $result['poids'] > 0)
            {
                return (float)$result['poids'];
            }

            // Sinon chercher dans profiles
            $stmt = $this->db->prepare('SELECT poids_kg FROM profiles WHERE user_id = ? ORDER BY id DESC LIMIT 1');
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && $result['poids_kg'] > 0)
            {
                return (float)$result['poids_kg'];
            }
        } catch (Exception $e)
        {
            error_log('Erreur récupération poids: ' . $e->getMessage());
        }

        return self::DEFAULT_WEIGHT;
    }

    /**
     * Calculer les calories brûlées avec la vraie formule MET.
     * Formule: Calories = MET × poids(kg) × durée(heures).
     *
     * @param string $type Type de marche (marche, marche_rapide)
     * @param int $durationMinutes Durée en minutes
     * @param int|null $userId ID utilisateur pour récupérer le poids (optionnel)
     * @return int Calories brûlées
     */
    public function calculerCalories(string $type, int $durationMinutes, ?int $userId = null): int
    {
        $met = self::MET_VALUES[$type] ?? self::MET_VALUES['marche'];
        $poids = $userId ? $this->getUserWeight($userId) : self::DEFAULT_WEIGHT;
        $durationHours = $durationMinutes / 60;

        // Formule MET: Calories = MET × poids × durée(h)
        return (int)round($met * $poids * $durationHours);
    }

    /**
     * Compter les jours consécutifs avec marche (streak).
     */
    public function getStreak(int $userId): int
    {
        try
        {
            $stmt = $this->db->prepare('
                SELECT DISTINCT walk_date 
                FROM walks 
                WHERE user_id = ? 
                ORDER BY walk_date DESC
            ');
            $stmt->execute([$userId]);
            $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($dates))
            {
                return 0;
            }

            $streak = 0;
            $expectedDate = date('Y-m-d');

            foreach ($dates as $date)
            {
                if ($date === $expectedDate)
                {
                    $streak++;
                    $expectedDate = date('Y-m-d', strtotime($expectedDate . ' -1 day'));
                } elseif ($date === date('Y-m-d', strtotime($expectedDate . ' -1 day')))
                {
                    // Permet de commencer le streak hier si pas encore marché aujourd'hui
                    $streak++;
                    $expectedDate = date('Y-m-d', strtotime($date . ' -1 day'));
                } else
                {
                    break;
                }
            }

            return $streak;
        } catch (Exception $e)
        {
            error_log('Erreur calcul streak: ' . $e->getMessage());

            return 0;
        }
    }

    /**
     * Compter le total de jours avec marche.
     */
    public function getTotalJoursMarche(int $userId): int
    {
        try
        {
            $stmt = $this->db->prepare('
                SELECT COUNT(DISTINCT walk_date) as total FROM walks WHERE user_id = ?
            ');
            $stmt->execute([$userId]);

            return (int)$stmt->fetchColumn();
        } catch (Exception $e)
        {
            return 0;
        }
    }

    /**
     * Compter le nombre total de marches.
     */
    public function getTotalMarches(int $userId): int
    {
        try
        {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM walks WHERE user_id = ?');
            $stmt->execute([$userId]);

            return (int)$stmt->fetchColumn();
        } catch (Exception $e)
        {
            return 0;
        }
    }

    /**
     * Obtenir le total de kilomètres parcourus.
     */
    public function getTotalKilometres(int $userId): float
    {
        try
        {
            $stmt = $this->db->prepare('SELECT COALESCE(SUM(distance_km), 0) FROM walks WHERE user_id = ?');
            $stmt->execute([$userId]);

            return (float)$stmt->fetchColumn();
        } catch (Exception $e)
        {
            return 0.0;
        }
    }

    /**
     * Compter le nombre de parcours favoris sauvegardés.
     */
    public function countParcoursFavoris(int $userId): int
    {
        try
        {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM walk_routes WHERE user_id = ?');
            $stmt->execute([$userId]);

            return (int)$stmt->fetchColumn();
        } catch (Exception $e)
        {
            return 0;
        }
    }

    /**
     * Obtenir toutes les stats pour la gamification.
     */
    public function getGamificationStats(int $userId): array
    {
        return [
            'streak' => $this->getStreak($userId),
            'total_jours' => $this->getTotalJoursMarche($userId),
            'total_marches' => $this->getTotalMarches($userId),
            'total_km' => $this->getTotalKilometres($userId),
            'parcours_saved' => $this->countParcoursFavoris($userId),
        ];
    }
}
