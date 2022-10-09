<?php

declare(strict_types=1);

namespace Domain\Entity\Board;

use Domain\Entity\EventRecording;
use Domain\Entity\Mapping;

final class Board
{
    use EventRecording;

    /**
     * @internal
     *
     * @param array<string, scalar|null> $array
     * @param Topic[]                    $topics
     */
    public static function fromArray(array $array, array $topics): self
    {
        return new self(
            BoardId::fromString(Mapping::getString($array, 'ulid')),
            Mapping::getString($array, 'name'),
            $topics,
        );
    }

    /**
     * @param Topic[] $topics
     */
    public function __construct(
        public readonly BoardId $id,
        private string $name,
        private array $topics,
    ) {
    }

    /**
     * @return Topic[]
     */
    public function getTopics(): array
    {
        return $this->topics;
    }

    /**
     * @internal
     */
    public function toArray(): array
    {
        return [
            'id'   => $this->id->__toString(),
            'name' => $this->name,
        ];
    }
}
