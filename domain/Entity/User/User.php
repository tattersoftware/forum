<?php

declare(strict_types=1);

namespace Domain\Entity\User;

use Domain\Entity\EventRecording;
use Domain\Entity\Mapping;
use Domain\Value\Email;

final class User
{
    use EventRecording;

    /**
     * @internal
     *
     * @param array<string, scalar|null> $array
     */
    public static function fromArray(array $array): self
    {
        return new self(
            UserId::fromInt(Mapping::getId($array, 'id')),
            Mapping::getString($array, 'username'),
            Email::fromString(Mapping::getString($array, 'email')),
            Mapping::getString($array, 'avatar'),
        );
    }

    public function __construct(
        public readonly UserId $id,
        private string $handle,
        private Email $email,
        private string $avatar,
    ) {
    }

    /**
     * @internal
     */
    public function toArray(): array
    {
        return [
            'id'       => $this->id->toInt(),
            'username' => $this->handle,
            'email'    => $this->email->__toString(),
            'avatar'   => $this->avatar,
        ];
    }
}
