<?php

namespace App\Controller;

use App\Service\AuthService;
use App\Service\CsrfService;
use App\Service\RateLimitService;
use App\Service\UserValidationService;
use App\Helper\JsonResponseTrait;

/**
 * UserController - Gère l'authentification et l'inscription des utilisateurs.
 * Responsabilités :
 * - Orchestration des services pour login/register/logout
 * - Gestion des pages et redirections
 * - Validation CSRF et rate limiting
 * - Délégation de la logique métier aux services
 */
class UserController
{
    use JsonResponseTrait;

    private AuthService $authService;
    private CsrfService $csrfService;
    private RateLimitService $rateLimitService;
    private UserValidationService $userValidationService;

    public function __construct(
        AuthService $authService,
        CsrfService $csrfService,
        RateLimitService $rateLimitService,
        UserValidationService $userValidationService
    ) {
        $this->authService = $authService;
        $this->csrfService = $csrfService;
        $this->rateLimitService = $rateLimitService;
        $this->userValidationService = $userValidationService;
    }

    public function register($data)
    {
        return $this->authService->register($data);
    }

    public function login($data)
    {
        return $this->authService->login($data);
    }

    public function handleLoginPage()
    {
        require_once dirname(__DIR__) . '/Config/session.php';

        // Générer le token CSRF pour la vue
        $this->csrfService->getToken();

        $data = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Valider le token CSRF
                $this->csrfService->validatePostToken();

                $data = $this->authService->login($_POST);
                $dataSuccess = $data['success'] ?? false;
                $dataMessage = $data['message'] ?? '';

                if ($data && $dataSuccess) {
                    // Gérer le cookie "remember me"
                    $this->authService->handleRememberMe($_POST);

                    $_SESSION['flash'] = [
                        'type' => 'success',
                        'msg' => $dataMessage,
                    ];
                    header('Location: ?page=home');
                    exit;
                } elseif ($data) {
                    $_SESSION['flash'] = [
                        'type' => 'error',
                        'msg' => $dataMessage,
                    ];
                }
            } catch (\InvalidArgumentException $e) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'msg' => $e->getMessage(),
                ];
                header('Location: ?page=login');
                exit;
            }
        }

        // Pré-remplir l'email si cookie existe
        $rememberedEmail = $this->authService->getRememberedEmail();
        require __DIR__ . '/../View/login.php';
    }

    public function handleRegisterPage()
    {
        require_once dirname(__DIR__) . '/Config/session.php';

        // Générer le token CSRF pour la vue
        $this->csrfService->getToken();

        $data = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Valider le token CSRF
                $this->csrfService->validatePostToken();

                $data = $this->authService->register($_POST);
                if ($data) {
                    $dataSuccess = $data['success'] ?? false;
                    $dataMessage = $data['message'] ?? '';
                    $_SESSION['flash'] = [
                        'type' => $dataSuccess ? 'success' : 'error',
                        'msg' => $dataMessage,
                    ];
                    if ($dataSuccess) {
                        header('Location: ?page=login');
                        exit;
                    }
                }
            } catch (\InvalidArgumentException $e) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'msg' => $e->getMessage(),
                ];
                header('Location: ?page=register');
                exit;
            }
        }
        require __DIR__ . '/../View/register.php';
    }

    public function handleApiCheckUnique()
    {
        try {
            header('Content-Type: application/json');

            // Vérifier le rate limiting
            try {
                $this->rateLimitService->checkRateLimit();
            } catch (\RuntimeException $e) {
                if ($e->getMessage() === 'rate_limited') {
                    http_response_code(429);
                    echo json_encode(['error' => 'rate_limited', 'retry_after' => $this->rateLimitService->getRetryAfter()]);
                    exit;
                }
                throw $e;
            }

            $email = $_GET['email'] ?? '';
            $pseudo = $_GET['pseudo'] ?? '';

            $response = $this->userValidationService->checkUniqueness($email, $pseudo);
            echo json_encode($response);

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erreur interne']);
        }
        exit;
    }

    public function handleLogout()
    {
        require_once dirname(__DIR__) . '/Config/session.php';

        $token = $_POST['csrf_token'] ?? '';

        // Vérification CSRF
        if ($this->csrfService->validateToken($token)) {
            // Déconnexion via le service
            $this->authService->logout();

            $_SESSION['flash'] = [
                'type' => 'info',
                'msg' => 'Déconnexion réussie',
            ];

            header('Location: ?page=login');
            exit;
        } else {
            // Échec de validation CSRF
            $_SESSION['flash'] = [
                'type' => 'error',
                'msg' => 'CSRF token invalide pour la déconnexion.',
            ];
            header('Location: ?page=home');
            exit;
        }
    }
}
