<?php

namespace App\Model;

/**
 * BaseModel - Classe de base pour tous les modèles.
 * Fournit l'initialisation commune de la base de données.
 */
abstract class BaseModel
{
    protected readonly \PDO $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }
}
