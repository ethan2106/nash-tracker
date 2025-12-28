<?php

namespace App\Model;

use PDO;
use PDOException;
use PDOStatement;

/**
 * DatabaseWrapper - Wrapper PDO pour mesurer les performances des requêtes DB.
 */
class DatabaseWrapper extends PDO
{
    private $queryCount = 0;

    public function __construct($dsn, $username = null, $passwd = null, $options = null)
    {
        parent::__construct($dsn, $username, $passwd, $options);
    }

    /**
     * Override prepare pour compter les requêtes.
     */
    public function prepare($query, $options = null): PDOStatement|false
    {
        $this->incrementQueryCount();

        return parent::prepare($query, $options ?? []);
    }

    /**
     * Override query pour compter les requêtes.
     */
    public function query($query, $fetchMode = null, ...$fetchModeArgs): PDOStatement|false
    {
        $this->incrementQueryCount();

        return parent::query($query, $fetchMode, ...$fetchModeArgs);
    }

    /**
     * Override exec pour compter les requêtes.
     */
    public function exec($statement): int|false
    {
        $this->incrementQueryCount();

        return parent::exec($statement);
    }

    /**
     * Incrémente le compteur de requêtes.
     */
    private function incrementQueryCount(): void
    {
        $this->queryCount++;
        if (class_exists('App\Service\PerformanceMetrics'))
        {
            \App\Service\PerformanceMetrics::getInstance()->incrementDbQueries();
        }
    }

    /**
     * Retourne le nombre de requêtes exécutées.
     */
    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    /**
     * Réinitialise le compteur.
     */
    public function resetQueryCount(): void
    {
        $this->queryCount = 0;
    }
}
