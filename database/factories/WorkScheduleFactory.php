<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriods;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriod;
use TheClinicDataStructures\DataStructures\Time\DSWorkSchedule;

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

    public function generateWorkSchedule(): DSWorkSchedule
    {
        $dsWorkSchedule = new DSWorkSchedule('Monday');

        /** @var string $weekDay */
        foreach (DSWorkSchedule::$weekDays as $weekDay) {
            $dsTimePeriods = new DSDateTimePeriods;

            $previous = $this->findDateTimeInWeekDay($weekDay);
            for ($i = 0; $i < 3; $i++) {
                $dsTimePeriods[] = new DSDateTimePeriod(...($period = $this->makeDateTimePeriod($previous, '+5 minutes', '+5 hours')));
                $previous = $period[1];
            }

            $dsWorkSchedule[$weekDay] = $dsTimePeriods;
        }
        return $dsWorkSchedule;
    }

    public function findDateTimeInWeekDay(string $weekDay): \DateTime
    {
        if (($now = (new \DateTime()))->format('l') === $weekDay) {
            return $now;
        }

        while ($now->format('l') !== $weekDay) {
            $now->modify('+1 day');
        }
        return $now;
    }

    /**
     * @param \DateTime $previous
     * @param string $firstModify
     * @param string $secondModify
     * @return array [$startingDateTime, $endingDatTime]
     */
    public function makeDateTimePeriod(\DateTime &$previous, string $firstModify, string $secondModify): array
    {
        $s = (new \DateTime)->setTimestamp($previous->getTimestamp())->modify($firstModify);
        $e = (new \DateTime)->setTimestamp($s->getTimestamp())->modify($firstModify);
        return [$s, $e];
    }
}
