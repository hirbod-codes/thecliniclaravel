<?php

namespace Tests\Unit\database\interactions\Business;

use App\DataStructures\Time\DSDownTimes;
use App\DataStructures\Time\DSWeeklyTimePatterns;
use Database\Interactions\Business\DataBaseRetrieveBusinessSettings;
use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;

/**
 * @covers \Database\Interactions\Business\DataBaseRetrieveBusinessSettingsTest
 */
class DataBaseRetrieveBusinessSettingsTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testGetWorkSchdule(): void
    {
        $workSchdule = (new DataBaseRetrieveBusinessSettings)->getWorkSchdule();

        $this->assertInstanceOf(DSWeeklyTimePatterns::class, $workSchdule);
    }

    public function testGetDownTimes(): void
    {
        $downTimes = (new DataBaseRetrieveBusinessSettings)->getDownTimes();

        $this->assertInstanceOf(DSDownTimes::class, $downTimes);
    }

    public function testGetDefaultRegularOrderPrice(): void
    {
        $defaultRegularOrderPrice = (new DataBaseRetrieveBusinessSettings)->getDefaultRegularOrderPrice();

        $this->assertIsInt($defaultRegularOrderPrice);
    }

    public function testGetDefaultRegularOrderTimeConsumption(): void
    {
        $defaultRegularOrderTimeConsumption = (new DataBaseRetrieveBusinessSettings)->getDefaultRegularOrderTimeConsumption();

        $this->assertIsInt($defaultRegularOrderTimeConsumption);
    }
}
