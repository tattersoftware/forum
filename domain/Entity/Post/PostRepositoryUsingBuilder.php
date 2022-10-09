<?php

declare(strict_types=1);

namespace Domain\Entity\Post;

use CodeIgniter\Events\Events;
use Domain\Entity\EntityNotFound;
use Domain\Entity\UlidBuilderRepository;

final class PostRepositoryUsingBuilder extends UlidBuilderRepository implements PostRepository
{
    private const TABLE = 'posts';

    /**
     * @throws EntityNotFound
     */
    public function find(PostId $id): Post
    {
        if (null === $result = $this->fetch(self::TABLE, $id)) {
            throw EntityNotFound::ofType(Post::class, (string) $id);
        }

        return Post::fromArray($result);
    }

    public function nextId(): PostId
    {
        return new PostId($this->ulids->create());
    }

    public function save(Post $post): void
    {
        if ($this->exists(self::TABLE, $post->id)) {
            $this->database->table(self::TABLE)->update($post->toArray(), [
                'ulid' => (string) $post->id,
            ]);
        } else {
            $this->database->table(self::TABLE)->insert($post->toArray());

            $event = new PostCreated($post->id, $post->author);
            Events::trigger((string) $event, $event);
        }

        // Release domain events
        foreach ($post->releaseEvents() as $event) {
            Events::trigger((string) $event, $event);
        }
    }
}
