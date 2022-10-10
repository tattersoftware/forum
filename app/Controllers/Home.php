<?php

declare(strict_types=1);

namespace App\Controllers;

use Domain\Entity\User\UserId;
use Domain\Entity\User\UserRepository;

class Home extends BaseController
{
    public function __construct(private UserRepository $users)
    {
    }

    public function index(string $id): string
    {
        $user = $this->users->find(UserId::fromInt((int) $id));
        d($user);

        return view('welcome_message');
    }
}
