<?php

namespace App\Service;

/**
 * NAFLDAdviceService - Service pour générer des conseils personnalisés NAFLD
 * Basé sur IMC, âge, objectifs et données nutritionnelles actuelles.
 */
class NAFLDAdviceService
{
    private \App\Model\UserConfigModel $userConfigModel;

    public function __construct(?\App\Model\UserConfigModel $userConfigModel = null)
    {
        $this->userConfigModel = $userConfigModel ?? new \App\Model\UserConfigModel();
    }

    /**
     * Vérifie si l'utilisateur a des conditions médicales déclarées.
     */
    protected function hasMedicalConditions(int $userId): bool
    {
        $cardiac = $this->userConfigModel->get($userId, 'medical_cardiac');
        $diabetes = $this->userConfigModel->get($userId, 'medical_diabetes');
        $other = $this->userConfigModel->get($userId, 'medical_other');

        return $cardiac || $diabetes || $other;
    }

    /**
     * Génère une liste de conseils personnalisés pour l'utilisateur.
     *
     * @param array $user Données utilisateur
     * @param array $objectifs Objectifs nutritionnels
     * @param array $currentNutrition Nutrition actuelle du jour
     * @param array $stats Statistiques utilisateur
     * @return array Liste des conseils avec icônes et couleurs
     */
    public function generatePersonalizedAdvice(array $user, array $objectifs, array $currentNutrition, array $stats): array
    {
        $userId = $user['id'] ?? 0;
        if (!$userId)
        {
            return [];
        }

        $advice = [];

        // Vérifier conditions médicales pour disclaimer
        $hasMedicalConditions = $this->hasMedicalConditions($userId);
        if ($hasMedicalConditions)
        {
            $advice[] = [
                'text' => 'Vous avez indiqué des conditions médicales. Ces conseils sont généraux et ne remplacent pas l\'avis d\'un professionnel de santé. Consultez votre médecin avant tout changement.',
                'icon' => 'fa-triangle-exclamation',
                'color' => 'red',
                'priority' => 10,
            ];
        }

        // Analyse IMC
        $imc = $objectifs['imc'] ?? 0;
        $imcCategory = $this->getIMCCategory($imc, $userId);

        // Analyse âge (si disponible)
        $age = $this->calculateAge($user['date_naissance'] ?? null);

        // Analyse objectifs atteints
        $objectivesAnalysis = $this->analyzeObjectives($currentNutrition, $objectifs, $userId);

        // Analyse activité physique
        $activityAnalysis = $this->analyzePhysicalActivity($stats);

        // Générer conseils prioritaires
        $advice = array_merge(
            $advice,
            $this->getIMCBasedAdvice($imcCategory, $imc, $userId),
            $this->getNutritionBasedAdvice($currentNutrition, $objectifs, $objectivesAnalysis, $userId),
            $this->getActivityAdvice($activityAnalysis, $userId),
            $this->getAgeBasedAdvice($age),
            $this->getGeneralNAFLDAdvice()
        );

        // Limiter à 5 conseils maximum, triés par priorité
        return array_slice($this->sortAdviceByPriority($advice), 0, 5);
    }

    /**
     * Détermine la catégorie IMC avec seuils personnalisables.
     */
    private function getIMCCategory(float $imc, int $userId): string
    {
        $seuilSousPoids = $this->userConfigModel->get($userId, 'imc_seuil_sous_poids');
        $seuilNormal = $this->userConfigModel->get($userId, 'imc_seuil_normal');
        $seuilSurpoids = $this->userConfigModel->get($userId, 'imc_seuil_surpoids');

        if ($imc < $seuilSousPoids)
        {
            return 'underweight';
        }
        if ($imc < $seuilNormal)
        {
            return 'normal';
        }
        if ($imc < $seuilSurpoids)
        {
            return 'overweight';
        }

        return 'obese';
    }

    /**
     * Calcule l'âge approximatif.
     */
    private function calculateAge(?string $birthDate): ?int
    {
        if (!$birthDate)
        {
            return null;
        }

        $birth = new \DateTime($birthDate);
        $now = new \DateTime();

        return $now->diff($birth)->y;
    }

    /**
     * Analyse l'atteinte des objectifs.
     */
    private function analyzeObjectives(array $current, array $objectifs, int $userId): array
    {
        $lipidesMax = $this->userConfigModel->get($userId, 'lipides_max_g');
        $sucresMax = $this->userConfigModel->get($userId, 'sucres_max_g');

        return [
            'calories' => ($current['calories'] ?? 0) >= ($objectifs['calories_perte'] ?? 0),
            'proteines' => ($current['proteines'] ?? 0) >= ($objectifs['proteines_min'] ?? 0),
            'fibres' => ($current['fibres'] ?? 0) >= ($objectifs['fibres_min'] ?? 0),
            'lipides' => ($current['graisses_sat'] ?? 0) <= $lipidesMax,
            'sucres' => ($current['sucres'] ?? 0) <= $sucresMax,
        ];
    }

    /**
     * Analyse l'activité physique (basé sur les stats disponibles).
     */
    private function analyzePhysicalActivity(array $stats): array
    {
        // Pour l'instant, on suppose que si l'utilisateur a des activités, c'est positif
        // À améliorer avec de vraies données d'activité
        $hasRecentActivity = isset($stats['last_activity_days']) && $stats['last_activity_days'] <= 2;

        return [
            'has_recent_activity' => $hasRecentActivity,
            'needs_more' => !$hasRecentActivity,
        ];
    }

