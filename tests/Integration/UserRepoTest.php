<?php

declare(strict_types=1);

namespace Tests\Integration;

use CodeIgniter\Shield\Entities\User as ShieldUser;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Test\DatabaseTestTrait;
use Domain\Entity\EntityNotFound;
use Domain\Entity\User\User;
use Domain\Entity\User\UserId;
use Domain\Entity\User\UserRepository;
use Domain\Value\Email;
use Tests\Support\TestCase;

/**
 * @internal
 */
abstract class UserRepoTest extends TestCase
{
    use DatabaseTestTrait;

    protected $namespace;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected UserRepository $repository;

    private function fakeUser(): ShieldUser
    {
        /** @var ShieldUser $user */
        $user = fake(UserModel::class);
        fake(UserIdentityModel::class, [
            'user_id' => $user->id,
            'secret'  => $this->faker->email,
        ]);

        return $user;
    }

    public function testNextId(): void
    {
        $this->fakeUser();
        $shield = $this->fakeUser();

        $result = $this->repository->nextId();

        $this->assertGreaterThan($shield->id, $result->toInt());
    }

    public function testFind(): void
    {
        $shield = $this->fakeUser();
        $userId = Userid::fromInt($shield->id);

        $result = $this->repository->find($userId);

        $this->assertSame($shield->id, $result->id->toInt());
    }

    public function testFindNotFound(): void
    {
        $this->expectException(EntityNotFound::class);
        $this->expectExceptionMessage('Entity ' . User::class . ' not found with identifier 4242');

        $userId = Userid::fromInt(4242);
        $this->repository->find($userId);
    }

    public function testSave(): void
    {
        // @see https://github.com/codeigniter4/shield/issues/471
        if (static::class === UserShieldTest::class) {
            $this->markTestSkipped('Shield Model cannot currently save arrays.');
        }

        $email  = 'me@codeigniter.com';
        $userId = $this->repository->nextId();
        $user   = new User(
            $userId,
            Email::fromString($email),
            'DeadlyKitten',
        );

        $this->repository->save($user);

        $this->seeInDatabase('auth_identities', ['secret' => $email]);
    }
}
