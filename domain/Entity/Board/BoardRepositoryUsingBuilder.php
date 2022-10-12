<?php

declare(strict_types=1);

namespace Domain\Entity\Board;

use CodeIgniter\Events\Events;
use CodeIgniter\I18n\Time;
use Domain\Entity\EntityNotFound;
use Domain\Entity\UlidBuilderRepository;

final class BoardRepositoryUsingBuilder extends UlidBuilderRepository implements BoardRepository
{
    private const TABLE = 'boards';

    /**
     * @throws EntityNotFound
     */
    public function find(BoardId $id): Board
    {
        if (null === $result = $this->fetch('boards', $id)) {
            throw EntityNotFound::ofType(Board::class, (string) $id);
        }

        // Get the topics
        $rows = $this->database
            ->table('topics')
            ->where('board_ulid', (string) $id)
            ->where('deleted_at IS NULL')
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
        $this->exists(self::TABLE, $board->id)
            ? $this->update($board)
            : $this->insert($board);

        // Release domain events
        foreach ($board->releaseEvents() as $event) {
            Events::trigger((string) $event, $event);
        }
    }

    public function insert(Board $board): void
    {
        $this->database->table(self::TABLE)->insert($board->toArray());

        foreach ($board->getTopics() as $topic) {
            $this->database
                ->table('topics')
                ->insert($topic->toArray());
        }
    }

    public function update(Board $board): void
    {
        $this->database->table(self::TABLE)->update($board->toArray(), [
            'ulid' => (string) $board->id,
        ]);

        $topicUlids = [];

        foreach ($board->getTopics() as $topic) {
            $topicUlids[] = (string) $topic->id;

            $this->database
                ->table('topics')
                ->where('ulid', (string) $topic->id)
                ->update($topic->toArray());
        }

        $this->database
            ->table('topics')
            ->whereNotIn('ulid', $topicUlids)
            ->update(['deleted_at' => (string) Time::now()]);
    }
}
