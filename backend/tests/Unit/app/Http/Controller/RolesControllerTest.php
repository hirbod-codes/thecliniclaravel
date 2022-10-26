<?php

namespace Tests\Unit\app\Http\Controller;

use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;

/**
 * @covers \App\Http\Controllers\RolesController
 * @ignore description
 */
class RolesControllerTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }
}
