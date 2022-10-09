<?php

declare(strict_types=1);

namespace Domain\Entity\Post;

use Domain\Entity\EntityNotFound;

interface PostRepository
{
    /**
     * @throws EntityNotFound
     */
    public function find(PostId $id): Post;

    public function nextId(): PostId;

    public function save(Post $post): void;
}
