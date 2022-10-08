<?php

namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use Nexus\PHPUnit\Extension\Expeditable;

/**
 * @internal
 */
abstract class TestCase extends CIUnitTestCase
{
    use Expeditable;

    /**
     * Methods to run during tearDown.
     *
     * @var array of methods
     */
    protected $tearDownMethods = ['resetServices'];

    // --------------------------------------------------------------------
    // Database Properties
    // --------------------------------------------------------------------

    /**
     * The namespace(s) to help us find the migration classes.
     * Empty is equivalent to running `spark migrate -all`.
     * Note that running "all" runs migrations in date order,
     * but specifying namespaces runs them in namespace order (then date)
     *
     * @var array|string|null
     */
    protected $namespace;
}
