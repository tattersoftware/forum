<?php

declare(strict_types=1);

namespace Domain\Entity;

use RuntimeException;

final class EntityNotFound extends RuntimeException
{
    /**
     * @param class-string $class
     */
    public static function ofType(string $class, string $id): self
    {
        return new self(sprintf('Entity %s not found with identifier %s', $class, $id));
    }
}
