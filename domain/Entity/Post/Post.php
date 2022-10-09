<?php

declare(strict_types=1);

namespace Domain\Entity\Post;

use Domain\Entity\EventRecording;
use Domain\Entity\Mapping;
use Domain\Entity\User\UserId;

final class Post
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
            PostId::fromString(Mapping::getString($array, 'ulid')),
            UserId::fromInt(Mapping::getIntId($array, 'user_id')),
            Mapping::getString($array, 'title'),
        );
    }

    public function __construct(
        public readonly PostId $id,
        public readonly UserId $author,
        private string $title,
    ) {
    }

    /**
     * @internal
     */
    public function toArray(): array
    {
        return [
            'id'      => $this->id->__toString(),
            'user_id' => $this->author->__toString(),
            'title'   => $this->title,
        ];
    }
}
