<?php

namespace App\Model;

use Dotenv\Dotenv;
use PDOException;

/**
 * Database - Factory/Singleton pour obtenir la connexion PDO.
 * Utilise DatabaseWrapper pour le query counting.
 */
class Database
{
    /** @var DatabaseWrapper|null */
    private static ?DatabaseWrapper $instance = null;

    private static bool $envLoaded = false;

    /**
     * Retourne l'instance unique de la connexion DB (Singleton).
     */
    public static function getInstance(): DatabaseWrapper
    {
        if (self::$instance === null)
        {
            // Charger .env une seule fois
            if (!self::$envLoaded)
            {
                $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
                $dotenv->load();
                self::$envLoaded = true;
            }

            $dbPath = $_ENV['DB_PATH'] ?? __DIR__ . '/../../storage/db.sqlite';
            $dsn = 'sqlite:' . $dbPath;

            try
            {
                self::$instance = new DatabaseWrapper(
                    $dsn,
                    null,
                    null,
                    [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                        \PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $exception)
            {
                error_log('Connection error: ' . $exception->getMessage());

                throw $exception;
            }
        }

        return self::$instance;
    }

    /**
     * @deprecated Utiliser Database::getInstance() à la place
     */
    public function getConnection(): DatabaseWrapper
    {
        return self::getInstance();
    }

    /**
     * Réinitialise l'instance (utile pour les tests).
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }
}
