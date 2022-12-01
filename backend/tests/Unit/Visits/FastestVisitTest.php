<?php

namespace Tests\Unit\Visits;

use App\DataStructures\Time\DSDateTimePeriods;
use App\DataStructures\Time\DSDownTimes;
use App\DataStructures\Time\DSWeeklyTimePatterns;
use App\DataStructures\Visit\DSVisits;
use App\DataStructures\Visit\Laser\DSLaserVisit;
use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;
use Illuminate\Support\Str;
use App\PoliciesLogic\Exceptions\Visit\NeededTimeOutOfRange;
use App\PoliciesLogic\Visit\FastestVisit;
use Tests\Unit\Visits\Util\TimeFactory;

class FastestVisitTest extends TestCase
{
    use TimeFactory;

    private Generator $faker;

    private \DateTime $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->now = new \DateTime();
    }

    public function testFindVisits(): void
    {
        $futureVisits = new DSVisits();

        $folderAddress = __DIR__ . "/FastestVisitTestLogs";
        $safety = 0;
        while (count($futureVisits) < 500 && $safety < 50000) {
            $R = $this->findVisit($futureVisits, $folderAddress);
            $safety++;
        }

        $this->assertCount(500, $futureVisits);

        $totalLogs = "";
        $content = "";
        foreach (scandir($folderAddress) as $key => $value) {
            if (in_array($value, [".", ".."]) || !Str::endsWith($value, ".log")) {
                continue;
            }

            $content = file_get_contents($folderAddress . '/' . $value);
            $totalLogs .= $content;
        }
        $totalLogs .= json_encode($futureVisits->toArray(), JSON_PRETTY_PRINT);
        file_put_contents($folderAddress . '/final_log_result.log', $totalLogs);
    }

    public function findVisit(
        DSVisits &$futureVisits,
        string $folderAddress,
        null|int $consumingTime = null,
        null|DSWeeklyTimePatterns $workSchedule = null,
        null|DSDownTimes $dsDownTimes = null,
        null|DSDateTimePeriods $dsDateTimePeriods = null
    ): array {
        $id = count($futureVisits) === 0 ? 1 : count($futureVisits);
        $consumingTime = $consumingTime ?: $this->faker->numberBetween(600, 7200);

        $workSchedule = $workSchedule ?: $this->buildWrokSchedule();
        $dsDownTimes = $dsDownTimes ?: $this->buildRandomDSDownTimes($consumingTime, 5, 3 * 60 * 60);

        $workScheduleArray = $workSchedule->toArray();
        $dsDownTimesArray = $dsDownTimes->toArray();

        $fileAddress = $folderAddress . "/$id.log";
        file_put_contents($fileAddress, json_encode(
            [
                "dsDownTimesArray" => $dsDownTimesArray,
                "workScheduleArray" => $workScheduleArray,
            ],
            JSON_PRETTY_PRINT
        ));

        $timestamp = $message = null;
        try {
            $timestamp = (new FastestVisit(
                new \DateTime(),
                $consumingTime,
                $futureVisits,
                $workSchedule,
                $dsDownTimes,
            ))->findVisit();
        } catch (NeededTimeOutOfRange $th) {
            $message = "NeededTimeOutOfRange Exception has been thrown.";
            return ["timestamp" => $timestamp, "message" => $message];
        }

        if ($timestamp !== null) {
            $futureVisits->setSort('Natural');
            $futureVisits[] = $futureVisit = new DSLaserVisit($id, $timestamp, $consumingTime, new \DateTime(), new \DateTime());
            $futureVisits->setSort('ASC');
            $message = "\n\n\$timestamp: " . (new \DateTime)->setTimestamp($timestamp)->format("Y-m-d H:i:s l") . "\n\n" . json_encode(["futureVisit" => $futureVisit->toArray()], JSON_PRETTY_PRINT);
        }

        if (isset($message)) {
            $content = file_get_contents($fileAddress);
            $content .= $message;
            file_put_contents($fileAddress, $content);
        }

        return ["timestamp" => $timestamp, "message" => $message];
    }
}
