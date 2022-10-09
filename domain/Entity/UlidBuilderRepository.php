<?php

declare(strict_types=1);

namespace Domain\Entity;

use CodeIgniter\Database\BaseBuilder;

/**
 * @property BaseBuilder $builder
 */
abstract class UlidBuilderRepository
{
    protected function fetch(string $ulid): ?array
    {
        return $this->builder
            ->where('ulid', $ulid)
            ->limit(1)
            ->get()
            ->getRowArray();
    }

    protected function exists(string $ulid): bool
    {
        return (bool) $this->builder
            ->select('1')
            ->where('ulid', $ulid)
            ->limit(1)
            ->get()
            ->getRowArray();
    }
}
