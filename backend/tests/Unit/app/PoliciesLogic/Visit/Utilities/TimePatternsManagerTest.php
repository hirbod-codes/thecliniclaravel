<?php

namespace Tests\Unit\app\PoliciesLogic\Visit\Utilities;

use App\PoliciesLogic\Visit\Utilities\TimePatternsManager;
use DateTimeZone;
use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;

/**
 * @covers TimePatternsManager
 */
class TimePatternsManagerTest extends TestCase
{
    private Generator $faker;

    private TimePatternsManager $timePatternsManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->timePatternsManager = new TimePatternsManager();
    }

    // ----------------------------------------------------------------- SUBTRACTIONS ---------------------------------------------------------------------------------

    public function testSubtraction0(): void
    {
        $timePatterns = [];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ----------------------------------------------------------------- One Time Pattern ------------------------------------------------------------------------------

    public function testSubtraction01(): void
    {
        $timePatterns = [[
            "00:05:00",
            "23:50:00",
        ]];

        $this->subtractTimePatternsFromTimePattern(
            [[]],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction02(): void
    {
        $timePatterns = [[
            "01:00:00",
            "23:00:00",
        ]];

        $this->subtractTimePatternsFromTimePattern(
            [[]],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction021(): void
    {
        $timePatterns = [[
            "02:00:00",
            "22:00:00",
        ]];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "02:00:00",
                ],
                [
                    "22:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Outside ----------------------------------------------------------------------------------

    public function testSubtraction1(): void
    {
        $timePatterns = [[
            "00:50:00",
            "00:55:00",
        ]];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction11(): void
    {
        $timePatterns = [[
            "00:50:00",
            "01:00:00",
        ]];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction2(): void
    {
        $timePatterns = [[
            "23:10:00",
            "23:15:00",
        ]];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction21(): void
    {
        $timePatterns = [[
            "23:00:00",
            "23:10:00",
        ]];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Inside ----------------------------------------------------------------------------------

    public function testSubtraction3(): void
    {
        $timePatterns = [[
            "05:00:00",
            "06:00:00",
        ]];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "05:00:00",
                ],
                [
                    "06:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction31(): void
    {
        $timePatterns = [[
            "01:00:00",
            "06:00:00",
        ]];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "06:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction32(): void
    {
        $timePatterns = [[
            "12:00:00",
            "23:00:00",
        ]];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "12:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Cross ----------------------------------------------------------------------------------

    public function testSubtraction4(): void
    {
        $timePatterns = [[
            "00:30:00",
            "5:00:00",
        ]];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "05:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction41(): void
    {
        $timePatterns = [[
            "20:00:00",
            "23:30:00",
        ]];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "20:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ----------------------------------------------------------------- Four Time Patterns ----------------------------------------------------------------------------

    // ---------------------------------------------------------------------- Outside ----------------------------------------------------------------------------------

    public function testSubtraction5(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:25:00",
            ],
            [
                "00:30:00",
                "00:35:00",
            ],
            [
                "00:40:00",
                "00:45:00",
            ],
            [
                "00:50:00",
                "00:55:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction51(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:25:00",
            ],
            [
                "00:30:00",
                "00:35:00",
            ],
            [
                "00:40:00",
                "00:45:00",
            ],
            [
                "00:50:00",
                "01:00:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction52(): void
    {
        $timePatterns = [
            [
                "23:05:00",
                "23:10:00",
            ],
            [
                "23:15:00",
                "23:20:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
            [
                "23:35:00",
                "23:40:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction53(): void
    {
        $timePatterns = [
            [
                "23:00:00",
                "23:10:00",
            ],
            [
                "23:15:00",
                "23:20:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
            [
                "23:35:00",
                "23:40:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction531(): void
    {
        $timePatterns = [
            [
                "00:40:00",
                "00:45:00",
            ],
            [
                "00:50:00",
                "00:55:00",
            ],
            [
                "23:05:00",
                "23:10:00",
            ],
            [
                "23:15:00",
                "23:20:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction532(): void
    {
        $timePatterns = [
            [
                "00:40:00",
                "00:45:00",
            ],
            [
                "00:50:00",
                "01:00:00",
            ],
            [
                "23:00:00",
                "23:10:00",
            ],
            [
                "23:15:00",
                "23:20:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Inside ----------------------------------------------------------------------------------

    public function testSubtraction54(): void
    {
        $timePatterns = [
            [
                "02:00:00",
                "04:00:00",
            ],
            [
                "06:00:00",
                "08:00:00",
            ],
            [
                "10:00:00",
                "12:00:00",
            ],
            [
                "14:00:00",
                "16:00:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "02:00:00",
                ],
                [
                    "04:00:00",
                    "06:00:00",
                ],
                [
                    "08:00:00",
                    "10:00:00",
                ],
                [
                    "12:00:00",
                    "14:00:00",
                ],
                [
                    "16:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction55(): void
    {
        $timePatterns = [
            [
                "01:00:00",
                "04:00:00",
            ],
            [
                "06:00:00",
                "08:00:00",
            ],
            [
                "10:00:00",
                "12:00:00",
            ],
            [
                "14:00:00",
                "16:00:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "04:00:00",
                    "06:00:00",
                ],
                [
                    "08:00:00",
                    "10:00:00",
                ],
                [
                    "12:00:00",
                    "14:00:00",
                ],
                [
                    "16:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction56(): void
    {
        $timePatterns = [
            [
                "02:00:00",
                "04:00:00",
            ],
            [
                "06:00:00",
                "08:00:00",
            ],
            [
                "10:00:00",
                "12:00:00",
            ],
            [
                "14:00:00",
                "23:00:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "02:00:00",
                ],
                [
                    "04:00:00",
                    "06:00:00",
                ],
                [
                    "08:00:00",
                    "10:00:00",
                ],
                [
                    "12:00:00",
                    "14:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Cross -----------------------------------------------------------------------------------

    // ---------------------------------------------------------------------- First Crosses ---------------------------------------------------------------------------

    public function testSubtraction6(): void
    {
        $timePatterns = [
            [
                "00:25:00",
                "00:30:00",
            ],
            [
                "00:35:00",
                "00:40:00",
            ],
            [
                "00:45:00",
                "00:50:00",
            ],
            [
                "00:55:00",
                "01:10:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [[
                "01:10:00",
                "23:00:00",
            ]],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction61(): void
    {
        $timePatterns = [
            [
                "00:25:00",
                "00:30:00",
            ],
            [
                "00:35:00",
                "00:40:00",
            ],
            [
                "00:45:00",
                "00:50:00",
            ],
            [
                "01:00:00",
                "01:10:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [[
                "01:10:00",
                "23:00:00",
            ]],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction62(): void
    {
        $timePatterns = [
            [
                "00:25:00",
                "00:30:00",
            ],
            [
                "00:35:00",
                "00:40:00",
            ],
            [
                "00:45:00",
                "00:50:00",
            ],
            [
                "03:00:00",
                "05:00:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "03:00:00",
                ],
                [
                    "05:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction63(): void
    {
        $timePatterns = [
            [
                "00:25:00",
                "00:30:00",
            ],
            [
                "00:35:00",
                "00:40:00",
            ],
            [
                "00:45:00",
                "01:00:00",
            ],
            [
                "03:00:00",
                "05:00:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "03:00:00",
                ],
                [
                    "05:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction64(): void
    {
        $timePatterns = [
            [
                "00:25:00",
                "00:30:00",
            ],
            [
                "00:35:00",
                "00:40:00",
            ],
            [
                "00:45:00",
                "01:10:00",
            ],
            [
                "03:00:00",
                "05:00:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:10:00",
                    "03:00:00",
                ],
                [
                    "05:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction65(): void
    {
        $timePatterns = [
            [
                "00:25:00",
                "00:30:00",
            ],
            [
                "00:35:00",
                "00:40:00",
            ],
            [
                "01:00:00",
                "01:10:00",
            ],
            [
                "03:00:00",
                "05:00:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:10:00",
                    "03:00:00",
                ],
                [
                    "05:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction66(): void
    {
        $timePatterns = [
            [
                "00:25:00",
                "00:30:00",
            ],
            [
                "00:35:00",
                "00:40:00",
            ],
            [
                "04:00:00",
                "06:00:00",
            ],
            [
                "08:00:00",
                "10:00:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "04:00:00",
                ],
                [
                    "06:00:00",
                    "08:00:00",
                ],
                [
                    "10:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction67(): void
    {
        $timePatterns = [
            [
                "00:25:00",
                "00:30:00",
            ],
            [
                "00:35:00",
                "01:00:00",
            ],
            [
                "04:00:00",
                "06:00:00",
            ],
            [
                "08:00:00",
                "10:00:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "04:00:00",
                ],
                [
                    "06:00:00",
                    "08:00:00",
                ],
                [
                    "10:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction68(): void
    {
        $timePatterns = [
            [
                "00:25:00",
                "00:30:00",
            ],
            [
                "00:35:00",
                "02:00:00",
            ],
            [
                "04:00:00",
                "06:00:00",
            ],
            [
                "08:00:00",
                "10:00:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "02:00:00",
                    "04:00:00",
                ],
                [
                    "06:00:00",
                    "08:00:00",
                ],
                [
                    "10:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction69(): void
    {
        $timePatterns = [
            [
                "00:25:00",
                "00:30:00",
            ],
            [
                "01:00:00",
                "02:00:00",
            ],
            [
                "04:00:00",
                "06:00:00",
            ],
            [
                "08:00:00",
                "10:00:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "02:00:00",
                    "04:00:00",
                ],
                [
                    "06:00:00",
                    "08:00:00",
                ],
                [
                    "10:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction610(): void
    {
        $timePatterns = [
            [
                "00:25:00",
                "00:30:00",
            ],
            [
                "02:00:00",
                "04:00:00",
            ],
            [
                "06:00:00",
                "08:00:00",
            ],
            [
                "10:00:00",
                "12:00:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "02:00:00",
                ],
                [
                    "04:00:00",
                    "06:00:00",
                ],
                [
                    "08:00:00",
                    "10:00:00",
                ],
                [
                    "12:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction611(): void
    {
        $timePatterns = [
            [
                "00:25:00",
                "01:00:00",
            ],
            [
                "02:00:00",
                "04:00:00",
            ],
            [
                "06:00:00",
                "08:00:00",
            ],
            [
                "10:00:00",
                "12:00:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "02:00:00",
                ],
                [
                    "04:00:00",
                    "06:00:00",
                ],
                [
                    "08:00:00",
                    "10:00:00",
                ],
                [
                    "12:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction612(): void
    {
        $timePatterns = [
            [
                "00:25:00",
                "01:10:00",
            ],
            [
                "02:10:00",
                "04:00:00",
            ],
            [
                "06:00:00",
                "08:00:00",
            ],
            [
                "10:00:00",
                "12:00:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:10:00",
                    "02:10:00",
                ],
                [
                    "04:00:00",
                    "06:00:00",
                ],
                [
                    "08:00:00",
                    "10:00:00",
                ],
                [
                    "12:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Last Crosses ----------------------------------------------------------------------------

    public function testSubtraction613(): void
    {
        $timePatterns = [
            [
                "22:00:00",
                "23:03:00",
            ],
            [
                "23:05:00",
                "23:10:00",
            ],
            [
                "23:15:00",
                "23:20:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [[
                "01:00:00",
                "22:00:00",
            ]],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction614(): void
    {
        $timePatterns = [
            [
                "22:00:00",
                "23:00:00",
            ],
            [
                "23:05:00",
                "23:10:00",
            ],
            [
                "23:15:00",
                "23:20:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [[
                "01:00:00",
                "22:00:00",
            ]],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction615(): void
    {
        $timePatterns = [
            [
                "20:00:00",
                "22:00:00",
            ],
            [
                "23:05:00",
                "23:10:00",
            ],
            [
                "23:15:00",
                "23:20:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "20:00:00",
                ],
                [
                    "22:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction616(): void
    {
        $timePatterns = [
            [
                "20:00:00",
                "22:00:00",
            ],
            [
                "23:00:00",
                "23:10:00",
            ],
            [
                "23:15:00",
                "23:20:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "20:00:00",
                ],
                [
                    "22:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction617(): void
    {
        $timePatterns = [
            [
                "18:00:00",
                "20:00:00",
            ],
            [
                "22:00:00",
                "23:10:00",
            ],
            [
                "23:15:00",
                "23:20:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "18:00:00",
                ],
                [
                    "20:00:00",
                    "22:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction618(): void
    {
        $timePatterns = [
            [
                "18:00:00",
                "20:00:00",
            ],
            [
                "22:00:00",
                "23:00:00",
            ],
            [
                "23:15:00",
                "23:20:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "18:00:00",
                ],
                [
                    "20:00:00",
                    "22:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction619(): void
    {
        $timePatterns = [
            [
                "16:00:00",
                "18:00:00",
            ],
            [
                "20:00:00",
                "22:00:00",
            ],
            [
                "23:15:00",
                "23:20:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "16:00:00",
                ],
                [
                    "18:00:00",
                    "20:00:00",
                ],
                [
                    "22:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction620(): void
    {
        $timePatterns = [
            [
                "16:00:00",
                "18:00:00",
            ],
            [
                "20:00:00",
                "22:00:00",
            ],
            [
                "23:00:00",
                "23:20:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "16:00:00",
                ],
                [
                    "18:00:00",
                    "20:00:00",
                ],
                [
                    "22:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction621(): void
    {
        $timePatterns = [
            [
                "14:00:00",
                "16:00:00",
            ],
            [
                "18:00:00",
                "20:00:00",
            ],
            [
                "22:00:00",
                "23:20:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "14:00:00",
                ],
                [
                    "16:00:00",
                    "18:00:00",
                ],
                [
                    "20:00:00",
                    "22:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction622(): void
    {
        $timePatterns = [
            [
                "14:00:00",
                "16:00:00",
            ],
            [
                "18:00:00",
                "20:00:00",
            ],
            [
                "22:00:00",
                "23:00:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "14:00:00",
                ],
                [
                    "16:00:00",
                    "18:00:00",
                ],
                [
                    "20:00:00",
                    "22:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction623(): void
    {
        $timePatterns = [
            [
                "12:00:00",
                "14:00:00",
            ],
            [
                "16:00:00",
                "18:00:00",
            ],
            [
                "20:00:00",
                "22:00:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "12:00:00",
                ],
                [
                    "14:00:00",
                    "16:00:00",
                ],
                [
                    "18:00:00",
                    "20:00:00",
                ],
                [
                    "22:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction624(): void
    {
        $timePatterns = [
            [
                "12:00:00",
                "14:00:00",
            ],
            [
                "16:00:00",
                "18:00:00",
            ],
            [
                "20:00:00",
                "22:00:00",
            ],
            [
                "23:00:00",
                "23:30:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "12:00:00",
                ],
                [
                    "14:00:00",
                    "16:00:00",
                ],
                [
                    "18:00:00",
                    "20:00:00",
                ],
                [
                    "22:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction625(): void
    {
        $timePatterns = [
            [
                "10:00:00",
                "12:00:00",
            ],
            [
                "14:00:00",
                "16:00:00",
            ],
            [
                "18:00:00",
                "20:00:00",
            ],
            [
                "22:00:00",
                "23:30:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "10:00:00",
                ],
                [
                    "12:00:00",
                    "14:00:00",
                ],
                [
                    "16:00:00",
                    "18:00:00",
                ],
                [
                    "20:00:00",
                    "22:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Mixed Crosses ---------------------------------------------------------------------------

    public function testSubtraction626(): void
    {
        $timePatterns = [
            [
                "00:00:00",
                "00:30:00",
            ],
            [
                "00:45:00",
                "05:00:00",
            ],
            [
                "22:00:00",
                "23:30:00",
            ],
            [
                "23:45:00",
                "23:55:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "05:00:00",
                    "22:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testSubtraction627(): void
    {
        $timePatterns = [
            [
                "00:00:00",
                "05:00:00",
            ],
            [
                "06:00:00",
                "08:00:00",
            ],
            [
                "18:00:00",
                "20:00:00",
            ],
            [
                "22:00:00",
                "23:55:00",
            ],
        ];

        $this->subtractTimePatternsFromTimePattern(
            [
                [
                    "05:00:00",
                    "06:00:00",
                ],
                [
                    "08:00:00",
                    "18:00:00",
                ],
                [
                    "20:00:00",
                    "22:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ----------------------------------------------------------------- INTERSECTIONS --------------------------------------------------------------------------------

    // ----------------------------------------------------------------- Zero Time Pattern -----------------------------------------------------------------------------

    public function testIntersection0(): void
    {
        $timePatterns = [];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [[]],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ----------------------------------------------------------------- One Time Pattern ------------------------------------------------------------------------------

    public function testIntersection01(): void
    {
        $timePatterns = [[
            "00:30:00",
            "23:30:00",
        ]];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [[
                "01:00:00",
                "23:00:00",
            ]],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection02(): void
    {
        $timePatterns = [[
            "01:00:00",
            "23:00:00",
        ]];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [[
                "01:00:00",
                "23:00:00",
            ]],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Outside ---------------------------------------------------------------------------------

    public function testIntersection1(): void
    {
        $timePatterns = [[
            "00:30:00",
            "00:50:00",
        ]];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection11(): void
    {
        $timePatterns = [[
            "00:30:00",
            "01:00:00",
        ]];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection12(): void
    {
        $timePatterns = [[
            "23:30:00",
            "23:50:00",
        ]];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection13(): void
    {
        $timePatterns = [[
            "23:00:00",
            "23:50:00",
        ]];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Inside ----------------------------------------------------------------------------------

    public function testIntersection2(): void
    {
        $timePatterns = [[
            "02:00:00",
            "04:00:00",
        ]];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "02:00:00",
                    "04:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection21(): void
    {
        $timePatterns = [[
            "01:00:00",
            "04:00:00",
        ]];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "04:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection22(): void
    {
        $timePatterns = [
            [
                "02:00:00",
                "23:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "02:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Cross -----------------------------------------------------------------------------------

    public function testIntersection3(): void
    {
        $timePatterns = [[
            "00:30:00",
            "2:00:00",
        ]];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "02:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection31(): void
    {
        $timePatterns = [[
            "22:00:00",
            "23:20:00",
        ]];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "22:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ----------------------------------------------------------------- Four Time Patterns ----------------------------------------------------------------------------

    // ---------------------------------------------------------------------- Outside ---------------------------------------------------------------------------------

    public function testIntersection4(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:25:00",
            ],
            [
                "00:30:00",
                "00:35:00",
            ],
            [
                "00:40:00",
                "00:45:00",
            ],
            [
                "00:50:00",
                "00:55:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection41(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:25:00",
            ],
            [
                "00:30:00",
                "00:35:00",
            ],
            [
                "00:40:00",
                "00:45:00",
            ],
            [
                "00:50:00",
                "01:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection42(): void
    {
        $timePatterns = [
            [
                "23:20:00",
                "23:25:00",
            ],
            [
                "23:30:00",
                "23:35:00",
            ],
            [
                "23:40:00",
                "23:45:00",
            ],
            [
                "23:50:00",
                "23:55:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection43(): void
    {
        $timePatterns = [
            [
                "23:00:00",
                "23:25:00",
            ],
            [
                "23:30:00",
                "23:35:00",
            ],
            [
                "23:40:00",
                "23:45:00",
            ],
            [
                "23:50:00",
                "23:55:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection44(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:25:00",
            ],
            [
                "00:30:00",
                "00:35:00",
            ],
            [
                "23:40:00",
                "23:45:00",
            ],
            [
                "23:50:00",
                "23:55:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection45(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:25:00",
            ],
            [
                "00:30:00",
                "01:00:00",
            ],
            [
                "23:00:00",
                "23:45:00",
            ],
            [
                "23:50:00",
                "23:55:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Inside ----------------------------------------------------------------------------------

    public function testIntersection5(): void
    {
        $timePatterns = [
            [
                "02:00:00",
                "04:00:00",
            ],
            [
                "06:00:00",
                "08:00:00",
            ],
            [
                "10:00:00",
                "12:00:00",
            ],
            [
                "14:00:00",
                "16:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            $timePatterns,
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection51(): void
    {
        $timePatterns = [
            [
                "01:00:00",
                "04:00:00",
            ],
            [
                "06:00:00",
                "08:00:00",
            ],
            [
                "10:00:00",
                "12:00:00",
            ],
            [
                "14:00:00",
                "16:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            $timePatterns,
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection52(): void
    {
        $timePatterns = [
            [
                "02:00:00",
                "04:00:00",
            ],
            [
                "06:00:00",
                "08:00:00",
            ],
            [
                "10:00:00",
                "12:00:00",
            ],
            [
                "14:00:00",
                "23:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            $timePatterns,
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Cross -----------------------------------------------------------------------------------

    // ---------------------------------------------------------------------- First Crosses ---------------------------------------------------------------------------

    public function testIntersection6(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:25:00",
            ],
            [
                "00:30:00",
                "00:35:00",
            ],
            [
                "00:40:00",
                "00:45:00",
            ],
            [
                "00:50:00",
                "02:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "02:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection61(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:25:00",
            ],
            [
                "00:30:00",
                "00:35:00",
            ],
            [
                "00:40:00",
                "00:45:00",
            ],
            [
                "01:00:00",
                "02:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "02:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection62(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:25:00",
            ],
            [
                "00:30:00",
                "00:35:00",
            ],
            [
                "00:40:00",
                "00:45:00",
            ],
            [
                "02:00:00",
                "04:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "02:00:00",
                    "04:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection63(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:25:00",
            ],
            [
                "00:30:00",
                "00:35:00",
            ],
            [
                "00:40:00",
                "01:00:00",
            ],
            [
                "02:00:00",
                "04:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "02:00:00",
                    "04:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection64(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:25:00",
            ],
            [
                "00:30:00",
                "00:35:00",
            ],
            [
                "00:40:00",
                "02:00:00",
            ],
            [
                "04:00:00",
                "06:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "02:00:00",
                ],
                [
                    "04:00:00",
                    "06:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection65(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:25:00",
            ],
            [
                "00:30:00",
                "00:35:00",
            ],
            [
                "01:00:00",
                "02:00:00",
            ],
            [
                "04:00:00",
                "06:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "02:00:00",
                ],
                [
                    "04:00:00",
                    "06:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection66(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:25:00",
            ],
            [
                "00:30:00",
                "00:35:00",
            ],
            [
                "02:00:00",
                "04:00:00",
            ],
            [
                "06:00:00",
                "08:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "02:00:00",
                    "04:00:00",
                ],
                [
                    "06:00:00",
                    "08:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection67(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:25:00",
            ],
            [
                "00:30:00",
                "01:00:00",
            ],
            [
                "02:00:00",
                "04:00:00",
            ],
            [
                "06:00:00",
                "08:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "02:00:00",
                    "04:00:00",
                ],
                [
                    "06:00:00",
                    "08:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection68(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:25:00",
            ],
            [
                "00:30:00",
                "02:00:00",
            ],
            [
                "04:00:00",
                "06:00:00",
            ],
            [
                "08:00:00",
                "10:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "02:00:00",
                ],
                [
                    "04:00:00",
                    "06:00:00",
                ],
                [
                    "08:00:00",
                    "10:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection69(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:25:00",
            ],
            [
                "01:00:00",
                "02:00:00",
            ],
            [
                "04:00:00",
                "06:00:00",
            ],
            [
                "08:00:00",
                "10:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "02:00:00",
                ],
                [
                    "04:00:00",
                    "06:00:00",
                ],
                [
                    "08:00:00",
                    "10:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection610(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:25:00",
            ],
            [
                "02:00:00",
                "04:00:00",
            ],
            [
                "06:00:00",
                "08:00:00",
            ],
            [
                "10:00:00",
                "12:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "02:00:00",
                    "04:00:00",
                ],
                [
                    "06:00:00",
                    "08:00:00",
                ],
                [
                    "10:00:00",
                    "12:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection611(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "01:00:00",
            ],
            [
                "02:00:00",
                "04:00:00",
            ],
            [
                "06:00:00",
                "08:00:00",
            ],
            [
                "10:00:00",
                "12:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "02:00:00",
                    "04:00:00",
                ],
                [
                    "06:00:00",
                    "08:00:00",
                ],
                [
                    "10:00:00",
                    "12:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection612(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "02:00:00",
            ],
            [
                "04:00:00",
                "06:00:00",
            ],
            [
                "08:00:00",
                "10:00:00",
            ],
            [
                "12:00:00",
                "14:00:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "02:00:00",
                ],
                [
                    "04:00:00",
                    "06:00:00",
                ],
                [
                    "08:00:00",
                    "10:00:00",
                ],
                [
                    "12:00:00",
                    "14:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Last Crosses ----------------------------------------------------------------------------

    public function testIntersection613(): void
    {
        $timePatterns = [
            [
                "22:00:00",
                "23:10:00",
            ],
            [
                "23:15:00",
                "23:20:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
            [
                "23:35:00",
                "23:40:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "22:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection614(): void
    {
        $timePatterns = [
            [
                "22:00:00",
                "23:00:00",
            ],
            [
                "23:15:00",
                "23:20:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
            [
                "23:35:00",
                "23:40:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "22:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection615(): void
    {
        $timePatterns = [
            [
                "20:00:00",
                "22:00:00",
            ],
            [
                "23:15:00",
                "23:20:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
            [
                "23:35:00",
                "23:40:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "20:00:00",
                    "22:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection616(): void
    {
        $timePatterns = [
            [
                "20:00:00",
                "22:00:00",
            ],
            [
                "23:00:00",
                "23:20:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
            [
                "23:35:00",
                "23:40:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "20:00:00",
                    "22:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection617(): void
    {
        $timePatterns = [
            [
                "18:00:00",
                "20:00:00",
            ],
            [
                "22:00:00",
                "23:20:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
            [
                "23:35:00",
                "23:40:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "18:00:00",
                    "20:00:00",
                ],
                [
                    "22:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection618(): void
    {
        $timePatterns = [
            [
                "18:00:00",
                "20:00:00",
            ],
            [
                "22:00:00",
                "23:00:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
            [
                "23:35:00",
                "23:40:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "18:00:00",
                    "20:00:00",
                ],
                [
                    "22:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection619(): void
    {
        $timePatterns = [
            [
                "16:00:00",
                "18:00:00",
            ],
            [
                "20:00:00",
                "22:00:00",
            ],
            [
                "23:25:00",
                "23:30:00",
            ],
            [
                "23:35:00",
                "23:40:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "16:00:00",
                    "18:00:00",
                ],
                [
                    "20:00:00",
                    "22:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection620(): void
    {
        $timePatterns = [
            [
                "16:00:00",
                "18:00:00",
            ],
            [
                "20:00:00",
                "22:00:00",
            ],
            [
                "23:00:00",
                "23:30:00",
            ],
            [
                "23:35:00",
                "23:40:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "16:00:00",
                    "18:00:00",
                ],
                [
                    "20:00:00",
                    "22:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection621(): void
    {
        $timePatterns = [
            [
                "14:00:00",
                "16:00:00",
            ],
            [
                "18:00:00",
                "20:00:00",
            ],
            [
                "22:00:00",
                "23:30:00",
            ],
            [
                "23:35:00",
                "23:40:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "14:00:00",
                    "16:00:00",
                ],
                [
                    "18:00:00",
                    "20:00:00",
                ],
                [
                    "22:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection622(): void
    {
        $timePatterns = [
            [
                "14:00:00",
                "16:00:00",
            ],
            [
                "18:00:00",
                "20:00:00",
            ],
            [
                "22:00:00",
                "23:00:00",
            ],
            [
                "23:35:00",
                "23:40:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "14:00:00",
                    "16:00:00",
                ],
                [
                    "18:00:00",
                    "20:00:00",
                ],
                [
                    "22:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection623(): void
    {
        $timePatterns = [
            [
                "12:00:00",
                "14:00:00",
            ],
            [
                "16:00:00",
                "18:00:00",
            ],
            [
                "20:00:00",
                "22:00:00",
            ],
            [
                "23:35:00",
                "23:40:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "12:00:00",
                    "14:00:00",
                ],
                [
                    "16:00:00",
                    "18:00:00",
                ],
                [
                    "20:00:00",
                    "22:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection624(): void
    {
        $timePatterns = [
            [
                "12:00:00",
                "14:00:00",
            ],
            [
                "16:00:00",
                "18:00:00",
            ],
            [
                "20:00:00",
                "22:00:00",
            ],
            [
                "23:00:00",
                "23:40:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "12:00:00",
                    "14:00:00",
                ],
                [
                    "16:00:00",
                    "18:00:00",
                ],
                [
                    "20:00:00",
                    "22:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection625(): void
    {
        $timePatterns = [
            [
                "10:00:00",
                "12:00:00",
            ],
            [
                "14:00:00",
                "16:00:00",
            ],
            [
                "18:00:00",
                "20:00:00",
            ],
            [
                "22:00:00",
                "23:40:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "10:00:00",
                    "12:00:00",
                ],
                [
                    "14:00:00",
                    "16:00:00",
                ],
                [
                    "18:00:00",
                    "20:00:00",
                ],
                [
                    "22:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ---------------------------------------------------------------------- Mixed Crosses ---------------------------------------------------------------------------

    public function testIntersection626(): void
    {
        $timePatterns = [
            [
                "00:20:00",
                "00:30:00",
            ],
            [
                "00:40:00",
                "02:00:00",
            ],
            [
                "22:00:00",
                "23:20:00",
            ],
            [
                "23:30:00",
                "23:40:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "02:00:00",
                ],
                [
                    "22:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    public function testIntersection627(): void
    {
        $timePatterns = [
            [
                "00:40:00",
                "02:00:00",
            ],
            [
                "04:00:00",
                "06:00:00",
            ],
            [
                "08:00:00",
                "10:00:00",
            ],
            [
                "22:00:00",
                "23:20:00",
            ],
        ];

        $this->findInterseciontsTimePatternsFromTimePattern(
            [
                [
                    "01:00:00",
                    "02:00:00",
                ],
                [
                    "04:00:00",
                    "06:00:00",
                ],
                [
                    "08:00:00",
                    "10:00:00",
                ],
                [
                    "22:00:00",
                    "23:00:00",
                ],
            ],
            "01:00:00",
            "23:00:00",
            (1 * 60),
            $timePatterns,
            [$this, 'startGetter'],
            [$this, 'endGetter'],
        );
    }

    // ----------------------------------------------------------------------------------------------------------------------------------------------------------------

    public function startGetter(array $value): string
    {
        return $value[0];
    }

    public function endGetter(array $value): string
    {
        return $value[1];
    }

    /**
     * @param array $expected
     * @param integer $startTS
     * @param integer $endTS
     * @param integer $neededTime
     * @param array|\ArrayAccess|\Countable $timePatterns
     * @param array|\Closure $startGetter
     * @param array|\Closure $endGetter
     * @return \Generator<int, int[]>
     */
    private function subtractTimePatternsFromTimePattern(array $expected, string $start, string $end, int $neededTime, array|\ArrayAccess|\Countable $timePatterns, array|\Closure $startGetter, array|\Closure $endGetter): void
    {
        $count = 0;
        foreach ($this->timePatternsManager->subtractTimePatternsFromTimePattern(
            $start,
            $end,
            $neededTime,
            $timePatterns,
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
     * @param array|\ArrayAccess|\Countable $timePatterns
     * @param array|\Closure $startGetter
     * @param array|\Closure $endGetter
     * @return \Generator<int, int[]>
     */
    private function findInterseciontsTimePatternsFromTimePattern(array $expected, string $start, string $end, int $neededTime, array|\ArrayAccess|\Countable $timePatterns, array|\Closure $startGetter, array|\Closure $endGetter): void
    {
        $count = 0;
        foreach ($this->timePatternsManager->findIntersectionsOfTimePatternsFromTimePattern(
            $start,
            $end,
            $neededTime,
            $timePatterns,
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
