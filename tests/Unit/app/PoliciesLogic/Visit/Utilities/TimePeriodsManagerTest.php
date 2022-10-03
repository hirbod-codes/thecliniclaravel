<?php

namespace Tests\Unit\PoliciesLogic\Visit\Utilities;

use App\PoliciesLogic\Visit\Utilities\TimePeriodsManager;
use DateTimeZone;
use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;

/**
 * @covers TimePeriodsManager
 */
class TimePeriodsManagerTest extends TestCase
{
    private Generator $faker;

    private TimePeriodsManager $timePeriodsManager;

    private \DateTime $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->timePeriodsManager = new TimePeriodsManager();

        $this->now = new \DateTime('2000-1-1 13:00:00', new DateTimeZone('UTC'));
        // fwrite(STDOUT, 'hiiiiii');
    }

    // ----------------------------------------------------------------- SUBTRACTIONS ---------------------------------------------------------------------------------

    // ----------------------------------------------------------------- One Time Period ------------------------------------------------------------------------------

    public function testSubtraction0(): void
    {
        $timePeriods = [];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction01(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-5 hours')->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+25 hours')->getTimestamp(),
        ]];

        $this->subtractTimePeriodsFromTimePeriod(
            [[]],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction02(): void
    {
        $timePeriods = [[
            $this->now->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
        ]];

        $this->subtractTimePeriodsFromTimePeriod(
            [[]],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Outside ----------------------------------------------------------------------------------

    public function testSubtraction1(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-5 hours')->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-1 hours')->getTimestamp(),
        ]];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction11(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-5 hours')->getTimestamp(),
            $this->now->getTimestamp(),
        ]];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction2(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+25 hours')->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
        ]];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction21(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
        ]];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Inside ----------------------------------------------------------------------------------

    public function testSubtraction3(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+5 hours')->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
        ]];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+5 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction31(): void
    {
        $timePeriods = [[
            $this->now->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
        ]];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction32(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
        ]];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Cross ----------------------------------------------------------------------------------

    public function testSubtraction4(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-5 hours')->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+5 hours')->getTimestamp(),
        ]];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+5 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction41(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
        ]];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ----------------------------------------------------------------- Four Time Periods ----------------------------------------------------------------------------

    // ---------------------------------------------------------------------- Outside ----------------------------------------------------------------------------------

    public function testSubtraction5(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-20 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-18 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-12 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-9 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-5 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-4 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction51(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-20 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-18 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-12 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-9 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-5 hours')->getTimestamp(),
                $this->now->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction52(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+25 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+27 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+42 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+48 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction53(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+27 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+42 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+48 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction531(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-20 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-18 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-12 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction532(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-20 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-18 hours')->getTimestamp(),
                $this->now->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Inside ----------------------------------------------------------------------------------

    public function testSubtraction54(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction55(): void
    {
        $timePeriods = [
            [
                $this->now->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction56(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Cross -----------------------------------------------------------------------------------

    // ---------------------------------------------------------------------- First Crosses ---------------------------------------------------------------------------

    public function testSubtraction6(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-12 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-8 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [[
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
            ]],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction61(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-12 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-2 hours')->getTimestamp(),
            ],
            [
                $this->now->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [[
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
            ]],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction62(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-12 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-2 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction63(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-12 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
                $this->now->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction64(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-12 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction65(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-12 hours')->getTimestamp(),
            ],
            [
                $this->now->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction66(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-12 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction67(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                $this->now->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction68(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction69(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-18 hours')->getTimestamp(),
            ],
            [
                $this->now->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction610(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction611(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-24 hours')->getTimestamp(),
                $this->now->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction612(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Last Crosses ----------------------------------------------------------------------------

    public function testSubtraction613(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+38 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [[
                $this->now->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
            ]],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction614(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [[
                $this->now->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
            ]],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction615(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction616(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction617(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction618(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction619(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction620(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction621(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction622(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction623(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction624(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction625(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Mixed Crosses ---------------------------------------------------------------------------

    public function testSubtraction626(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction627(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
            ],
        ];

        $this->subtractTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ----------------------------------------------------------------- INTERSECTIONS --------------------------------------------------------------------------------

    // ----------------------------------------------------------------- Zero Time Period -----------------------------------------------------------------------------

    public function testIntersection0(): void
    {
        $timePeriods = [];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [[]],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ----------------------------------------------------------------- One Time Period ------------------------------------------------------------------------------

    public function testIntersection01(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-5 hours')->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+25 hours')->getTimestamp(),
        ]];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [[
                $this->now->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
            ]],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection02(): void
    {
        $timePeriods = [[
            $this->now->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
        ]];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [[
                $this->now->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
            ]],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Outside ---------------------------------------------------------------------------------

    public function testIntersection1(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-4 hours')->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-2 hours')->getTimestamp(),
        ]];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection11(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-4 hours')->getTimestamp(),
            $this->now->getTimestamp(),
        ]];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection12(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
        ]];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection13(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
        ]];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Inside ----------------------------------------------------------------------------------

    public function testIntersection2(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
        ]];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection21(): void
    {
        $timePeriods = [[
            $this->now->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
        ]];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection22(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
        ]];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Cross -----------------------------------------------------------------------------------

    public function testIntersection3(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-2 hours')->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
        ]];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection31(): void
    {
        $timePeriods = [[
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
            (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
        ]];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ----------------------------------------------------------------- Four Time Periods ----------------------------------------------------------------------------

    // ---------------------------------------------------------------------- Outside ---------------------------------------------------------------------------------

    public function testIntersection4(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-14 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-12 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-10 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-8 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-4 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-2 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection41(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-14 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-12 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-10 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-8 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-4 hours')->getTimestamp(),
                $this->now->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection42(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+38 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+40 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection43(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+38 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+40 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection44(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-8 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-4 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-2 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection45(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-8 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-4 hours')->getTimestamp(),
                $this->now->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Inside ----------------------------------------------------------------------------------

    public function testIntersection5(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            $timePeriods,
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection51(): void
    {
        $timePeriods = [
            [
                $this->now->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            $timePeriods,
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection52(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            $timePeriods,
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Cross -----------------------------------------------------------------------------------

    // ---------------------------------------------------------------------- First Crosses ---------------------------------------------------------------------------

    public function testIntersection6(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-14 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-8 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection61(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-14 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-8 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-4 hours')->getTimestamp(),
            ],
            [
                $this->now->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection62(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-14 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-8 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection63(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-14 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-8 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-4 hours')->getTimestamp(),
                $this->now->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection64(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-14 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-8 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection65(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-14 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-8 hours')->getTimestamp(),
            ],
            [
                $this->now->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection66(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-14 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-8 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection67(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-2 hours')->getTimestamp(),
                $this->now->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection68(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection69(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-4 hours')->getTimestamp(),
            ],
            [
                $this->now->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection610(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection611(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-2 hours')->getTimestamp(),
                $this->now->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection612(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+8 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Last Crosses ----------------------------------------------------------------------------

    public function testIntersection613(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+38 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [[
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
            ]],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection614(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [[
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
            ]],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection615(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection616(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection617(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection618(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection619(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection620(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection621(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+32 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection622(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection623(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+34 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection624(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection625(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+36 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+10 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+12 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+14 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+16 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Mixed Crosses ---------------------------------------------------------------------------

    public function testIntersection626(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-6 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-4 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+28 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+30 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection627(): void
    {
        $timePeriods = [
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('-2 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
            ],
            [
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+26 hours')->getTimestamp(),
            ],
        ];

        $this->findInterseciontsTimePeriodsFromTimePeriod(
            [
                [
                    $this->now->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+2 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+4 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+6 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+18 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+20 hours')->getTimestamp(),
                ],
                [
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+22 hours')->getTimestamp(),
                    (new \DateTime)->setTimestamp($this->now->getTimestamp())->modify('+24 hours')->getTimestamp(),
                ],
            ],
            $this->now->getTimestamp(),
            ((new \DateTime())->setTimestamp($this->now->getTimestamp()))->modify('+24 hours')->getTimestamp(),
            (1 * 60),
            $timePeriods,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ----------------------------------------------------------------------------------------------------------------------------------------------------------------

    public function startGetter(array $value): int
    {
        return $value[0];
    }

    public function endGetter(array $value): int
    {
        return $value[1];
    }

    /**
     * @param array $expected
     * @param integer $startTS
     * @param integer $endTS
     * @param integer $neededTime
     * @param array|\ArrayAccess|\Countable $timePeriods
     * @param array|\Closure $startGetter
     * @param array|\Closure $endGetter
     * @return \Generator<int, int[]>
     */
    private function subtractTimePeriodsFromTimePeriod(array $expected, int $startTS, int $endTS, int $neededTime, array|\ArrayAccess|\Countable $timePeriods, array|\Closure $startGetter, array|\Closure $endGetter): void
    {
        $count = 0;
        foreach ($this->timePeriodsManager->subtractTimePeriodsFromTimePeriod(
            $startTS,
            $endTS,
            $neededTime,
            $timePeriods,
            $startGetter,
            $endGetter
        ) as $key => $value) {
            $count++;
            $this->assertIsInt($key);
            $this->assertIsArray($value);
            $this->assertCount(count($expected[$key]), $value);
            foreach ($expected[$key] as $k => $v) {
                $this->assertArrayHasKey($k, $value);
                $this->assertSame($v, $value[$k]);
            }
        }

        $this->assertEquals(count($expected), $count);
    }

    /**
     * @param array $expected
     * @param integer $startTS
     * @param integer $endTS
     * @param integer $neededTime
     * @param array|\ArrayAccess|\Countable $timePeriods
     * @param array|\Closure $startGetter
     * @param array|\Closure $endGetter
     * @return \Generator<int, int[]>
     */
    private function findInterseciontsTimePeriodsFromTimePeriod(array $expected, int $startTS, int $endTS, int $neededTime, array|\ArrayAccess|\Countable $timePeriods, array|\Closure $startGetter, array|\Closure $endGetter): void
    {
        $count = 0;
        foreach ($this->timePeriodsManager->findIntersectionsOfTimePeriodsFromTimePeriod(
            $startTS,
            $endTS,
            $neededTime,
            $timePeriods,
            $startGetter,
            $endGetter
        ) as $key => $value) {
            $count++;
            $this->assertIsInt($key);
            $this->assertIsArray($value);
            $this->assertCount(count($expected[$key]), $value);
            foreach ($expected[$key] as $k => $v) {
                $this->assertArrayHasKey($k, $value);
                $this->assertSame($v, $value[$k]);
            }
        }

        $this->assertEquals(count($expected), $count);
    }
}
