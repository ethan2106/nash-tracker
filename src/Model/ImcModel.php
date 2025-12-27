<?php

namespace App\Model;

/**
 * ImcModel
 * Calcul IMC, besoins caloriques et cibles nutritionnelles.
 */
class ImcModel
{
    /**
     * Construit les cibles front (targets) selon les recommandations NAFLD.
     * Applique un déficit de 20% sur le TDEE réel pour la perte de poids,
     * sans jamais descendre sous le BMR. Les autres seuils suivent les guidelines.
     *
     * @param float $bmr
     * @param int   $tdee_reel   TDEE calculé avec le facteur d'activité choisi (kcal)
     * @param int   $tdee_sedentaire  TDEE si sédentaire (kcal)
     * @param float $poids
     * @param float $deficit_pct
     * @return array<int, array<string, string|float>>
     */
    private static function buildTargets(float $bmr, int $tdee_reel, int $tdee_sedentaire, float $poids, float $deficit_pct = 0.2): array
    {
        // Déficit basé sur le TDEE réel (cohérent avec l'activité déclarée)
        $calories_perte_candidate = (int)round($tdee_reel * (1 - $deficit_pct));
        // Protection : ne jamais descendre sous le BMR
        $calories_perte = max((int)round($bmr), $calories_perte_candidate);

        // Valeurs de maintien basées sur le TDEE réel
        $calories_maintien = (int)$tdee_reel;

        // Valeurs nutritionnelles
        $sucres_max = 50.0; // g/jour
        $graisses_sat_max = round($calories_maintien * 0.10 / 9, 1); // g/jour
        $proteines_min = round($poids * 0.8, 1);
        $proteines_max = round($poids * 1.0, 1);
        $fibres_min = 25.0;
        $fibres_max = 30.0;

        // Tooltips explicites : indiquent si la perte est basée sur TDEE réel et que le plancher est BMR,
        // et on fournit aussi le TDEE sédentaire si tu veux expliquer la version conservative.
        $tooltipCalories = sprintf(
            'Déficit de %d%% sur le TDEE réel (%d kcal). Plancher = BMR %d kcal. (TDEE sédentaire: %d kcal pour référence conservative)',
            (int)round($deficit_pct * 100),
            $tdee_reel,
            (int)round($bmr),
            $tdee_sedentaire
        );

        return [
            ['label'=>'CALORIES', 'icon'=>'fa-bolt', 'value'=>$calories_perte, 'unit'=>'kcal/jour', 'desc'=>'Max/jour', 'color'=>'yellow', 'tooltip'=>$tooltipCalories],
            ['label'=>'SUCRES', 'icon'=>'fa-candy-cane', 'value'=>$sucres_max, 'unit'=>'g/jour', 'desc'=>'<50g/jour recommandé', 'color'=>'pink', 'tooltip'=>'Moins de 50g/jour pour réduire l\'inflammation hépatique (EASL 2016)'],
            ['label'=>'GRAISSES SATURÉES', 'icon'=>'fa-bacon', 'value'=>$graisses_sat_max, 'unit'=>'g/jour', 'desc'=>'<10% des calories', 'color'=>'yellow', 'tooltip'=>'Moins de 10% des calories saturées (AHA + NAFLD consensus)'],
            ['label'=>'PROTÉINES', 'icon'=>'fa-dumbbell', 'value'=>$proteines_min . ' - ' . $proteines_max, 'unit'=>'g/jour', 'desc'=>'0.8-1g/kg recommandé', 'color'=>'purple', 'tooltip'=>'0.8-1g/kg/jour pour la masse musculaire (EASL guidelines)'],
            ['label'=>'FIBRES', 'icon'=>'fa-seedling', 'value'=>$fibres_min . ' - ' . $fibres_max, 'unit'=>'g/jour', 'desc'=>'25-30g/jour recommandé', 'color'=>'green', 'tooltip'=>'25-30g/jour pour la santé digestive et NAFLD (WHO + IOM)'],
            ['label'=>'ACTIVITÉ PHYSIQUE', 'icon'=>'fa-person-running', 'value'=>600, 'unit'=>'kcal/jour', 'desc'=>'600 kcal/jour recommandé', 'color'=>'blue', 'tooltip'=>'600 kcal/jour ≈ 1h marche rapide (NAFLD exercise guidelines)'],
        ];
    }

    /**
     * Valide les inputs utilisateur.
     */
    public static function validateInputs(array $request): array
    {
        $errors = [];

        // Taille
        $taille = $request['taille'] ?? null;
        if (!is_numeric($taille) || $taille < 100 || $taille > 250)
        {
            $errors[] = 'Taille invalide (doit être entre 100 et 250 cm).';
        }

        // Poids
        $poids = $request['poids'] ?? null;
        if (!is_numeric($poids) || $poids < 30 || $poids > 300)
        {
            $errors[] = 'Poids invalide (doit être entre 30 et 300 kg).';
        }

        // Année de naissance
        $annee = $request['annee'] ?? null;
        $currentYear = (int)date('Y');
        if (!is_numeric($annee) || $annee < 1920 || $annee > $currentYear)
        {
            $errors[] = 'Année de naissance invalide.';
        }

        // Sexe
        $sexe = $request['sexe'] ?? null;
        if (!in_array($sexe, ['homme', 'femme']))
        {
            $errors[] = 'Sexe invalide.';
        }

        // Activité
        $activite = $request['activite'] ?? null;
        $validActivites = ['sedentaire', 'leger', 'modere', 'intense'];
        if (!in_array($activite, $validActivites))
        {
            $errors[] = "Niveau d'activité invalide.";
        }

        // Objectif (optionnel)
        $objectif = $request['objectif'] ?? null;
        if ($objectif && !in_array($objectif, ['perte', 'maintien']))
        {
            $errors[] = 'Objectif invalide.';
        }

        return $errors;
    }

