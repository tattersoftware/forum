<?php

declare(strict_types=1);

namespace Domain\Entity\Board;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Events\Events;
use Domain\Entity\EntityNotFound;
use Domain\Entity\UlidBuilderRepository;
use Symfony\Component\Uid\Factory\UlidFactory;

final class BoardRepositoryUsingBuilder extends UlidBuilderRepository implements BoardRepository
{
    public function __construct(
        private BaseBuilder $builder,
        private UlidFactory $ulids,
    ) {
    }

    /**
     * @throws EntityNotFound
     */
    public function find(BoardId $id): Board
    {
        if (null === $result = $this->fetch((string) $id)) {
            throw EntityNotFound::ofType(Board::class, (string) $id);
        }

        // Get the topics
        $rows = $this->builder
            ->db()
            ->table('topics')
            ->where('board_ulid', (string) $id)
            ->get()
            ->getResultArray();

        $topics = array_map(static fn ($array) => Topic::fromArray($array), $rows);

        return Board::fromArray($result, $topics);
    }

    public function nextId(): BoardId
    {
        return new BoardId($this->ulids->create());
    }

    public function nextTopicId(): TopicId
    {
        return new TopicId($this->ulids->create());
    }

    public function save(Board $board): void
    {
        $this->exists((string) $board->id)
            ? $this->update($board)
            : $this->insert($board);

        // Release domain events
        foreach ($board->releaseEvents() as $event) {
            Events::trigger((string) $event, $event);
        }
    }

    public function insert(Board $board): void
    {
        $this->builder->insert($board->toArray());

        foreach ($board->getTopics() as $topic) {
            $this->builder
                ->db()
                ->table('topics')
                ->insert($topic->toArray());
        }
    }

    public function update(Board $board): void
    {
        $this->builder->update($board->toArray(), [
            'ulid' => (string) $board->id,
        ]);

        $topicUlids = [];

        foreach ($board->getTopics() as $topic) {
            $topicUlids[] = (string) $topic->id;

            $this->builder
                ->db()
                ->table('topics')
                ->update($topic->toArray(), [
                    'ulid' => (string) $topic->id,
                ]);
        }
    }
}
