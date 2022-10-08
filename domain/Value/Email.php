<?php

declare(strict_types=1);

namespace Domain\Value;

use Webmozart\Assert\Assert;

final class Email
{
    public static function fromString(string $email): self
    {
        Assert::email($email);

        return new self($email);
    }

    private function __construct(private string $email)
    {
    }

    public function __toString(): string
    {
        return $this->email;
    }
}
