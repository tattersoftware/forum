<?php

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class HomeTest extends TestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrateOnce = true;
    protected $seedOnce    = true;

    public function testRootShowsHomePage()
    {
        $result = $this->get('/');

        $result->assertStatus(200);
        $result->assertSee('The small framework with powerful features', 'h2');
    }

    public function testFourOhFour()
    {
        $this->expectException(PageNotFoundException::class);

        $this->get('bananas');
    }
}
