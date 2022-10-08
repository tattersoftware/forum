<?php

declare(strict_types=1);

use CodeIgniter\CodingStandard\CodeIgniter4;
use Nexus\CsConfig\Factory;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->files()
    ->in([
        __DIR__ . '/app/',
        __DIR__ . '/domain/',
        __DIR__ . '/tests/',
    ])
    ->exclude('build')
    ->append([__FILE__]);

$overrides = [
    'declare_strict_types' => true,
    'modernize_strpos'     => false,
    'octal_notation'       => false,
    'void_return'          => false,
];

$options = [
    'finder'    => $finder,
    'cacheFile' => 'build/.php-cs-fixer.cache',
];

return Factory::create(new CodeIgniter4(), $overrides, $options)->forProjects();
