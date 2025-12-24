<?php

namespace App\Service;

/**
 * ImcApiService - Gère la logique API pour les données IMC.
 * Responsabilités :
 * - Formatage des données pour graphiques
 * - Pagination si nécessaire.
 */
class ImcApiService
{
    /**
     * Formate les données IMC pour le graphique Chart.js.
     */
    public function getChartData(array $imcData): array
    {
        return [
            'labels' => ['IMC', 'BMR', 'TDEE', 'Perte', 'Maintien', 'Masse'],
            'data' => [
                (float)number_format($imcData['imc'], 1, '.', ''),
                (float)number_format($imcData['bmr'], 0, '.', ''),
                (float)number_format($imcData['tdee'], 0, '.', ''),
                (float)number_format($imcData['calories_perte'], 0, '.', ''),  // Perte de poids
                (float)number_format($imcData['calories_maintien'], 0, '.', ''),
                (float)number_format($imcData['calories_masse'], 0, '.', ''),
            ],
            'backgroundColor' => [
                '#3b82f6', '#facc15', '#22c55e', '#f97316', '#22c55e', '#3b82f6',
            ],
        ];
    }
}
