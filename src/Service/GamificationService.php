<?php

namespace App\Service;

/**
 * Service générique pour la gamification
 * - Streak, score hebdomadaire, grades
 * - Réutilisable sur toutes les pages (repas, activité, etc.).
 */
class GamificationService
{
    /**
     * Calcule le streak (jours consécutifs avec objectif atteint).
     *
     * @param array $historique Historique trié du plus récent au plus ancien
     * @param callable $isGoalReached Fonction qui détermine si l'objectif est atteint pour un jour
     * @return int Nombre de jours consécutifs
     */
    public function computeStreak(array $historique, callable $isGoalReached): int
    {
        $streak = 0;
        foreach ($historique as $jour)
        {
            if ($isGoalReached($jour))
            {
                $streak++;
            } else
            {
                break;
            }
        }

        return $streak;
    }

    /**
     * Calcule le score hebdomadaire générique.
     *
     * @param array $historique Historique des 7 derniers jours
     * @param callable $isGoalReached Fonction qui détermine si l'objectif est atteint
     * @param callable|null $getVolumeRatio Fonction optionnelle pour calculer le ratio de volume (0-1)
     * @return array Score, grade, et statistiques
     */
    public function computeWeeklyScore(
        array $historique,
        callable $isGoalReached,
        ?callable $getVolumeRatio = null
    ): array {
        $weeklyData = array_slice($historique, 0, 7);
        $daysWithGoal = 0;

        foreach ($weeklyData as $jour)
        {
            if ($isGoalReached($jour))
            {
                $daysWithGoal++;
            }
        }

        // Score basé sur les jours (70%) + volume optionnel (30%)
        $daysScore = (count($weeklyData) > 0)
            ? ($daysWithGoal / 7) * 70
            : 0;

        $volumeScore = 0;
        if ($getVolumeRatio !== null)
        {
            $volumeScore = min(30, $getVolumeRatio($weeklyData) * 30);
        }

        $score = (int)round($daysScore + $volumeScore);
        $grade = $this->getWeeklyGrade($score);

        return [
            'score' => $score,
            'grade' => $grade,
            'daysWithGoal' => $daysWithGoal,
            'totalDays' => count($weeklyData),
        ];
    }

    /**
     * Convertit un score en grade (A-F) avec couleurs.
     */
    public function getWeeklyGrade(int $score): array
    {
        return match (true)
        {
            $score >= 90 => [
                'grade' => 'A',
                'color' => 'text-green-600',
                'bg' => 'bg-green-100',
                'label' => 'Excellent !',
            ],
            $score >= 75 => [
                'grade' => 'B',
                'color' => 'text-blue-600',
                'bg' => 'bg-blue-100',
                'label' => 'Très bien',
            ],
            $score >= 60 => [
                'grade' => 'C',
                'color' => 'text-yellow-600',
                'bg' => 'bg-yellow-100',
                'label' => 'Bien',
            ],
            $score >= 40 => [
                'grade' => 'D',
                'color' => 'text-orange-600',
                'bg' => 'bg-orange-100',
                'label' => 'À améliorer',
            ],
            default => [
                'grade' => 'E',
                'color' => 'text-red-600',
                'bg' => 'bg-red-100',
                'label' => 'Attention',
            ],
        };
    }

