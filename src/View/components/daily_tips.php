<?php
// Conseils du jour — suggestions actionnables sans redondance chiffrée
$dashboard = $viewData['dashboard'] ?? [];
$objectifs = $dashboard['objectifs'] ?? [];
$stats = $dashboard['stats'] ?? [];
$userConfig = $viewData['userConfig'] ?? [];

// Récup cibles
$caloriesTarget = $objectifs['calories_perte'] ?? 1800;
$proteinesMin = $objectifs['proteines_min'] ?? null;
$fibresMin = $objectifs['fibres_min'] ?? null;

// Cibles dérivées (évite doublons avec cartes du haut)
$glucidesTarget = $caloriesTarget > 0 ? round(($caloriesTarget * 0.50) / 4) : null; // 50% kcal
$lipidesTarget = $caloriesTarget > 0 ? round(($caloriesTarget * 0.30) / 9) : null;   // 30% kcal

// Consommations du jour
$proteines = (float)($stats['proteines_consumed'] ?? 0);
$fibres = (float)($stats['fibres_consumed'] ?? 0);
$glucides = (float)($stats['glucides_consumed'] ?? 0);
$lipides = (float)($stats['lipides_consumed'] ?? 0);

$activityToday = (int)($stats['activity_minutes_today'] ?? 0);
$activityGoal = (int)($userConfig['activite_objectif_minutes'] ?? 0);

$tips = [];

// Tip Protéines (atteindre le minimum)
if ($proteinesMin !== null && $proteines < $proteinesMin)
{
    $rest = max(0, round($proteinesMin - $proteines));
    if ($rest > 0)
    {
        $tips[] = [
            'icon' => 'fa-dumbbell',
            'color' => 'purple',
            'title' => 'Augmentez vos protéines',
            'text' => "Encore ~{$rest} g pour atteindre le minimum",
        ];
    }
}

// Tip Fibres (atteindre le minimum)
if ($fibresMin !== null && $fibres < $fibresMin)
{
    $rest = max(0, round($fibresMin - $fibres));
    if ($rest > 0)
    {
        $tips[] = [
            'icon' => 'fa-seedling',
            'color' => 'green',
            'title' => 'Ajoutez des fibres',
            'text' => "Encore ~{$rest} g pour votre objectif",
        ];
    }
}

// Tip Glucides (suggérer une limite douce)
if ($glucidesTarget !== null)
{
    $rest = round($glucidesTarget - $glucides);
    if ($rest > 0)
    {
        $tips[] = [
            'icon' => 'fa-bread-slice',
            'color' => 'orange',
            'title' => 'Glucides à répartir',
            'text' => "Il vous reste ~{$rest} g à consommer",
        ];
    } else
    {
        $exces = abs($rest);
        if ($exces > 0)
        {
            $tips[] = [
                'icon' => 'fa-scale-unbalanced-flip',
                'color' => 'orange',
                'title' => 'Glucides élevés',
                'text' => "Vous avez dépassé d’environ {$exces} g aujourd’hui",
            ];
        }
    }
}

// Tip Lipides (sensibilisation)
if ($lipidesTarget !== null)
{
    $rest = round($lipidesTarget - $lipides);
    if ($rest < 0)
    {
        $tips[] = [
            'icon' => 'fa-cheese',
            'color' => 'yellow',
            'title' => 'Lipides à modérer',
            'text' => 'Privilégiez les graisses de qualité (huile d’olive, noix) et limitez les fritures.',
        ];
    }
}


// Tip Activité (en fonction de l'objectif configuré)
if ($activityGoal > 0)
{
    $remain = max(0, $activityGoal - $activityToday);
    if ($remain > 0)
    {
        $tips[] = [
            'icon' => 'fa-person-running',
            'color' => 'purple',
            'title' => 'Bouge un peu',
            'text' => "Il te reste ~{$remain} min d'activité aujourd'hui",
        ];
    } else
    {
        $tips[] = [
            'icon' => 'fa-circle-check',
            'color' => 'purple',
            'title' => 'Objectif activité atteint',
            'text' => 'Bravo ! Un peu d’étirements doux pour récupérer ?',
        ];
    }
}

// Tip contextuel selon l'heure
$hour = (int)date('G');
if ($hour < 11)
{
    $tips[] = [
        'icon' => 'fa-mug-hot',
        'color' => 'blue',
        'title' => 'Matin: bon départ',
        'text' => 'Petit-déj riche en protéines (yaourt grec, oeufs) pour tenir jusqu’à midi.',
    ];
} elseif ($hour < 17)
{
    $tips[] = [
        'icon' => 'fa-carrot',
        'color' => 'green',
        'title' => 'Après-midi: collation fibre',
        'text' => 'Une pomme + quelques noix: fibres et satiété sans pic glycémique.',
    ];
} else
{
    $tips[] = [
        'icon' => 'fa-person-walking',
        'color' => 'purple',
        'title' => 'Soir: bouger un peu',
        'text' => 'Marche 10 minutes après le dîner pour aider la glycémie.',
    ];
}

// Si aucun tip pertinent, proposer une suggestion générique
if (empty($tips))
{
    $tips[] = [
        'icon' => 'fa-circle-check',
        'color' => 'blue',
        'title' => 'Bien joué !',
        'text' => 'Vos objectifs sont bien engagés aujourd’hui. Continuez sur cette lancée ✨',
    ];
}
?>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6" x-show="isLoaded" x-transition>
    <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center">
        <i class="fa-solid fa-lightbulb text-amber-500 mr-2"></i> Conseils du jour
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($tips as $i => $tip)
        { ?>
        <div class="p-4 rounded-xl border bg-<?= $tip['color']; ?>-50 border-<?= $tip['color']; ?>-200 flex items-start gap-3"
             x-show="isLoaded" x-transition:enter.delay.<?= $i * 100; ?>ms>
            <div class="w-10 h-10 rounded-lg bg-<?= $tip['color']; ?>-100 border border-<?= $tip['color']; ?>-200 flex items-center justify-center shrink-0">
                <i class="fa-solid <?= $tip['icon']; ?> text-<?= $tip['color']; ?>-600"></i>
            </div>
            <div>
                <p class="font-semibold text-slate-800">
                    <?= htmlspecialchars($tip['title']); ?>
                </p>
                <p class="text-sm text-slate-600">
                    <?= htmlspecialchars($tip['text']); ?>
                </p>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
