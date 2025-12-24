<?php

namespace App\Repository;

use App\Model\ObjectifsModel;

/**
 * Objectifs repository implementation using ObjectifsModel.
 */
class ObjectifsRepository implements ObjectifsRepositoryInterface
{
    public function save(array $data): bool
    {
        return ObjectifsModel::save($data);
    }
}