    /**
     * Compte le total des jours avec objectif atteint.
     */
    public function countDaysWithGoal(array $historique, callable $isGoalReached): int
    {
        $count = 0;
        foreach ($historique as $jour)
        {
            if ($isGoalReached($jour))
            {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Calcule les badges génériques basés sur le streak.
     *
     * @param int $streak Streak actuel
     * @param int $totalDaysWithGoal Total des jours avec objectif atteint
     * @param string $category Catégorie (repas, activite)
     * @return array Badges earned et toEarn
     */
    public function computeStreakBadges(int $streak, int $totalDaysWithGoal, string $category): array
    {
        $badgesEarned = [];
        $badgesToEarn = [];

        $categoryLabels = [
            'repas' => 'ton objectif nutritionnel',
            'activite' => "ton objectif d'activité",
        ];
        $goalLabel = $categoryLabels[$category] ?? 'ton objectif';

        // Badge: 3 jours de suite
        $this->addBadge(
            $streak >= 3,
            $badgesEarned,
            $badgesToEarn,
            [
                'id' => '3days',
                'icon' => '⭐',
                'label' => '3 jours de suite',
                'condition' => "Atteindre {$goalLabel} 3 jours consécutifs",
                'tip' => 'Tu as pris le rythme, bravo !',
            ],
            [
                'progress' => $streak,
                'target' => 3,
                'hint' => $streak . '/3 jours',
                'tip' => 'Encore ' . max(0, 3 - $streak) . ' jour(s) pour débloquer !',
            ]
        );

        // Badge: Semaine parfaite (7 jours)
        $this->addBadge(
            $streak >= 7,
            $badgesEarned,
            $badgesToEarn,
            [
                'id' => 'week',
                'icon' => '🏆',
                'label' => 'Semaine parfaite',
                'condition' => "Atteindre {$goalLabel} 7 jours de suite",
                'tip' => 'La régularité est la clé pour la santé !',
            ],
            [
                'progress' => $streak,
                'target' => 7,
                'hint' => $streak . '/7 jours',
                'tip' => 'Continue comme ça, plus que ' . max(0, 7 - $streak) . ' jour(s) !',
            ]
        );

        // Badge: 30 jours total
        $this->addBadge(
            $totalDaysWithGoal >= 30,
            $badgesEarned,
            $badgesToEarn,
            [
                'id' => '30days',
                'icon' => '💎',
                'label' => '30 jours total',
                'condition' => "Atteindre {$goalLabel} 30 jours au total",
                'tip' => 'Tu es un champion !',
            ],
            [
                'progress' => $totalDaysWithGoal,
                'target' => 30,
                'hint' => $totalDaysWithGoal . '/30 jours',
                'tip' => 'Encore ' . max(0, 30 - $totalDaysWithGoal) . ' jour(s) !',
            ]
        );

        return [
            'earned' => $badgesEarned,
            'toEarn' => $badgesToEarn,
        ];
    }

    /**
     * Ajoute un badge à la liste appropriée.
     */
    public function addBadge(
        bool $isEarned,
        array &$badgesEarned,
        array &$badgesToEarn,
        array $earnedData,
        array $toEarnData
    ): void {
        if ($isEarned)
        {
            $badgesEarned[] = $earnedData;
        } else
        {
            $badgesToEarn[] = array_merge($earnedData, $toEarnData);
        }
    }

    /**
     * Fusionne les badges de plusieurs sources.
     */
    public function mergeBadges(array ...$badgeSets): array
    {
        $earned = [];
        $toEarn = [];

        foreach ($badgeSets as $set)
        {
            $earned = array_merge($earned, $set['earned'] ?? []);
            $toEarn = array_merge($toEarn, $set['toEarn'] ?? []);
        }

        return [
            'earned' => $earned,
            'toEarn' => $toEarn,
        ];
    }

    /**
     * Calcule les badges spécifiques à WalkTrack.
     *
     * @param int $streak Streak actuel (jours consécutifs)
     * @param int $totalJours Total des jours avec marche
     * @param int $totalMarches Nombre total de marches
     * @param float $totalKm Kilomètres totaux parcourus
     * @param int $parcoursSaved Nombre de parcours sauvegardés
     * @return array Badges earned et toEarn
     */
    public function computeWalkTrackBadges(
        int $streak,
        int $totalJours,
        int $totalMarches = 0,
        float $totalKm = 0,
        int $parcoursSaved = 0
    ): array {
        $badgesEarned = [];
        $badgesToEarn = [];

        // 🎉 Badge: Première marche
        $this->addBadge(
            $totalMarches >= 1,
            $badgesEarned,
            $badgesToEarn,
            [
                'id' => 'first_walk',
                'icon' => '🎉',
                'label' => 'Premier pas',
                'condition' => 'Enregistrer ta première marche',
                'tip' => 'Chaque voyage commence par un premier pas !',
            ],
            [
                'progress' => $totalMarches,
                'target' => 1,
                'hint' => $totalMarches . '/1 marche',
                'tip' => 'Ajoute ta première marche pour débloquer !',
            ]
        );

        // 🚶 Badge: 5 marches
        $this->addBadge(
            $totalMarches >= 5,
            $badgesEarned,
            $badgesToEarn,
            [
                'id' => '5_walks',
                'icon' => '🚶',
                'label' => 'Marcheur régulier',
                'condition' => 'Enregistrer 5 marches',
                'tip' => 'Tu prends de bonnes habitudes !',
            ],
            [
                'progress' => $totalMarches,
                'target' => 5,
                'hint' => $totalMarches . '/5 marches',
                'tip' => 'Encore ' . max(0, 5 - $totalMarches) . ' marche(s) !',
            ]
        );

        // 📍 Badge: Premier parcours sauvegardé
        $this->addBadge(
            $parcoursSaved >= 1,
            $badgesEarned,
            $badgesToEarn,
            [
                'id' => 'first_route',
                'icon' => '📍',
                'label' => 'Explorateur',
                'condition' => 'Sauvegarder ton premier parcours favori',
                'tip' => 'Tu connais ton quartier par cœur !',
            ],
            [
                'progress' => $parcoursSaved,
                'target' => 1,
                'hint' => $parcoursSaved . '/1 parcours',
                'tip' => 'Trace un parcours et sauvegarde-le !',
            ]
        );

        // 🔥 Badge: 3 jours de suite
        $this->addBadge(
            $streak >= 3,
            $badgesEarned,
            $badgesToEarn,
            [
                'id' => 'streak_3',
                'icon' => '🔥',
                'label' => '3 jours de suite',
                'condition' => 'Marcher 3 jours consécutifs',
                'tip' => 'Tu as pris le rythme, continue !',
            ],
            [
                'progress' => $streak,
                'target' => 3,
                'hint' => $streak . '/3 jours',
                'tip' => 'Encore ' . max(0, 3 - $streak) . ' jour(s) !',
            ]
        );

        // 🏆 Badge: Semaine parfaite
        $this->addBadge(
            $streak >= 7,
            $badgesEarned,
            $badgesToEarn,
            [
                'id' => 'streak_7',
                'icon' => '🏆',
                'label' => 'Semaine parfaite',
                'condition' => 'Marcher 7 jours de suite',
                'tip' => 'La régularité paie toujours !',
            ],
            [
                'progress' => $streak,
                'target' => 7,
                'hint' => $streak . '/7 jours',
                'tip' => 'Plus que ' . max(0, 7 - $streak) . ' jour(s) !',
            ]
        );

        // 🎯 Badge: 10 km total
        $this->addBadge(
            $totalKm >= 10,
            $badgesEarned,
            $badgesToEarn,
            [
                'id' => '10km',
                'icon' => '🎯',
                'label' => '10 km parcourus',
                'condition' => 'Parcourir 10 km au total',
                'tip' => 'Les premiers kilomètres sont les plus importants !',
            ],
            [
                'progress' => round($totalKm, 1),
                'target' => 10,
                'hint' => round($totalKm, 1) . '/10 km',
                'tip' => 'Encore ' . round(max(0, 10 - $totalKm), 1) . ' km !',
            ]
        );

        // 🥇 Badge: 50 km total
        $this->addBadge(
            $totalKm >= 50,
            $badgesEarned,
            $badgesToEarn,
            [
                'id' => '50km',
                'icon' => '🥇',
                'label' => '50 km parcourus',
                'condition' => 'Parcourir 50 km au total',
                'tip' => 'Tu es un vrai marcheur !',
            ],
            [
                'progress' => round($totalKm, 1),
                'target' => 50,
                'hint' => round($totalKm, 1) . '/50 km',
                'tip' => 'Encore ' . round(max(0, 50 - $totalKm), 1) . ' km !',
            ]
        );

        // 💎 Badge: 100 km total
        $this->addBadge(
            $totalKm >= 100,
            $badgesEarned,
            $badgesToEarn,
            [
                'id' => '100km',
                'icon' => '💎',
                'label' => '100 km parcourus',
                'condition' => 'Parcourir 100 km au total',
                'tip' => 'Légende de la marche !',
            ],
            [
                'progress' => round($totalKm, 1),
                'target' => 100,
                'hint' => round($totalKm, 1) . '/100 km',
                'tip' => 'Encore ' . round(max(0, 100 - $totalKm), 1) . ' km !',
            ]
        );

        // ⭐ Badge: 30 jours total
        $this->addBadge(
            $totalJours >= 30,
            $badgesEarned,
            $badgesToEarn,
            [
                'id' => '30days',
                'icon' => '⭐',
                'label' => '30 jours de marche',
                'condition' => 'Marcher 30 jours au total',
                'tip' => 'Un mois de marche, bravo !',
            ],
            [
                'progress' => $totalJours,
                'target' => 30,
                'hint' => $totalJours . '/30 jours',
                'tip' => 'Encore ' . max(0, 30 - $totalJours) . ' jour(s) !',
            ]
        );

        return [
            'earned' => $badgesEarned,
            'toEarn' => $badgesToEarn,
        ];
    }

    /**
     * Calcule le niveau XP basé sur les points.
     */
    public function computeLevel(int $totalXp): array
    {
        $levels = [
            ['name' => 'Débutant', 'minXp' => 0, 'maxXp' => 100],
            ['name' => 'Apprenti', 'minXp' => 100, 'maxXp' => 300],
            ['name' => 'Confirmé', 'minXp' => 300, 'maxXp' => 600],
            ['name' => 'Expert', 'minXp' => 600, 'maxXp' => 1000],
            ['name' => 'Maître', 'minXp' => 1000, 'maxXp' => 1500],
            ['name' => 'Légende', 'minXp' => 1500, 'maxXp' => PHP_INT_MAX],
        ];

        $currentLevel = $levels[0];
        $levelNumber = 1;

        foreach ($levels as $index => $level)
        {
            if ($totalXp >= $level['minXp'])
            {
                $currentLevel = $level;
                $levelNumber = $index + 1;
            }
        }

        $xpInLevel = $totalXp - $currentLevel['minXp'];
        $xpForNextLevel = $currentLevel['maxXp'] - $currentLevel['minXp'];
        $progress = $xpForNextLevel > 0 ? min(100, ($xpInLevel / $xpForNextLevel) * 100) : 100;

        return [
            'level' => $levelNumber,
            'name' => $currentLevel['name'],
            'currentXp' => $xpInLevel,
            'nextLevelXp' => $xpForNextLevel,
            'totalXp' => $totalXp,
            'progress' => (int)round($progress),
        ];
    }
}