    /**
     * Conseils basés sur l'IMC.
     */
    private function getIMCBasedAdvice(string $category, float $imc, int $userId): array
    {
        $advice = [];
        $hasMedical = $this->hasMedicalConditions($userId);

        switch ($category)
        {
            case 'underweight':
                $advice[] = [
                    'text' => 'Votre IMC indique une insuffisance pondérale. Augmentez progressivement vos apports caloriques avec des aliments riches en nutriments.',
                    'icon' => 'fa-weight-scale',
                    'color' => 'blue',
                    'priority' => 9,
                ];

                break;

            case 'normal':
                $advice[] = [
                    'text' => 'Votre IMC est dans la zone normale. Maintenez ce bon équilibre avec une alimentation équilibrée.',
                    'icon' => 'fa-check-circle',
                    'color' => 'green',
                    'priority' => 5,
                ];

                break;

            case 'overweight':
                $text = 'Votre IMC indique un surpoids. Concentrez-vous sur un déficit calorique modéré';
                if (!$hasMedical)
                {
                    $text .= ' et une activité physique régulière.';
                } else
                {
                    $text .= '. Consultez un professionnel pour des recommandations adaptées.';
                }
                $advice[] = [
                    'text' => $text,
                    'icon' => 'fa-exclamation-triangle',
                    'color' => 'orange',
                    'priority' => 8,
                ];

                break;

            case 'obese':
                $advice[] = [
                    'text' => 'Votre IMC indique une obésité. Consultez un professionnel de santé pour un accompagnement personnalisé.',
                    'icon' => 'fa-triangle-exclamation',
                    'color' => 'red',
                    'priority' => 10,
                ];

                break;
        }

        return $advice;
    }

    /**
     * Conseils basés sur la nutrition actuelle.
     */
    private function getNutritionBasedAdvice(array $current, array $objectifs, array $analysis, int $userId): array
    {
        $advice = [];

        if (!$analysis['calories'])
        {
            $advice[] = [
                'text' => 'Vos apports caloriques sont insuffisants aujourd\'hui. Augmentez les portions ou ajoutez des collations saines.',
                'icon' => 'fa-fire',
                'color' => 'orange',
                'priority' => 7,
            ];
        }

        if (!$analysis['proteines'])
        {
            $advice[] = [
                'text' => 'Augmentez les protéines : viande maigre, poisson, œufs, légumineuses ou produits laitiers.',
                'icon' => 'fa-drumstick-bite',
                'color' => 'blue',
                'priority' => 6,
            ];
        }

        if (!$analysis['fibres'])
        {
            $advice[] = [
                'text' => 'Mangez plus de fibres : légumes, fruits, céréales complètes pour améliorer votre santé hépatique.',
                'icon' => 'fa-leaf',
                'color' => 'green',
                'priority' => 6,
            ];
        }

        if (!$analysis['lipides'])
        {
            $lipidesMax = $this->userConfigModel->get($userId, 'lipides_max_g');
            $advice[] = [
                'text' => "Réduisez les graisses saturées : limitez les viandes grasses, charcuteries et produits transformés (max {$lipidesMax}g/jour).",
                'icon' => 'fa-oil-can',
                'color' => 'red',
                'priority' => 8,
            ];
        }

        if (!$analysis['sucres'])
        {
            $sucresMax = $this->userConfigModel->get($userId, 'sucres_max_g');
            $advice[] = [
                'text' => "Limitez les sucres rapides à {$sucresMax}g/jour maximum pour protéger votre foie.",
                'icon' => 'fa-candy-cane',
                'color' => 'red',
                'priority' => 7,
            ];
        }

        return $advice;
    }

    /**
     * Conseils d'activité physique.
     */
    private function getActivityAdvice(array $activityAnalysis, int $userId): array
    {
        $hasMedical = $this->hasMedicalConditions($userId);

        if ($activityAnalysis['needs_more'])
        {
            $text = 'L\'activité physique peut aider votre santé hépatique.';
            if (!$hasMedical)
            {
                $text .= ' Envisagez 30 minutes de marche quotidienne.';
            } else
            {
                $text .= ' Discutez avec votre médecin des activités adaptées à votre condition.';
            }

            return [[
                'text' => $text,
                'icon' => 'fa-person-walking',
                'color' => 'green',
                'priority' => 6,
            ]];
        }

        return [[
            'text' => 'Activité physique régulière détectée. Continuez vos efforts pour maintenir votre forme !',
            'icon' => 'fa-person-running',
            'color' => 'green',
            'priority' => 4,
        ]];
    }

    /**
     * Conseils basés sur l'âge.
     */
    private function getAgeBasedAdvice(?int $age): array
    {
        if ($age === null)
        {
            return [];
        }

        if ($age > 50)
        {
            return [[
                'text' => 'À votre âge, surveillez particulièrement votre métabolisme. Des repas équilibrés sont essentiels.',
                'icon' => 'fa-user-clock',
                'color' => 'purple',
                'priority' => 5,
            ]];
        }

        return [];
    }

    /**
     * Conseils généraux NAFLD.
     */
    private function getGeneralNAFLDAdvice(): array
    {
        return [
            [
                'text' => 'Privilégiez les graisses insaturées (huile d\'olive, avocats) aux graisses saturées.',
                'icon' => 'fa-oil-can',
                'color' => 'orange',
                'priority' => 4,
            ],
            [
                'text' => 'Limitez les sucres rapides à 50g/jour maximum pour protéger votre foie.',
                'icon' => 'fa-candy-cane',
                'color' => 'red',
                'priority' => 5,
            ],
        ];
    }

    /**
     * Trie les conseils par priorité (décroissante).
     */
    private function sortAdviceByPriority(array $advice): array
    {
        usort($advice, fn ($a, $b) => $b['priority'] <=> $a['priority']);

        return $advice;
    }
}
