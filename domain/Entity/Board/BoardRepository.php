<?php

declare(strict_types=1);

namespace Domain\Entity\Board;

use Domain\Entity\EntityNotFound;

interface BoardRepository
{
    /**
     * @throws EntityNotFound
     */
    public function find(BoardId $id): Board;

    public function nextId(): BoardId;

    public function nextTopicId(): TopicId;

    public function save(Board $board): void;
}
