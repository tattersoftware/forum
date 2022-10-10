<?php

declare(strict_types=1);

namespace App\Container;

use CodeIgniter\CodeIgniter as Core;
use CodeIgniter\Controller; 
use Config\App;
use Config\Services;

class CodeIgniter extends Core
{
    public readonly Container $container;

    public function __construct(App $config, Container $container)
    {
        $this->container = $container;

        parent::__construct($config);
    }

    /**
     * Instantiates the controller class with proper dependencies.
     *
     * @return Controller
     */
    protected function createController()
    {
        $segments = explode('\\', $this->controller);
        $method   = end($segments);
        $method   = lcfirst($method);

        $controller = $this->container->$method();
        $controller->initController($this->request, $this->response, Services::logger());

        $this->benchmark->stop('controller_constructor');

        return $controller;
    }

    /**
     * Workaround for testing so this class can be used
     * instead of the typical MockCodeIgniter.
     */
    protected function callExit($code)
    {
        if (ENVIRONMENT !== 'testing') {
            parent::callExit($code);
        }
    }
}
