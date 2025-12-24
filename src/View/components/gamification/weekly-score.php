<?php
/**
 * Score hebdomadaire avec grade (A-F) - Réutilisable.
 *
 * Props PHP requises:
 * - $weeklyGrade: array avec 'grade', 'color', 'bg', 'label'
 * - $weeklyScoreValue: int (0-100)
 * - $weeklyDaysWithGoal: int (0-7)
 * - $totalDays: int (default: 7)
 *
 * Grades supportés: A+, A, B, C, D, F
 */
$weeklyGrade = $weeklyGrade ?? ['grade' => '?', 'color' => 'text-gray-500', 'bg' => 'bg-gray-100', 'label' => 'N/A'];
$weeklyScoreValue = $weeklyScoreValue ?? 0;
$weeklyDaysWithGoal = $weeklyDaysWithGoal ?? 0;
$totalDays = $totalDays ?? 7;
$title = $title ?? 'Score semaine';
?>

<div class="text-center rounded-2xl p-4 border-2 shadow-lg <?= htmlspecialchars($weeklyGrade['bg']); ?>"
     style="border-color: currentColor;">
    <div class="text-xs text-gray-500 mb-1"><?= htmlspecialchars($title); ?></div>
    <div class="text-4xl font-black <?= htmlspecialchars($weeklyGrade['color']); ?>"><?= htmlspecialchars($weeklyGrade['grade']); ?></div>
    <div class="text-lg font-bold <?= htmlspecialchars($weeklyGrade['color']); ?>"><?= (int)$weeklyScoreValue; ?>/100</div>
    <div class="text-xs <?= htmlspecialchars($weeklyGrade['color']); ?> font-medium"><?= htmlspecialchars($weeklyGrade['label']); ?></div>
    <div class="text-xs text-gray-500 mt-1"><?= (int)$weeklyDaysWithGoal; ?>/<?= (int)$totalDays; ?> jours OK</div>
</div>
