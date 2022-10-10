<?php

declare(strict_types=1);

namespace App\Container;

use App\Controllers\Home;
use CodeIgniter\CLI\CommandRunner;
use CodeIgniter\Database\ConnectionInterface;
use Config\Database;
use Domain\Entity\User\UserRepository;
use Domain\Entity\User\UserRepositoryUsingBuilder;
use RuntimeException;

abstract class Container
{
    public static function getInstance(): DevelopmentContainer|ProductionContainer|TestingContainer
    {
        static $instance;

        if (! isset($instance)) {
            switch (env('CI_ENVIRONMENT', 'production')) {
                case 'development':
                    $instance = new DevelopmentContainer();
                    break;
                case 'production':
                    $instance = new ProductionContainer();
                    break;
                case 'testing':
                    $instance = new TestingContainer();
                    break;
                default:
                    throw new RuntimeException('The application environment is not set correctly.');
            }
        }

        return $instance;
    }
    
    protected function __construct()
    {
    }

    public function homeController(): Home
    {
        return new Home($this->userRepository());
    }

    protected function userRepository(): UserRepository
    {
        return new UserRepositoryUsingBuilder($this->database());
    }

    protected function database(): ConnectionInterface
    {
        return Database::connect();
    }
}
