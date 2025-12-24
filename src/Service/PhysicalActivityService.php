<?php

namespace App\Service;

use App\Model\ActivityModel;
use Exception;

/**
 * PhysicalActivityService - Service métier pour la gestion des activités physiques.
 * Responsabilités :
 * - Calculs avancés des calories
 * - Gestion des objectifs d'activité
 * - Statistiques et analyses
 * - Intégration avec le système nutritionnel.
 */
class PhysicalActivityService
{
    private ActivityModel $activityModel;

    // Coefficients de calories par minute pour chaque activité (kcal/min)
    private const CALORIE_COEFFICIENTS = [
        'marche' => 8,      // Marche rapide
        'course' => 12,     // Course
        'velo' => 10,       // Vélo
        'natation' => 13,   // Natation
        'yoga' => 5,        // Yoga
        'musculation' => 6, // Musculation
        'danse' => 7,       // Danse
        'tennis' => 9,      // Tennis
        'football' => 10,   // Football
        'basketball' => 9,  // Basketball
    ];

    public function __construct(ActivityModel $activityModel)
    {
        $this->activityModel = $activityModel;
    }

    /**
     * Calcule les calories estimées pour une activité.
     */
    public function calculerCaloriesEstimees(string $type, int $dureeMinutes): int
    {
        $coefficient = self::CALORIE_COEFFICIENTS[$type] ?? 8; // Défaut marche

        return (int)($coefficient * $dureeMinutes);
    }

    /**
     * Valide les données d'une activité.
     */
    public function validerActivite(string $type, int $duree, ?int $calories): array
    {
        $erreurs = [];

        if (empty($type))
        {
            $erreurs[] = 'Le type d\'activité est requis';
        } elseif (!array_key_exists($type, self::CALORIE_COEFFICIENTS))
        {
            $erreurs[] = 'Type d\'activité invalide';
        }

        if ($duree <= 0 || $duree > 480)
        {
            $erreurs[] = 'La durée doit être entre 1 et 480 minutes';
        }

        if ($calories !== null && ($calories < 0 || $calories > 2000))
        {
            $erreurs[] = 'Les calories doivent être entre 0 et 2000';
        }

        return $erreurs;
    }

    /**
     * Calcule le surplus calorique pour les calories disponibles.
     * Si calories brûlées > 500, ajoute le surplus aux calories disponibles.
     */
    public function calculerSurplusCalorique(int $totalCaloriesBrulees): int
    {
        $seuil = 500; // Seuil minimum pour bonus
        if ($totalCaloriesBrulees > $seuil)
        {
            return $totalCaloriesBrulees - $seuil;
        }

        return 0;
    }

    /**
     * Génère des recommandations d'activités basées sur l'historique.
     */
    public function genererRecommandations(int $userId): array
    {
        try
        {
            $historique = $this->activityModel->getHistoriqueActivites($userId, 30);

            if (!$historique['success'])
            {
                return ['error' => 'Impossible de récupérer l\'historique'];
            }

            $recommandations = [];

            // TODO: Implémenter l'analyse des activités favorites
            // Pour l'instant, recommandation par défaut

            $recommandations[] = [
                'type' => 'marche',
                'duree_suggeree' => 30,
                'raison' => 'Activité douce idéale pour commencer',
            ];

            return [
                'success' => true,
                'recommandations' => $recommandations,
            ];
        } catch (Exception $e)
        {
            error_log('Erreur génération recommandations: ' . $e->getMessage());

            return ['error' => 'Erreur lors de la génération des recommandations'];
        }
    }

    /**
     * Calcule les statistiques hebdomadaires.
     */
    public function calculerStatistiquesHebdomadaires(int $userId): array
    {
        try
        {
            $historique = $this->activityModel->getHistoriqueActivites($userId, 7);

            if (!$historique['success'])
            {
                return $historique;
            }

            $activites = $historique['historique'];
            $stats = [
                'total_jours_activite' => count($activites),
                'moyenne_calories_jour' => 0,
                'total_calories_semaine' => 0,
                'activite_plus_pratiquee' => null,
                'duree_totale' => 0,
            ];

            if (!empty($activites))
            {
                $totalCalories = array_sum(array_column($activites, 'total_calories'));
                $totalDuree = array_sum(array_column($activites, 'total_duree'));

                $stats['moyenne_calories_jour'] = round($totalCalories / count($activites));
                $stats['total_calories_semaine'] = $totalCalories;
                $stats['duree_totale'] = $totalDuree;
            }

            return [
                'success' => true,
                'statistiques' => $stats,
            ];
        } catch (Exception $e)
        {
            error_log('Erreur calcul statistiques: ' . $e->getMessage());

            return ['error' => 'Erreur lors du calcul des statistiques'];
        }
    }

    /**
     * Vérifie si l'utilisateur a atteint ses objectifs hebdomadaires.
     */
    public function verifierObjectifsHebdomadaires(int $userId, int $objectifCalories = 1500): array
    {
        $stats = $this->calculerStatistiquesHebdomadaires($userId);

        if (!$stats['success'])
        {
            return $stats;
        }

        $totalCalories = $stats['statistiques']['total_calories_semaine'];
        $atteint = $totalCalories >= $objectifCalories;

        return [
            'success' => true,
            'objectif_atteint' => $atteint,
            'calories_actuelles' => $totalCalories,
            'calories_objectif' => $objectifCalories,
            'pourcentage' => $objectifCalories > 0 ? min(100, round(($totalCalories / $objectifCalories) * 100)) : 0,
        ];
    }
}
