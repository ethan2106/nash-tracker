<?php

namespace App\Config;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

/**
 * Simple DI Container Configuration
 * Only essential services are registered here.
 * Keep it minimal - not everything goes in the container.
 */
class DIContainer
{
    private static ?ContainerInterface $container = null;

    public static function getContainer(): ContainerInterface
    {
        if (self::$container === null)
        {
            $builder = new ContainerBuilder();

            // Enable compilation for performance in prod only
            if (getenv('APP_ENV') !== 'dev') {
                $builder->enableCompilation(__DIR__ . '/../../storage/cache/di');
            }

            // Add definitions
            $builder->addDefinitions([
                // Database Wrapper (PDO)
                \App\Model\DatabaseWrapper::class => function () {
                    if (!isset($_ENV['DB_PATH'])) {
                        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
                        $dotenv->load();
                    }

                    $dbPath = $_ENV['DB_PATH'] ?? __DIR__ . '/../../storage/db.sqlite';
                    $dsn = 'sqlite:' . $dbPath;

                    return new \App\Model\DatabaseWrapper(
                        $dsn,
                        null,
                        null,
                        [
                            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                            \PDO::ATTR_EMULATE_PREPARES => false,
                        ]
                    );
                },

                // PDO alias to DatabaseWrapper
                \PDO::class => \DI\get(\App\Model\DatabaseWrapper::class),

                // Logger (already configured in index.php, but can be injected)
                \Monolog\Logger::class => function () {
                    $logger = new \Monolog\Logger('nash-tracker');
                    $logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/../../storage/app.log', \Monolog\Logger::WARNING));
                    $logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::DEBUG));
                    $logger->pushProcessor(new \Monolog\Processor\PsrLogMessageProcessor());

                    return $logger;
                },

                // CacheService
                \App\Service\CacheService::class => \DI\autowire(),

                // OpenFoodFactsService (depends on CacheService)
                \App\Service\OpenFoodFactsService::class => \DI\autowire(),

                // FoodManager (depends on MealModel, OpenFoodFactsService, UploadService)
                \App\Service\FoodManager::class => \DI\autowire(),

                // MealManager (depends on MealModel)
                \App\Service\MealManager::class => \DI\autowire(),

                // ActivityService (depends on PDO)
                \App\Service\ActivityService::class => \DI\autowire(),

                // Models (simple, no deps)
                \App\Model\MealModel::class => \DI\autowire(),
                \App\Model\UserModel::class => \DI\autowire(),
                \App\Model\HistoriqueMesuresModel::class => \DI\autowire(),
                \App\Model\ObjectifsModel::class => \DI\autowire(),
                \App\Model\UserConfigModel::class => \DI\autowire(),
                \App\Model\PriseMedicamentModel::class => \DI\autowire(),
                \App\Model\SymptomModel::class => \DI\autowire(),
                \App\Model\User::class => \DI\autowire(),
                \App\Model\WalkModel::class => \DI\autowire(),
                \App\Model\ActivityModel::class => \DI\autowire(),
                \App\Model\ReportsModel::class => \DI\autowire(),

                // ValidationService
                \App\Service\ValidationService::class => \DI\autowire(),

                // GamificationService
                \App\Service\GamificationService::class => \DI\autowire(),

                \App\Service\NAFLDAdviceService::class => \DI\autowire(),

                // NutritionService
                \App\Service\NutritionService::class => \DI\autowire(),

                // Profile services
                \App\Service\ProfileDataService::class => \DI\autowire(),
                \App\Service\ProfileApiService::class => \DI\autowire(),

                // Repository
                \App\Repository\ObjectifsRepositoryInterface::class => \DI\autowire(\App\Repository\ObjectifsRepository::class),
                \App\Repository\FoodRepositoryInterface::class => \DI\autowire(\App\Repository\FoodRepository::class),
                \App\Repository\MealRepositoryInterface::class => \DI\autowire(\App\Repository\MealRepository::class),

                // Imc services
                \App\Service\ImcDataService::class => \DI\autowire(),
                \App\Service\ImcApiService::class => \DI\autowire(),
                \App\Service\ImcSaveService::class => \DI\autowire(),

                // Food services
                \App\Service\FoodDataService::class => \DI\autowire(),
                \App\Service\FoodApiService::class => \DI\autowire(),
                \App\Service\FoodSaveService::class => \DI\autowire(),

                // Settings services
                \App\Service\SettingsDataService::class => \DI\autowire(),

                // Medicament services
                \App\Service\MedicamentService::class => \DI\autowire(),

                // User services
                \App\Service\AuthService::class => \DI\autowire(),
                \App\Service\CsrfService::class => \DI\autowire(),
                \App\Service\RateLimitService::class => \DI\autowire(),
                \App\Service\UserValidationService::class => \DI\autowire(),

                // Controllers
                \App\Controller\FoodController::class => \DI\autowire(),
                \App\Controller\HomeController::class => \DI\autowire(),
                \App\Controller\ProfileController::class => \DI\autowire(),
                \App\Controller\ImcController::class => \DI\autowire(),
                \App\Controller\SettingsController::class => \DI\autowire(),
                \App\Controller\MedicamentController::class => \DI\autowire(),
                \App\Controller\UserController::class => \DI\autowire(),
                \App\Controller\MealController::class => \DI\autowire(),
            ]);

            self::$container = $builder->build();
        }

        return self::$container;
    }
}
