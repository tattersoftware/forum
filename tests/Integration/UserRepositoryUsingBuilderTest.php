<?php

declare(strict_types=1);

namespace Tests\Integration;

use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Test\DatabaseTestTrait;
use Domain\Entity\EntityNotFound;
use Domain\Entity\User\User;
use Domain\Entity\User\UserId;
use Domain\Entity\User\UserRepositoryUsingBuilder;
use Domain\Value\Email;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class UserRepositoryUsingBuilderTest extends TestCase
{
    use DatabaseTestTrait;

    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace;
    protected UserRepositoryUsingBuilder $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $builder = db_connect()->table('users');

        $this->repository = new UserRepositoryUsingBuilder($builder);
    }

    /**
     * @param array<string, scalar|null> $overrides
     */
    private function makeUser(array $overrides = []): array
    {
        return array_merge([
            'id'       => random_int(1000, 9999),
            'username' => $this->faker->userName,
            'email'    => $this->faker->email,
            'avatar'   => $this->faker->imageUrl,
            'active'   => 1,
        ], $overrides);
    }

    /**
     * @param array<string, scalar|null> $overrides
     */
    private function fakeUser(array $overrides = []): array
    {
        $userData = $this->makeUser($overrides);

        $this->db->table('users')->insert([
            'id'       => $userData['id'],
            'username' => $userData['username'],
            'active'   => $userData['active'],
        ]);

        $this->db->table('auth_identities')->insert([
            'user_id' => $userData['id'],
            'type'    => Session::ID_TYPE_EMAIL_PASSWORD,
            'secret'  => $userData['email'],
        ]);

        $this->db->table('profiles')->insert([
            'user_id' => $userData['id'],
            'avatar'  => $userData['avatar'],
        ]);

        return $userData;
    }

    public function testNextId(): void
    {
        $this->fakeUser(['id' => 14242]);

        $result = $this->repository->nextId();

        $this->assertSame(14243, $result->toInt());
    }

    public function testNextIdEmptyTable(): void
    {
        $this->db->table('users')->truncate();

        $result = $this->repository->nextId();

        $this->assertSame(1, $result->toInt());
    }

    public function testFind(): void
    {
        $userData = $this->fakeUser();
        $userId   = UserId::fromInt($userData['id']);

        $result = $this->repository->find($userId);

        $this->assertSame($userData['id'], $result->id->toInt());
    }

    public function testFindNotFound(): void
    {
        $this->expectException(EntityNotFound::class);
        $this->expectExceptionMessage('Entity ' . User::class . ' not found with identifier 42');

        $userId = UserId::fromInt(42);
        $this->repository->find($userId);
    }

    public function testSaveCreates(): void
    {
        $userData = $this->makeUser();
        $user     = new User(
            UserId::fromInt($userData['id']),
            $userData['username'],
            Email::fromString($userData['email']),
            $userData['avatar'],
        );

        $this->repository->save($user);

        $this->seeInDatabase('users', ['id' => $userData['id']]);
        $this->seeInDatabase('auth_identities', ['secret' => $userData['email']]);
        $this->seeInDatabase('profiles', ['avatar' => $userData['avatar']]);
    }

    public function testSaveUpdates(): void
    {
        $userData = $this->makeUser();
        $this->repository->save(new User(
            UserId::fromInt($userData['id']),
            $userData['username'],
            Email::fromString($userData['email']),
            $userData['avatar'],
        ));

        $this->repository->save(new User(
            UserId::fromInt($userData['id']),
            'DeadlyKitten',
            Email::fromString('banana@fruitguys.com'),
            '',
        ));

        $this->seeInDatabase('users', [
            'id'       => $userData['id'],
            'username' => 'DeadlyKitten',
        ]);
        $this->seeInDatabase('auth_identities', [
            'user_id' => $userData['id'],
            'secret'  => 'banana@fruitguys.com',
        ]);
        $this->seeInDatabase('profiles', [
            'user_id' => $userData['id'],
            'avatar'  => '',
        ]);
    }
}
