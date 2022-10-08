<?php

namespace Tests\Feature\Visits;

use App\DataStructures\Time\DSDateTimePeriod;
use App\DataStructures\Time\DSDateTimePeriods;
use App\DataStructures\Time\DSDownTime;
use App\DataStructures\Time\DSDownTimes;
use App\DataStructures\Time\DSTimePattern;
use App\DataStructures\Time\DSTimePatterns;
use App\DataStructures\Time\DSWeeklyTimePatterns;
use App\DataStructures\Visit\DSVisits;
use App\DataStructures\Visit\Laser\DSLaserVisit;
use App\PoliciesLogic\Visit\CustomVisit;
use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;
use Illuminate\Support\Str;
use App\PoliciesLogic\Exceptions\Visit\NeededTimeOutOfRange;

class CustomVisitTest extends TestCase
{
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

        $folderAddress = __DIR__ . "/CustomVisitTestLogs";
        $safety = 0;
        while (count($futureVisits) < 500 && $safety < 50000) {
            $R = $this->findVisit($futureVisits, $folderAddress);
            $safety++;
        }

        // fwrite(STDOUT, "    \033[33mR => " . json_encode($R, JSON_PRETTY_PRINT) . "\033\n");

        $totalLogs = "";
        $content = "";
        foreach (scandir($folderAddress) as $key => $value) {
            if (in_array($value, [".", ".."]) || !Str::endsWith($value, ".log")) {
                continue;
            }

            $content = file_get_contents($folderAddress . '/' . $value);
            $totalLogs .= $content;
        }
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
        $dsDateTimePeriods = $dsDateTimePeriods ?: $this->buildRandomDSDateTimePeriods($consumingTime);

        $workScheduleArray = $workSchedule->toArray();
        $dsDownTimesArray = $dsDownTimes->toArray();
        $dsDateTimePeriodsArray = $dsDateTimePeriods->toArray();

        $fileAddress = $folderAddress . "/$id.log";
        file_put_contents($fileAddress, json_encode(
            [
                "dsDateTimePeriodsArray" => $dsDateTimePeriodsArray,
                "dsDownTimesArray" => $dsDownTimesArray,
                "workScheduleArray" => $workScheduleArray,
            ],
            JSON_PRETTY_PRINT
        ));

