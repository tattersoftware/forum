<?php

declare(strict_types=1);

namespace Domain\Entity\Post;

use Domain\Entity\User\UserId;
use Stringable;

final class PostCreated implements Stringable
{
    public function __construct(
        public readonly PostId $postId,
        public readonly UserId $author,
    ) {
    }

    public function __toString(): string
    {
        return 'post_created';
    }
}
