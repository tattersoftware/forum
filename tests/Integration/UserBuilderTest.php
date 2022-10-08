<?php

declare(strict_types=1);

namespace Tests\Integration;

use Domain\Entity\User\UserRepositoryUsingBuilder;

/**
 * @internal
 */
final class UserBuilderTest extends UserRepoTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $builder = db_connect()->table('users');

        $this->repository = new UserRepositoryUsingBuilder($builder);
    }
}
