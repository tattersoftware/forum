<?php

declare(strict_types=1);

namespace Domain\Entity\User;

use CodeIgniter\Events\Events;
use CodeIgniter\Shield\Entities\User as ShieldUser;
use CodeIgniter\Shield\Models\UserModel;
use Domain\Entity\EntityNotFound;
use UnexpectedValueException;

final class UserRepositoryUsingShield implements UserRepository
{
    public function __construct(private UserModel $provider)
    {
    }

    /**
     * @throws EntityNotFound
     */
    public function find(UserId $id): User
    {
        $shield = $this->provider->find($id->toInt())
            ?? throw EntityNotFound::ofType(User::class, $id->__toString());

        /** @var ShieldUser $shield */
        $array = array_merge($shield->toArray(), [
            'email' => $shield->getEmail(),
        ]);

        return User::fromArray($array);
    }

    public function nextId(): UserId
    {
        $result = $this->provider
            ->builder()
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
        $this->provider->save($user->toArray());

        // Release domain events
        foreach ($user->releaseEvents() as $event) {
            Events::trigger((string) $event, $event);
        }
    }
}
