<?php

namespace App\Controller;

use App\Service\ImcApiService;
use App\Service\ImcDataService;
use App\Service\ImcSaveService;

/**
 * ImcController - Gère les calculs et l'affichage de l'IMC et métriques associées.
 * Responsabilités :
 * - Calcul de l'IMC, BMR, TDEE, etc.
 * - Gestion des objectifs nutritionnels
 * - Fourniture des données pour les graphiques API
 * - Interactions avec ImcModel et ObjectifsModel.
 */
class ImcController
{
    private ImcDataService $dataService;

    private ImcApiService $apiService;

    private ImcSaveService $saveService;

    public function __construct(ImcDataService $dataService, ImcApiService $apiService, ImcSaveService $saveService)
    {
        $this->dataService = $dataService;
        $this->apiService = $apiService;
        $this->saveService = $saveService;
    }

    /**
     * Affiche la page IMC.
     */
    public function showImc(): void
    {
        $userId = $_SESSION['user']['id'] ?? null;
        $request = $_GET; // Pour la pagination ou autres params

        // Hydrate request with saved data if missing
        $saved = \App\Model\ObjectifsModel::getByUser($userId);
        if ($saved)
        {
            $request['poids'] ??= $saved['poids'];
            $request['taille'] ??= $saved['taille'];
            $request['annee'] ??= $saved['annee'];
            $request['sexe'] ??= $saved['sexe'];
            $request['activite'] ??= $saved['activite'];
        }

        $data = $this->dataService->getImcData($userId, $request);

        // Inclure la vue avec les données
        extract($data);
        include __DIR__ . '/../View/imc.php';
    }

    public function index($request)
    {
        $user_id = $_SESSION['user']['id'] ?? null;

        // Sanitize request data to handle arrays
        $fields = ['taille', 'poids', 'annee', 'sexe', 'activite', 'objectif', 'sucres', 'graisses_sat', 'fibres', 'glucides', 'graisses_insaturees'];
        foreach ($fields as $field)
        {
            if (isset($request[$field]))
            {
                $value = $request[$field];
                if (is_array($value))
                {
                    $value = $value[0] ?? '';
                }
                $request[$field] = $value;
            }
        }

        // Hydrate request with saved data if missing
        $saved = \App\Model\ObjectifsModel::getByUser($user_id);
        if ($saved)
        {
            $request['poids'] ??= $saved['poids'];
            $request['taille'] ??= $saved['taille'];
            $request['annee'] ??= $saved['annee'];
            $request['sexe'] ??= $saved['sexe'];
            $request['activite'] ??= $saved['activite'];
        }

        return $this->dataService->getImcData($user_id, $request);
    }

    public function getChartData()
    {
        $data = $this->index($_POST);

        return $this->apiService->getChartData($data);
    }

    /**
     * Bridge statique legacy pour compatibilité API.
     * TODO: Migrer vers DI complète.
     */
    public static function handleApiImcData()
    {
        $container = \App\Config\DIContainer::getContainer();
        $imcController = $container->get(\App\Controller\ImcController::class);
        $imcController->handleApiImcDataInstance();
    }

    public function handleApiImcDataInstance()
    {
        header('Content-Type: application/json');
        echo json_encode($this->getChartData());
        exit;
    }

    /**
     * Sauvegarder les objectifs et mesures IMC.
     */
    public function save()
    {
        // Vérifie que l'utilisateur est connecté
        $user_id = $_SESSION['user']['id'] ?? null;
        if (!$user_id)
        {
            $_SESSION['flash'] = 'Utilisateur non connecté.';
            header('Location: ?page=imc');
            exit;
        }

        // Sanitize POST data to handle arrays (e.g., from Alpine.js duplicates)
        $cleanPost = [];
        $fields = ['taille', 'poids', 'annee', 'sexe', 'activite', 'objectif', 'sucres', 'graisses_sat', 'fibres', 'glucides', 'graisses_insaturees'];
        foreach ($fields as $field)
        {
            if (isset($_POST[$field]))
            {
                $value = $_POST[$field];
                if (is_array($value))
                {
                    $value = $value[0] ?? '';
                }
                $cleanPost[$field] = $value;
            }
        }

        try
        {
            $success = $this->saveService->saveImcData($user_id, $cleanPost);
        } catch (\InvalidArgumentException $e)
        {
            file_put_contents(__DIR__ . '/../../storage/debug_imc.log', date('Y-m-d H:i:s') . " VALIDATION ERRORS:\n" . $e->getMessage() . "\n\n", FILE_APPEND);
            $_SESSION['flash'] = $e->getMessage();
            header('Location: ?page=imc');
            exit;
        }

        $_SESSION['flash'] = $success
            ? 'Objectifs et mesures enregistrés !'
            : 'Erreur lors de l\'enregistrement.';

        header('Location: ?page=imc');
        exit;
    }
}
