<?php

declare(strict_types=1);

namespace Domain\Entity\Board;

use Domain\Entity\EventRecording;
use Domain\Entity\Mapping;

final class Topic
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
            TopicId::fromString(Mapping::getString($array, 'ulid')),
            Mapping::getString($array, 'title'),
            Mapping::getString($array, 'summary'),
        );
    }

    public function __construct(
        public readonly TopicId $id,
        private string $title,
        private string $summary,
    ) {
    }

    /**
     * @internal
     */
    public function toArray(): array
    {
        return [
            'id'      => $this->id->__toString(),
            'title'   => $this->title,
            'summary' => $this->summary,
        ];
    }
}
