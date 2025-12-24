<?php

namespace App\Model;

use Exception;
use PDO;

class ReportsModel
{
    private $pdo;

    public function __construct()
    {
        try
        {
            $this->pdo = Database::getInstance();
        } catch (\PDOException $e)
        {
            throw new Exception('Erreur de connexion à la base de données');
        }
    }

    /**
     * Générer les données pour le rapport PDF.
     */
    public function getReportData($userId)
    {
        try
        {
            $data = [];

            // Informations utilisateur
            $data['user'] = $this->getUserInfo($userId);

            // IMC actuel (si disponible en base)
            $saved = $this->getSavedObjectifs($userId);
            $data['imc'] = $saved ? \App\Model\ImcModel::calculate($saved) : null;

            // Repas d'aujourd'hui
            $data['today_meals'] = $this->getTodayMeals($userId);

            // Total calories aujourd'hui
            $data['today_calories'] = $this->calculateTodayCalories($userId);

            return $data;
        } catch (Exception $e)
        {
            error_log('Erreur génération rapport: ' . $e->getMessage());

            return null;
        }
    }

    private function getUserInfo($userId)
    {
        $stmt = $this->pdo->prepare('SELECT pseudo as username, email FROM users WHERE id = ?');
        $stmt->execute([$userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getTodayMeals($userId)
    {
        require_once __DIR__ . '/MealModel.php';
        $mealModel = new MealModel();
        $meals = $mealModel->getMealsByDate($userId, date('Y-m-d'));
        $mealsWithDetails = [];
        foreach ($meals as $meal)
        {
            $details = $mealModel->getMealDetails($meal['id']);
            $meal['aliments'] = [];
            foreach ($details as $detail)
            {
                $meal['aliments'][] = [
                    'nom' => $detail['nom'],
                    'quantite' => $detail['quantite_g'],
                    'calories' => $detail['calories_100g'],
                ];
            }
            $mealsWithDetails[] = $meal;
        }

        return $mealsWithDetails;
    }

    private function getSavedObjectifs($userId)
    {
        require_once __DIR__ . '/ObjectifsModel.php';
        require_once __DIR__ . '/ImcModel.php';

        return \App\Model\ObjectifsModel::getByUser($userId);
    }

    private function calculateTodayCalories($userId)
    {
        $meals = $this->getTodayMeals($userId);
        $total = 0;
        foreach ($meals as $meal)
        {
            if (isset($meal['aliments']))
            {
                foreach ($meal['aliments'] as $aliment)
                {
                    $total += ($aliment['calories'] ?? 0) * ($aliment['quantite'] ?? 0) / 100;
                }
            }
        }

        return round($total);
    }
}
