<?php

declare(strict_types=1);

namespace App\Container;

use App\Controllers\Home;
use CodeIgniter\CLI\CommandRunner;
use CodeIgniter\Database\ConnectionInterface;
use Config\Database;
use Domain\Entity\User\UserRepository;
use Domain\Entity\User\UserRepositoryUsingBuilder;

abstract class Container
{
    public static function getInstance(): DevelopmentContainer|ProductionContainer|TestingContainer
    {
        static $instance;

        if (! isset($instance)) {
            $environment = env('CI_ENVIRONMENT', 'production');
            $class       = '\\App\\Container\\' . ucfirst(strtolower($environment)) . 'Container';
            $instance    = new $class();
        }

        return $instance;
    }

    public function home(): Home {
        return new Home($this->userRepository());
    }

    public function commandRunner(): CommandRunner {
        return new CommandRunner();
    }

    protected function userRepository(): UserRepository
    {
        return new UserRepositoryUsingBuilder(Database::connect());
    }

    protected function connection(): ConnectionInterface
    {
        return Database::connect();
    }
}
