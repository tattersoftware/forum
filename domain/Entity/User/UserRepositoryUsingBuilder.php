<?php

declare(strict_types=1);

namespace Domain\Entity\User;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Events\Events;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use Domain\Entity\EntityNotFound;

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
            ->select('users.id, users.username, profiles.avatar, auth_identities.secret as email')
            ->join('profiles', 'profiles.user_id = users.id')
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

        // SELECT MAX will return $result['id'] = null so casting to int works as expected
        return UserId::fromInt((int) $result['id'] + 1);
    }

    public function save(User $user): void
    {
        $exists = (bool) $this->builder
            ->select('1')
            ->where('users.id', $user->id->__toString())
            ->limit(1)
            ->get()
            ->getRowArray();

        $exists ? $this->update($user) : $this->insert($user);

        // Release domain events
        foreach ($user->releaseEvents() as $event) {
            Events::trigger((string) $event, $event);
        }
    }

    private function insert(User $user): void
    {
        $data = $user->toArray();

        // `users`
        $this->builder->insert([
            'id'       => $data['id'],
            'username' => $data['username'],
            'active'   => 1,
        ]);

        // `auth_identities`
        $this->builder
            ->db()
            ->table('auth_identities')
            ->insert([
                'user_id' => $data['id'],
                'type'    => Session::ID_TYPE_EMAIL_PASSWORD,
                'secret'  => $data['email'],
            ]);

        // `profiles`
        $this->builder
            ->db()
            ->table('profiles')
            ->insert([
                'user_id' => $data['id'],
                'avatar'  => $data['avatar'],
            ]);
    }

    private function update(User $user): void
    {
        $data = $user->toArray();

        // `users`
        $this->builder->update([
            'username' => $data['username'],
        ], ['id' => $data['id']]);

        // `auth_identities`
        $this->builder
            ->db()
            ->table('auth_identities')
            ->update([
                'secret' => $data['email'],
            ], [
                'user_id' => $data['id'],
                'type'    => Session::ID_TYPE_EMAIL_PASSWORD,
            ]);

        // `profiles`
        $this->builder
            ->db()
            ->table('profiles')
            ->update([
                'avatar' => $data['avatar'],
            ], ['user_id' => $data['id']]);
    }
}
