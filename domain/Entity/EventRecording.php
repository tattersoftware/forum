<?php

declare(strict_types=1);

namespace Domain\Entity;

use Stringable;

trait EventRecording
{
    /**
     * @var Stringable[]
     */
    private array $events = [];

    /**
     * @return Stringable[]
     */
    public function releaseEvents(): array
    {
        $events = $this->events;

        $this->events = [];

        return $events;
    }

    private function recordThat(Stringable $event): void
    {
        $this->events[] = $event;
    }
}
