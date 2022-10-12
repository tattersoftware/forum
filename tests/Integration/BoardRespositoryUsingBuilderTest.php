<?php

declare(strict_types=1);

namespace Tests\Integration;

use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Test\DatabaseTestTrait;
use Domain\Entity\Board\Board;
use Domain\Entity\Board\BoardId;
use Domain\Entity\Board\BoardRepositoryUsingBuilder;
use Domain\Entity\EntityNotFound;
use Domain\Value\Email;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class BoardRespositoryUsingBuilderTest extends TestCase
{
    use DatabaseTestTrait;

    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace;
    protected BoardRepositoryUsingBuilder $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new BoardRepositoryUsingBuilder($this->db);
    }

    /**
     * @param array<string, scalar|null> $overrides
     */
    private function makeBoard(array $overrides = []): array
    {
        return array_merge([
            'id'        => random_int(1000, 9999),
            'boardname' => $this->faker->boardName,
            'email'     => $this->faker->email,
            'avatar'    => $this->faker->imageUrl,
            'active'    => 1,
        ], $overrides);
    }

    /**
     * @param array<string, scalar|null> $overrides
     */
    private function fakeBoard(array $overrides = []): array
    {
        $boardData = $this->makeBoard($overrides);

        $this->db->table('boards')->insert([
            'id'        => $boardData['id'],
            'boardname' => $boardData['boardname'],
            'active'    => $boardData['active'],
        ]);

        $this->db->table('auth_identities')->insert([
            'board_id' => $boardData['id'],
            'type'     => Session::ID_TYPE_EMAIL_PASSWORD,
            'secret'   => $boardData['email'],
        ]);

        $this->db->table('profiles')->insert([
            'board_id' => $boardData['id'],
            'avatar'   => $boardData['avatar'],
        ]);

        return $boardData;
    }

    public function testNextId(): void
    {
        $this->fakeBoard(['id' => 14242]);

        $result = $this->repository->nextId();

        $this->assertSame(14243, $result->toInt());
    }

    public function testNextIdEmptyTable(): void
    {
        $this->db->table('boards')->truncate();

        $result = $this->repository->nextId();

        $this->assertSame(1, $result->toInt());
    }

    public function testFind(): void
    {
        $boardData = $this->fakeBoard();
        $boardId   = BoardId::fromInt($boardData['id']);

        $result = $this->repository->find($boardId);

        $this->assertSame($boardData['id'], $result->id->toInt());
    }

    public function testFindNotFound(): void
    {
        $this->expectException(EntityNotFound::class);
        $this->expectExceptionMessage('Entity ' . Board::class . ' not found with identifier 42');

        $boardId = BoardId::fromInt(42);
        $this->repository->find($boardId);
    }

    public function testSaveCreates(): void
    {
        $boardData = $this->makeBoard();
        $board     = new Board(
            BoardId::fromInt($boardData['id']),
            $boardData['boardname'],
            Email::fromString($boardData['email']),
            $boardData['avatar'],
        );

        $this->repository->save($board);

        $this->seeInDatabase('boards', ['id' => $boardData['id']]);
        $this->seeInDatabase('auth_identities', ['secret' => $boardData['email']]);
        $this->seeInDatabase('profiles', ['avatar' => $boardData['avatar']]);
    }

    public function testSaveUpdates(): void
    {
        $boardData = $this->makeBoard();
        $this->repository->save(new Board(
            BoardId::fromInt($boardData['id']),
            $boardData['boardname'],
            Email::fromString($boardData['email']),
            $boardData['avatar'],
        ));

        $this->repository->save(new Board(
            BoardId::fromInt($boardData['id']),
            'DeadlyKitten',
            Email::fromString('banana@fruitguys.com'),
            '',
        ));

        $this->seeInDatabase('boards', [
            'id'        => $boardData['id'],
            'boardname' => 'DeadlyKitten',
        ]);
        $this->seeInDatabase('auth_identities', [
            'board_id' => $boardData['id'],
            'secret'   => 'banana@fruitguys.com',
        ]);
        $this->seeInDatabase('profiles', [
            'board_id' => $boardData['id'],
            'avatar'   => '',
        ]);
    }
}
