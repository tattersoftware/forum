<?php

declare(strict_types=1);

namespace Domain\Entity\User;

use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Events\Events;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use Domain\Entity\EntityNotFound;

final class UserRepositoryUsingBuilder implements UserRepository
{
    private const TABLE = 'users';

    public function __construct(private ConnectionInterface $database)
    {
    }

    /**
     * @throws EntityNotFound
     */
    public function find(UserId $id): User
    {
        $result = $this->database
            ->table(self::TABLE)
            ->select('users.id, users.username, profiles.avatar, auth_identities.secret as email')
            ->join('profiles', 'profiles.user_id = users.id')
            ->join('auth_identities', 'auth_identities.user_id = users.id')
            ->where('users.id', $id->toInt())
            ->where('auth_identities.type', Session::ID_TYPE_EMAIL_PASSWORD)
            ->where('users.deleted_at IS NULL')
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
        $result = $this->database
            ->table(self::TABLE)
            ->selectMax('id', 'id')
            ->get()
            ->getRowArray();

        // SELECT MAX will return $result['id'] = null so casting to int works as expected
        return UserId::fromInt((int) $result['id'] + 1);
    }

    public function save(User $user): void
    {
        $exists = (bool) $this->database
            ->table(self::TABLE)
            ->select('1')
            ->where('id', (string) $user->id)
            ->where('deleted_at IS NULL')
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
        $this->database->table(self::TABLE)->insert([
            'id'       => $data['id'],
            'username' => $data['username'],
            'active'   => 1,
        ]);

        // `auth_identities`
        $this->database
            ->table('auth_identities')
            ->insert([
                'user_id' => $data['id'],
                'type'    => Session::ID_TYPE_EMAIL_PASSWORD,
                'secret'  => $data['email'],
            ]);

        // `profiles`
        $this->database
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
        $this->database
            ->table(self::TABLE)
            ->where('id', $data['id'])
            ->where('deleted_at IS NULL')
            ->update(['username' => $data['username']]);

        // `auth_identities`
        $this->database
            ->table('auth_identities')
            ->where('user_id', $data['id'])
            ->where('type', Session::ID_TYPE_EMAIL_PASSWORD)
            ->update(['secret' => $data['email']]);

        // `profiles`
        $this->database
            ->table('profiles')
            ->where('user_id', $data['id'])
            ->update(['avatar' => $data['avatar']]);
    }
}
