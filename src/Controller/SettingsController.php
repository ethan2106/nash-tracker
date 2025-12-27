<?php

namespace App\Controller;

use App\Model\HistoriqueMesuresModel;
use App\Model\UserConfigModel;
use App\Model\UserModel;
use App\Service\CacheService;
use App\Service\SettingsDataService;
use App\Service\ValidationService;

/**
 * SettingsController - Gestion des paramètres utilisateur
 * Modification email, pseudo, mot de passe, suppression compte.
 */
class SettingsController extends BaseApiController
{
    private UserModel $userModel;

    private UserConfigModel $userConfigModel;

    private HistoriqueMesuresModel $historiqueMesuresModel;

    private CacheService $cache;

    private ValidationService $validationService;

    private SettingsDataService $settingsDataService;

    public function __construct(
        UserModel $userModel,
        UserConfigModel $userConfigModel,
        HistoriqueMesuresModel $historiqueMesuresModel,
        CacheService $cache,
        ValidationService $validationService,
        SettingsDataService $settingsDataService
    ) {
        $this->ensureSession();
        $this->userModel = $userModel;
        $this->userConfigModel = $userConfigModel;
        $this->historiqueMesuresModel = $historiqueMesuresModel;
        $this->cache = $cache;
        $this->validationService = $validationService;
        $this->settingsDataService = $settingsDataService;
    }

    /**
     * Affiche la page des paramètres.
     */
    public function showSettings(): void
    {
        $userId = $this->requireAuth();

        // Pagination pour historique mesures
        $page = isset($_GET['page_mesures']) ? (int)$_GET['page_mesures'] : 1;

        $data = $this->settingsDataService->getSettingsData($userId, $page);

        // Inclure la vue avec les données
        extract($data);
        include __DIR__ . '/../View/settings.php';
    }

    /**
     * Met à jour l'email de l'utilisateur (AJAX).
     */
    public function updateEmail(): void
    {
        header('Content-Type: application/json');

        try
        {
            $userId = $this->requireAuthAndCsrfJson();

            $data = ['email' => trim($_POST['email'] ?? '')];

            // Validation
            $errors = $this->validationService->validateEmail($data);
            if (!empty($errors))
            {
                $this->jsonError(['errors' => $errors]);
            }

            // Mise à jour
            $email = $data['email'] ?? '';
            $success = $this->userModel->updateEmail($userId, $email);

            if ($success)
            {
                // Mettre à jour la session
                $_SESSION['user']['email'] = $email;
                $this->jsonSuccess(['message' => 'Email mis à jour avec succès']);
            } else
            {
                $this->jsonError(['errors' => ['email' => 'Email déjà utilisé ou invalide']]);
            }
        } catch (\Throwable $e)
        {
            error_log('SettingsController::updateEmail error: ' . $e->getMessage());
            $this->jsonError(['errors' => ['general' => 'Erreur interne du serveur']], 500);
        }
    }

    /**
     * Met à jour le pseudo de l'utilisateur (AJAX).
     */
    public function updatePseudo(): void
    {
        header('Content-Type: application/json');
        $userId = $this->requireAuthAndCsrfJson();

        $data = ['pseudo' => trim($_POST['pseudo'] ?? '')];

        // Validation
        $errors = $this->validationService->validatePseudo($data);
        if (!empty($errors))
        {
            $this->jsonError(['errors' => $errors]);
        }

        // Mise à jour
        $pseudo = $data['pseudo'] ?? '';
        $success = $this->userModel->updatePseudo($userId, $pseudo);

        if ($success)
        {
            $_SESSION['user']['pseudo'] = $pseudo;
            $this->jsonSuccess(['message' => 'Pseudo mis à jour avec succès']);
        } else
        {
            $this->jsonError(['errors' => ['pseudo' => 'Pseudo déjà utilisé ou invalide (alphanumérique, _, -)']]);
        }
    }

    /**
     * Met à jour le mot de passe de l'utilisateur (AJAX).
     */
    public function updatePassword(): void
    {
        header('Content-Type: application/json');
        $userId = $this->requireAuthAndCsrfJson();

        $data = [
            'current_password' => $_POST['current_password'] ?? '',
            'new_password' => $_POST['new_password'] ?? '',
            'new_password_confirm' => $_POST['confirm_password'] ?? '',
        ];

        // Validation
        $errors = $this->validationService->validatePasswordChange($data);
        if (!empty($errors))
        {
            $this->jsonError(['errors' => $errors]);
        }

        // Mise à jour
        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        $success = $this->userModel->updatePassword($userId, $currentPassword, $newPassword);

        if ($success)
        {
            $this->jsonSuccess(['message' => 'Mot de passe mis à jour avec succès']);
        } else
        {
            $this->jsonError(['errors' => ['current_password' => 'Mot de passe actuel incorrect']]);
        }
    }

    /**
     * Supprime le compte utilisateur (AJAX).
     */
    public function deleteAccount(): void
    {
        header('Content-Type: application/json');
        $userId = $this->requireAuthAndCsrfJson();

        // Validation
        $errors = $this->validationService->validateDeleteAccount($_POST);
        if (!empty($errors))
        {
            $this->jsonError(['errors' => $errors]);
        }

        // Suppression
        $success = $this->userModel->deleteAccount($userId, $_POST['password']);

        if ($success)
        {
            session_destroy();
            $this->jsonSuccess(['message' => 'Compte supprimé avec succès', 'redirect' => '?page=home']);
        } else
        {
            $this->jsonError(['errors' => ['password' => 'Mot de passe incorrect']]);
        }
    }

    /**
     * Met à jour une configuration utilisateur (AJAX).
     */
    public function updateUserConfig(): void
    {
        header('Content-Type: application/json');
        $userId = $this->requireAuthAndCsrfJson();

        $configKey = $_POST['config_key'] ?? '';
        $configValue = $_POST['config_value'] ?? '';

        if (empty($configKey))
        {
            $this->jsonError(['error' => 'Clé de configuration requise']);
        }

        try
        {
            $success = $this->userConfigModel->set($userId, $configKey, $configValue);

            if ($success)
            {
                $this->cache->delete('settings', 'user_config_' . $userId);
                $this->cache->clearNamespace('dashboard');
                $this->cache->clearNamespace('profile');

                $this->jsonSuccess(['message' => 'Configuration mise à jour avec succès']);
            } else
            {
                $this->jsonError(['error' => 'Erreur lors de la mise à jour']);
            }
        } catch (\InvalidArgumentException $e)
        {
            $this->jsonError(['error' => $e->getMessage()]);
        }
    }

    /**
     * Réinitialise une configuration utilisateur à sa valeur par défaut (AJAX).
     */
    public function resetUserConfig(): void
    {
        header('Content-Type: application/json');
        $userId = $this->requireAuthAndCsrfJson();

        $configKey = $_POST['config_key'] ?? '';

        if (empty($configKey))
        {
            $this->jsonError(['error' => 'Clé de configuration requise']);
        }

        try
        {
            $success = $this->userConfigModel->reset($userId, $configKey);

            if ($success)
            {
                $this->cache->delete('settings', 'user_config_' . $userId);
                $this->cache->clearNamespace('dashboard');
                $this->cache->clearNamespace('profile');

                $this->jsonSuccess(['message' => 'Configuration réinitialisée avec succès']);
            } else
            {
                $this->jsonError(['error' => 'Erreur lors de la réinitialisation']);
            }
        } catch (\InvalidArgumentException $e)
        {
            $this->jsonError(['error' => $e->getMessage()]);
        }
    }
}
