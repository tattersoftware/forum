<?php

declare(strict_types=1);

namespace Domain\Entity;

use CodeIgniter\Database\ConnectionInterface;
use Symfony\Component\Uid\Factory\UlidFactory;

abstract class UlidBuilderRepository
{
    public function __construct(
        protected ConnectionInterface $database,
        protected UlidFactory $ulids,
    ) {
    }

    protected function fetch(string $table, UlidId $id): ?array
    {
        return $this->database
            ->table($table)
            ->where('ulid', (string) $id)
            ->limit(1)
            ->get()
            ->getRowArray();
    }

    protected function exists(string $table, UlidId $id): bool
    {
        return (bool) $this->database
            ->table($table)
            ->select('1')
            ->where('ulid', (string) $id)
            ->limit(1)
            ->get()
            ->getRowArray();
    }
}
