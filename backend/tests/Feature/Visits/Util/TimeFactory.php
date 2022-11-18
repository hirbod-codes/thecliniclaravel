<?php

namespace Tests\Feature\Visits\Util;

use App\DataStructures\Time\DSDateTimePeriod;
use App\DataStructures\Time\DSDateTimePeriods;
use App\DataStructures\Time\DSDownTime;
use App\DataStructures\Time\DSDownTimes;
use App\DataStructures\Time\DSTimePattern;
use App\DataStructures\Time\DSTimePatterns;
use App\DataStructures\Time\DSWeeklyTimePatterns;

trait TimeFactory
{

    private function buildRandomDSDownTimes(int $consumingTime, int $maximumTimePeriodsCount = 3, int $maximumTimePeriodsDuration = 0, int $maximumTimeGapeDuration = 2 * 60 * 60): DSDownTimes
    {
        $dsDownTimes = new DSDownTimes;
        $dsDateTimePeriods = $this->buildRandomDSDateTimePeriods($consumingTime, $maximumTimePeriodsCount, $maximumTimePeriodsDuration, $maximumTimeGapeDuration);

        /** @var DSDateTimePeriod $dsDateTimePeriod */
        foreach ($dsDateTimePeriods as $dsDateTimePeriod) {
            $dsDownTimes[] = new DSDownTime($dsDateTimePeriod->getStart(), $dsDateTimePeriod->getEnd(), $this->faker->unique()->name());
        }

        return $dsDownTimes;
    }

    private function buildRandomDSDateTimePeriods(int $consumingTime, int $maximumTimePeriodsCount = 3, int $maximumTimePeriodsDuration = 0, int $maximumTimeGapeDuration = 2 * 60 * 60): DSDateTimePeriods
    {

        $pointer = (new \DateTime)->setTimestamp($this->now->getTimestamp());

        $dsDateTimePeriods = new DSDateTimePeriods;

        // ||||||   [----------------]  [----------------]  [----------------]  ||||||
        $pointer->modify("+" . $this->faker->numberBetween(0, $maximumTimeGapeDuration) . " seconds");

        $totalTime = (60 * 60 * 24 * 30) + (new \DateTime($pointer->format("Y-m-d") . " 00:00:00"))->modify("+1 day")->getTimestamp() - $pointer->getTimestamp();

        $timePeriodsCount = $this->faker->numberBetween(1, $maximumTimePeriodsCount);

        $totalTime -= $timePeriodsCount * 2 * 60 * 60; // Subtracting total time from empty time gapes between time periods

        $eachTime = intval($totalTime / $timePeriodsCount);
        $eachTime = ($maximumTimePeriodsDuration !== 0 && $eachTime > $maximumTimePeriodsDuration) ? $maximumTimePeriodsDuration : $eachTime; // Each time period"s maximum duration

        for ($i = 0; $i < $timePeriodsCount; $i++) {
            $safety = 0;
            do {
                $timePeriodDuration = $this->faker->numberBetween(1, $eachTime);
                $safety++;
            } while ($timePeriodDuration < $consumingTime && $safety < 500);
            if ($timePeriodDuration < $consumingTime && $safety >= 500) {
                $timePeriodDuration = $eachTime;
            }

            $start = (new \DateTime())->setTimestamp($pointer->getTimestamp());

            $pointer->modify("+$timePeriodDuration seconds"); // Add the new time period duration

            $end = (new \DateTime())->setTimestamp($pointer->getTimestamp());

            $pointer->modify("+" . $this->faker->numberBetween(1, $maximumTimeGapeDuration) . " seconds"); // Add the new time gape

            $dsDateTimePeriods[] = new DSDateTimePeriod($start, $end);
        }

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

    private function buildRandomDSWeeklyTimePatterns(int $maximumTimeGapeDuration = 2 * 60 * 60, int $maximumTimePatterns = 3, null|int $maximumWeekDays = null, string $startingWeekDay = "Monday"): DSWeeklyTimePatterns
    {
        $dsWeeklyTimePatterns = new DSWeeklyTimePatterns($startingWeekDay);

        $maximumTimePatternDuration = intval(((24 * 60 * 60) - (($maximumTimePatterns + 1) * $maximumTimeGapeDuration)) / $maximumTimePatterns);

        $weekDays = $this->faker->randomElements(DSWeeklyTimePatterns::$weekDays, $maximumWeekDays ?: $this->faker->numberBetween(1, 7));

        for ($i = 0; $i < count($weekDays); $i++) {
            $pointer = new \DateTime("00:00:00");
            $pointer->modify("+" . $this->faker->numberBetween(1, $maximumTimeGapeDuration) . " seconds");

            $dsTimePatterns = new DSTimePatterns;

            for ($j = 0; $j < $maximumTimePatterns; $j++) {
                $start = $pointer->format("H:i:s");
                $pointer->modify("+" . $this->faker->numberBetween(1, $maximumTimePatternDuration) . " seconds");
                $end = $pointer->format("H:i:s");

                $dsTimePatterns[] = new DSTimePattern($start, $end);

                $pointer->modify("+" . $this->faker->numberBetween(1, $maximumTimeGapeDuration) . " seconds");
            }

            $dsWeeklyTimePatterns[$weekDays[$i]] = $dsTimePatterns;
        }

        return $dsWeeklyTimePatterns;
    }
}
