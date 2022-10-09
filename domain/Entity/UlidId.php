<?php

declare(strict_types=1);

namespace Domain\Entity;

use Symfony\Component\Uid\NilUlid;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

abstract class UlidId
{
    public static function fromString(string $id): static
    {
        $ulid = Ulid::fromString($id);
        Assert::notInstanceOf($ulid, NilUlid::class, 'Invalid ULID string: ' . $id);

        return new static($ulid);
    }

    final public function __construct(private Ulid $id)
    {
    }

    public function toUlid(): Ulid
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->id->__toString();
    }
}