        $timestamp = $message = null;
        try {
            $timestamp = (new CustomVisit(
                $dsDateTimePeriods,
                $consumingTime,
                $futureVisits,
                $workSchedule,
                $dsDownTimes
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

    private function buildRandomDSDownTimes(int $consumingTime, int $maximumTimePeriodsCount = 3, int $maximumTimePeriodsDuration = 0, int $maximumTimeGapeDuration = 2 * 60 * 60): DSDownTimes
    {
        $dsDownTimes = new DSDownTimes;
        $dsDateTimePeriods = $this->buildRandomDSDateTimePeriods($consumingTime, $maximumTimePeriodsCount, $maximumTimePeriodsDuration, $maximumTimeGapeDuration);

        /** @var DSDateTimePeriod $dsDateTimePeriod */
        foreach ($dsDateTimePeriods as $dsDateTimePeriod) {
            $dsDownTimes[] = new DSDownTime($dsDateTimePeriod->getStart(), $dsDateTimePeriod->getEnd(), $this->faker->name());
        }

        return $dsDownTimes;
    }

    private function buildRandomDSDateTimePeriods(int $consumingTime, int $maximumTimePeriodsCount = 3, int $maximumTimePeriodsDuration = 0, int $maximumTimeGapeDuration = 2 * 60 * 60): DSDateTimePeriods
    {
        // fwrite(STDOUT, "\033[33mconsumingTime => " . $consumingTime . "\033\n");
        // fwrite(STDOUT, "\033[33mmaximumTimePeriodsDuration => " . $maximumTimePeriodsDuration . "\033\n");

        $pointer = (new \DateTime)->setTimestamp($this->now->getTimestamp());
        // fwrite(STDOUT, "\033[33mpointer => " . $pointer->format("Y-m-d H:i:s") . "\033\n");

        $dsDateTimePeriods = new DSDateTimePeriods;

        // ||||||   [----------------]  [----------------]  [----------------]  ||||||
        $pointer->modify("+" . $this->faker->numberBetween(0, $maximumTimeGapeDuration) . " seconds");

        $totalTime = (60 * 60 * 24 * 30) + (new \DateTime($pointer->format("Y-m-d") . " 00:00:00"))->modify("+1 day")->getTimestamp() - $pointer->getTimestamp();

        $timePeriodsCount = $this->faker->numberBetween(1, $maximumTimePeriodsCount);
        // fwrite(STDOUT, "\033[33mtimePeriodsCount => " . $timePeriodsCount . "\033\n");

        $totalTime -= $timePeriodsCount * 2 * 60 * 60; // Subtracting total time from empty time gapes between time periods
        // fwrite(STDOUT, "\033[33mtotalTime => " . $totalTime . "\033\n");

        $eachTime = intval($totalTime / $timePeriodsCount);
        // fwrite(STDOUT, "\033[33meachTime => " . $eachTime . "\033\n");
        $eachTime = ($maximumTimePeriodsDuration !== 0 && $eachTime > $maximumTimePeriodsDuration) ? $maximumTimePeriodsDuration : $eachTime; // Each time period"s maximum duration
        // fwrite(STDOUT, "\033[33meachTime => " . $eachTime . "\033\n");

        for ($i = 0; $i < $timePeriodsCount; $i++) {
            $safety = 0;
            do {
                $timePeriodDuration = $this->faker->numberBetween(1, $eachTime);
                $safety++;
            } while ($timePeriodDuration < $consumingTime && $safety < 500);
            if ($timePeriodDuration < $consumingTime && $safety >= 500) {
                $timePeriodDuration = $eachTime;
            }
            // fwrite(STDOUT, "\033[33mtimePeriodDuration => " . $timePeriodDuration . "\033\n");

            $start = (new \DateTime())->setTimestamp($pointer->getTimestamp());
            // fwrite(STDOUT, "\033[33mstart => " . $start->format("Y-m-d H:i:s") . "\033\n");

            $pointer->modify("+$timePeriodDuration seconds"); // Add the new time period duration

            $end = (new \DateTime())->setTimestamp($pointer->getTimestamp());
            // fwrite(STDOUT, "\033[33mend => " . $end->format("Y-m-d H:i:s") . "\033\n");

            $pointer->modify("+" . $this->faker->numberBetween(1, $maximumTimeGapeDuration) . " seconds"); // Add the new time gape
            // fwrite(STDOUT, "\033[33mpointer => " . $pointer->format("Y-m-d H:i:s") . "\033\n");

            $dsDateTimePeriods[] = new DSDateTimePeriod($start, $end);
        }

        // fwrite(STDOUT, "\n\n\n");
        return $dsDateTimePeriods;
    }

    private function buildWrokSchedule(): DSWeeklyTimePatterns
    {
        $workSchedule = new DSWeeklyTimePatterns("Monday");

        $workSchedule["Monday"] = new DSTimePatterns();
        $workSchedule["Monday"][] = new DSTimePattern("08:00:00", "16:00:00");
        $workSchedule["Monday"][] = new DSTimePattern("18:00:00", "23:00:00");
        $workSchedule["Tuesday"] = new DSTimePatterns();
        $workSchedule["Tuesday"][] = new DSTimePattern("08:00:00", "16:00:00");
        $workSchedule["Tuesday"][] = new DSTimePattern("18:00:00", "23:00:00");
        $workSchedule["Wednesday"] = new DSTimePatterns();
        $workSchedule["Wednesday"][] = new DSTimePattern("08:00:00", "16:00:00");
        $workSchedule["Wednesday"][] = new DSTimePattern("18:00:00", "23:00:00");
        $workSchedule["Thursday"] = new DSTimePatterns();
        $workSchedule["Thursday"][] = new DSTimePattern("08:00:00", "16:00:00");
        $workSchedule["Thursday"][] = new DSTimePattern("18:00:00", "23:00:00");
        $workSchedule["Friday"] = new DSTimePatterns();
        $workSchedule["Friday"][] = new DSTimePattern("08:00:00", "16:00:00");
        $workSchedule["Friday"][] = new DSTimePattern("18:00:00", "23:00:00");
        $workSchedule["Saturday"] = new DSTimePatterns();
        $workSchedule["Saturday"][] = new DSTimePattern("08:00:00", "16:00:00");
        $workSchedule["Saturday"][] = new DSTimePattern("18:00:00", "23:00:00");
        $workSchedule["Sunday"] = new DSTimePatterns();
        $workSchedule["Sunday"][] = new DSTimePattern("08:00:00", "16:00:00");
        $workSchedule["Sunday"][] = new DSTimePattern("18:00:00", "23:00:00");

        return $workSchedule;
    }
}
