<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\DataStructures\Time\DSTimePattern;
use App\DataStructures\Time\DSTimePatterns;
use App\DataStructures\Time\DSWeeklyTimePatterns;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class WorkScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [];
    }

    public function generateWorkSchedule(): DSWeeklyTimePatterns
    {
        $time = new \DateTime;
        $dsWorkSchedule = new DSWeeklyTimePatterns('Monday');

        /** @var string $weekDay */
        foreach (DSWeeklyTimePatterns::$weekDays as $weekDay) {
            $this->moveToWeekDay($time, $weekDay);
            $dsDateTimePeriods = new DSTimePatterns;

            $dsDateTimePeriods[] = new DSTimePattern('04:30:00', '11:30:00');

            $dsDateTimePeriods[] = new DSTimePattern('12:20:00', '19:30:00');

            $dsWorkSchedule[$weekDay] = $dsDateTimePeriods;
        }
        return $dsWorkSchedule;
    }

    public function moveToWeekDay(\DateTime &$time, string $weekDay): void
    {
        if (!in_array($weekDay, DSWeeklyTimePatterns::$weekDays)) {
            throw new \InvalidArgumentException('Incorrect value for weekDay variable.');
        }

        while ($time->format('l') !== $weekDay) {
            $time->modify('+1 day');
        }
    }
}
