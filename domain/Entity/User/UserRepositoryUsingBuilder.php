<?php

declare(strict_types=1);

namespace Domain\Entity\User;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Events\Events;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use Domain\Entity\EntityNotFound;
use UnexpectedValueException;

final class UserRepositoryUsingBuilder implements UserRepository
{
    public function __construct(private BaseBuilder $builder)
    {
    }

    /**
     * @throws EntityNotFound
     */
    public function find(UserId $id): User
    {
        $result = $this->builder
            ->select('users.*, auth_identities.secret as email')
            ->join('auth_identities', 'auth_identities.user_id = users.id')
            ->where('users.id', $id->toInt())
            ->where('auth_identities.type', Session::ID_TYPE_EMAIL_PASSWORD)
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($result === null) {
            throw EntityNotFound::ofType(User::class, $id->__toString());
        }

        return User::fromArray($result);
    }

    public function nextId(): UserId
    {
        $result = $this->builder
            ->selectMax('id', 'id')
            ->get()
            ->getRowArray();

        if ($result === null) {
            throw new UnexpectedValueException('Unable to locate the next ID for ' . User::class);
        }

        return UserId::fromInt((int) $result['id'] + 1);
    }

    public function save(User $user): void
    {
        // Handle email separate
        $data  = $user->toArray();
        $email = $data['email'];
        unset($data['email']);

        $exists = (bool) $this->builder
            ->select('1')
            ->where('users.id', $data['id'])
            ->limit(1)
            ->get()
            ->getRowArray();

        $exists ? $this->update($data, $email) : $this->insert($data, $email);

        // Release domain events
        foreach ($user->releaseEvents() as $event) {
            Events::trigger((string) $event, $event);
        }
    }

    /**
     * @param array<string, scalar|null> $data
     */
    private function insert(array $data, string $email): void
    {
        $this->builder->insert($data);
        $this->builder
            ->db()
            ->table('auth_identities')
            ->insert([
                'user_id' => $data['id'],
                'type'    => Session::ID_TYPE_EMAIL_PASSWORD,
                'secret'  => $email,
            ]);
    }

    /**
     * @param array<string, scalar|null> $data
     */
    private function update(array $data, string $email): void
    {
        $this->builder->update($data, ['id' => $data['id']]);
        $this->builder
            ->db()
            ->table('auth_identities')
            ->update(['secret' => $email], [
                'user_id' => $data['id'],
                'type'    => Session::ID_TYPE_EMAIL_PASSWORD,
            ]);
    }
}
