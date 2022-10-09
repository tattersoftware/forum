<?php

declare(strict_types=1);

namespace Domain\Entity\User;

use Webmozart\Assert\Assert;

final class UserId
{
    public static function fromInt(int $id): self
    {
        Assert::positiveInteger($id);

        return new self($id);
    }

    private function __construct(private int $id)
    {
    }

    public function toInt(): int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
