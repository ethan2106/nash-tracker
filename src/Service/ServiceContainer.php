<?php

namespace App\Service;

/**
 * Conteneur de services simple (Service Locator pattern).
 *
 * Permet l'injection de dépendances et facilite les tests.
 * Usage:
 *   $container = ServiceContainer::getInstance();
 *   $eauService = $container->get(EauService::class);
 */
final class ServiceContainer
{
    private static ?self $instance = null;

    /** @var array<class-string, object> */
    private array $services = [];

    /** @var array<class-string, callable> */
    private array $factories = [];

    private function __construct()
    {
        $this->registerDefaults();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Raccourci statique pour récupérer un service.
     *
     * Usage: ServiceContainer::make(GamificationService::class)
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public static function make(string $class): object
    {
        return self::getInstance()->get($class);
    }

    /**
     * Réinitialise le conteneur (utile pour les tests).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * Enregistre les services par défaut.
     */
    private function registerDefaults(): void
    {
        // GamificationService (pas de dépendances)
        $this->factories[GamificationService::class] = fn () => new GamificationService();

    }

    /**
     * Récupère un service (lazy loading + singleton).
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function get(string $class): object
    {
        if (!isset($this->services[$class]))
        {
            if (!isset($this->factories[$class]))
            {
                throw new \InvalidArgumentException("Service non enregistré: {$class}");
            }
            $this->services[$class] = ($this->factories[$class])();
        }

        return $this->services[$class];
    }

    /**
     * Enregistre un service personnalisé (pour les tests ou override).
     *
     * @template T of object
     *
     * @param class-string<T> $class
     * @param T               $service
     */
    public function set(string $class, object $service): void
    {
        $this->services[$class] = $service;
    }

    /**
     * Enregistre une factory personnalisée.
     *
     * @param class-string $class
     */
    public function register(string $class, callable $factory): void
    {
        $this->factories[$class] = $factory;
        unset($this->services[$class]); // Reset si déjà instancié
    }

    /**
     * Vérifie si un service est enregistré.
     *
     * @param class-string $class
     */
    public function has(string $class): bool
    {
        return isset($this->factories[$class]) || isset($this->services[$class]);
    }
}
