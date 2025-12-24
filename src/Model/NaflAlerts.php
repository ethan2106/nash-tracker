<?php

namespace App\Model;

class NaflAlerts
{
    public static function generate(array $data): array
    {
        $alertes = [];

        // Alertes basées sur l'IMC
        if ($data['imc'] >= 30)
        {
            $alertes[] = 'Votre IMC est élevé. Un suivi médical peut être utile pour votre santé hépatique.';
        } elseif ($data['imc'] >= 25)
        {
            $alertes[] = 'Votre IMC est légèrement au-dessus de la norme. Adopter une alimentation équilibrée est recommandé.';
        } elseif ($data['imc'] < 18.5)
        {
            $alertes[] = 'Votre IMC est en dessous de la norme. Assurez-vous de couvrir vos besoins nutritionnels.';
        }

        // Alertes basées sur l'âge
        $age = (int)date('Y') - $data['annee'];
        if ($age >= 40 && $data['imc'] >= 25)
        {
            $alertes[] = 'À votre âge, un suivi régulier de votre santé hépatique peut être bénéfique.';
        }

        // Alertes basées sur les objectifs nutritionnels
        if (isset($data['sucres']) && $data['sucres'] > $data['sucres_max'])
        {
            $alertes[] = 'Votre consommation de sucres est un peu élevée. Réduire les sucres raffinés peut soutenir votre foie.';
        }

        if (isset($data['graisses_sat']) && $data['graisses_sat'] > $data['graisses_sat_max'])
        {
            $alertes[] = 'Vos graisses saturées sont un peu au-dessus de la recommandation. Privilégier les graisses insaturées est conseillé.';
        }

        if (isset($data['fibres']) && $data['fibres'] < $data['fibres_min'])
        {
            $alertes[] = 'Votre apport en fibres est faible. Ajouter légumes, fruits et céréales complètes peut aider.';
        }

        // Alertes spécifiques NAFLD
        if ($data['imc'] >= 25 && $age >= 30)
        {
            $alertes[] = 'Certaines données suggèrent qu’un suivi régulier de vos analyses hépatiques pourrait être utile.';
        }

        if ($data['tdee'] < 2000 && $data['activite'] === 'sedentaire')
        {
            $alertes[] = 'Bouger un peu plus chaque jour peut aider à préserver votre foie et votre énergie.';
        }

        // Recommandations positives
        if ($data['imc'] < 25 && $data['activite'] !== 'sedentaire')
        {
            $alertes[] = 'Super profil ! Continuez votre mode de vie sain pour garder votre foie en forme.';
        }

        return $alertes;
    }
}
