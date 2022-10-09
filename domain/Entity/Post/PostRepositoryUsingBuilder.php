<?php

declare(strict_types=1);

namespace Domain\Entity\Post;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Events\Events;
use Domain\Entity\EntityNotFound;
use Domain\Entity\UlidBuilderRepository;
use Symfony\Component\Uid\Factory\UlidFactory;

final class PostRepositoryUsingBuilder extends UlidBuilderRepository implements PostRepository
{
    public function __construct(
        private BaseBuilder $builder,
        private UlidFactory $ulids,
    ) {
    }

    /**
     * @throws EntityNotFound
     */
    public function find(PostId $id): Post
    {
        if (null === $result = $this->fetch((string) $id)) {
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
        if ($this->exists((string) $post->id)) {
            $this->builder->update($post->toArray(), [
                'ulid' => (string) $post->id,
            ]);
        } else {
            $this->builder->insert($post->toArray());

            $event = new PostCreated($post->id, $post->author);
            Events::trigger((string) $event, $event);
        }

        // Release domain events
        foreach ($post->releaseEvents() as $event) {
            Events::trigger((string) $event, $event);
        }
    }
}
