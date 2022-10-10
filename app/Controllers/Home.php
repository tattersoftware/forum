<?php

namespace App\Controllers;

use Domain\Entity\User\UserId;
use Domain\Entity\User\UserRepository;

class Home extends BaseController
{
    public function __construct(private UserRepository $users)
    {
    }

    public function index(): string
    {
        $user = $this->users->find(UserId::fromInt(1));
        d($user);

        return view('welcome_message');
    }
}
