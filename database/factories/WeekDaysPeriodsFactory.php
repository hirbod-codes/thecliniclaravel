<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriods;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriod;
use TheClinicDataStructures\DataStructures\Time\DSWeekDaysPeriods;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class WeekDaysPeriodsFactory extends Factory
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

    public function generateDSWeekDaysPeriods(): DSWeekDaysPeriods
    {
        $time = new \DateTime;
        $dsWorkSchedule = new DSWeekDaysPeriods('Monday');

        /** @var string $weekDay */
        foreach (DSWeekDaysPeriods::$weekDays as $weekDay) {
            $this->moveToWeekDay($time, $weekDay);
            $dsDateTimePeriods = new DSDateTimePeriods;

            $dsDateTimePeriods[] = new DSDateTimePeriod(
                (new \DateTime($time->format('Y-m-d')))->setTime(8, 0),
                (new \DateTime($time->format('Y-m-d')))->setTime(15, 0)
            );

            $dsDateTimePeriods[] = new DSDateTimePeriod(
                (new \DateTime($time->format('Y-m-d')))->setTime(16, 0),
                (new \DateTime($time->format('Y-m-d')))->setTime(23, 0)
            );

            $dsWorkSchedule[$weekDay] = $dsDateTimePeriods;
        }
        return $dsWorkSchedule;
    }

    public function moveToWeekDay(\DateTime &$time, string $weekDay): void
    {
        if (!in_array($weekDay, DSWeekDaysPeriods::$weekDays)) {
            throw new \InvalidArgumentException('Incorrect value for weekDay variable.');
        }

        while ($time->format('l') !== $weekDay) {
            $time->modify('+1 day');
        }
    }
}
