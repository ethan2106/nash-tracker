<?php

namespace App\Repository;

use App\Model\ObjectifsModel;

/**
 * Objectifs repository implementation using ObjectifsModel.
 */
class ObjectifsRepository implements ObjectifsRepositoryInterface
{
    public function __construct(private ObjectifsModel $objectifsModel)
    {
    }

    public function save(array $data, int $userId): bool
    {
        return $this->objectifsModel->save($data, $userId);
    }
}