    public static function calculate(array $request): array
    {
        // sanitize numeric inputs (allow comma decimals)
        $sanitizeFloat = function ($v, $default = 0.0)
        {
            if (!isset($v) || $v === '')
            {
                return (float)$default;
            }
            $v = str_replace(',', '.', $v);

            return (float)$v;
        };

        $taille = $sanitizeFloat($request['taille'] ?? null);
        $taille = max(0.1, $taille);
        $poids = $sanitizeFloat($request['poids'] ?? null);
        $annee = isset($request['annee']) ? (int)$request['annee'] : null;
        $annee = max(1920, min((int)date('Y'), $annee));

        $sexe = $request['sexe'] ?? 'homme';
        $sexe = in_array($sexe, ['homme', 'femme']) ? $sexe : 'homme';
        $activite = strtolower($request['activite'] ?? 'sedentaire');

        $facteurs = [
            'sedentaire' => 1.2,
            'leger'      => 1.375,
            'modere'     => 1.55,
            'intense'    => 1.725,
        ];
        $activite = in_array($activite, array_keys($facteurs)) ? $activite : 'sedentaire';

        // calcul IMC (protection division par zéro incluse)
        $imc = $poids / pow($taille / 100, 2);
        $imc = round($imc, 1);

        if ($imc < 18.5)
        {
            $imc_cat = 'Maigreur';
            $imc_color = 'blue';
        } elseif ($imc < 25)
        {
            $imc_cat = 'Normal';
            $imc_color = 'green';
        } elseif ($imc < 30)
        {
            $imc_cat = 'Surpoids';
            $imc_color = 'orange';
        } else
        {
            $imc_cat = 'Obésité';
            $imc_color = 'red';
        }

        // BMR calculation using Mifflin-St Jeor Equation (2005)
        $age = (int)date('Y') - $annee;
        $bmr = ($sexe === 'homme')
            ? 10 * $poids + 6.25 * $taille - 5 * $age + 5
            : 10 * $poids + 6.25 * $taille - 5 * $age - 161;
        $bmr = round($bmr);

        // TDEE = BMR × Activity Factor
        $tdee_reel = (int)round($bmr * $facteurs[$activite]);

        // TDEE sédentaire (référence conservative)
        $tdee_sedentaire = (int)round($bmr * $facteurs['sedentaire']);

        // Calorie targets for NAFLD management
        $deficit_pct = 0.2; // 20% deficit recommended

        // Maintenant, on construit les targets via la méthode qui applique le plancher BMR
        $targets = self::buildTargets($bmr, $tdee_reel, $tdee_sedentaire, $poids, $deficit_pct);

        // autres inputs
        $sucres = $sanitizeFloat($request['sucres'] ?? null, 0);
        $graisses_sat = $sanitizeFloat($request['graisses_sat'] ?? null, 0);
        $fibres = $sanitizeFloat($request['fibres'] ?? null, 0);
        $glucides = $sanitizeFloat($request['glucides'] ?? null, 0);
        $graisses_insaturees = $sanitizeFloat($request['graisses_insaturees'] ?? null, 0);

        // Macronutrient targets based on NAFLD and general health guidelines
        $sucres_max = 50; // g/jour
        $graisses_sat_max = round($targets[2]['value'] ?? ($tdee_reel * 0.10 / 9), 1); // fallback
        $proteines_min = round($poids * 0.8, 1);
        $proteines_max = round($poids * 1, 1);
        $fibres_min = 25;
        $fibres_max = 30;
        $sodium_max = 5; // g/jour

        // alertes personnalisées NAFLD
        $tempData = [
            'imc' => $imc,
            'annee' => $annee,
            'sexe' => $sexe,
            'activite' => $activite,
            'tdee' => $tdee_reel,
            'sucres' => $sucres,
            'sucres_max' => $sucres_max,
            'graisses_sat' => $graisses_sat,
            'graisses_sat_max' => $graisses_sat_max,
            'fibres' => $fibres,
            'fibres_min' => $fibres_min,
        ];
        $alertes = \App\Model\NaflAlerts::generate($tempData);

        // valeurs retournées (inclut targets déjà calculés)
        return [
            'taille' => $taille,
            'poids' => $poids,
            'annee' => $annee,
            'sexe' => $sexe,
            'activite' => $activite,
            'imc' => $imc,
            'imc_cat' => $imc_cat,
            'imc_color' => $imc_color,
            'imc_left' => ($imc < 18.5) ? '10%' : (($imc < 25) ? '30%' : (($imc < 30) ? '60%' : '85%')),
            'imc_marker_color' => ($imc < 18.5) ? '#3b82f6' : (($imc < 25) ? 'green' : (($imc < 30) ? 'orange' : 'red')),
            'bmr' => $bmr,
            'tdee' => $tdee_reel,
            'calories_perte' => $targets[0]['value'],
            'calories_maintien' => $targets[1]['value'],
            'calories_masse' => $targets[2]['value'],
            'sucres_max' => $sucres_max,
            'graisses_sat_max' => $graisses_sat_max,
            'proteines_min' => $proteines_min,
            'proteines_max' => $proteines_max,
            'fibres_min' => $fibres_min,
            'fibres_max' => $fibres_max,
            'sodium_max' => $sodium_max,
            'glucides' => $glucides,
            'graisses_insaturees' => $graisses_insaturees,
            'objectif' => isset($request['objectif']) ? $request['objectif'] : 'perte',
            'alertes' => $alertes,
            'targets' => $targets,
        ];
    }
}
