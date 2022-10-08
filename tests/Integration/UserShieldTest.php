<?php

declare(strict_types=1);

namespace Tests\Integration;

use CodeIgniter\Shield\Models\UserModel;
use Domain\Entity\User\UserRepositoryUsingShield;

/**
 * @internal
 */
final class UserShieldTest extends UserRepoTest
{
    protected function setUp(): void
    {
        parent::setUp();

        /** @var UserModel $provider */
        $provider = model(config('Auth')->userProvider);

        $this->repository = new UserRepositoryUsingShield($provider);
    }
}
