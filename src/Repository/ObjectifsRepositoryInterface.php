<?php

namespace App\Repository;

/**
 * Interface for objectifs data persistence.
 */
interface ObjectifsRepositoryInterface
{
    public function save(array $data): bool;
}
