<?php

declare(strict_types=1);

namespace Domain\Entity\User;

use Domain\Entity\EntityNotFound;

interface UserRepository
{
    /**
     * @throws EntityNotFound
     */
    public function find(UserId $id): User;

    public function nextId(): UserId;

    public function save(User $user): void;
}
