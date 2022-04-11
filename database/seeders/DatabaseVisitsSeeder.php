<?php

namespace Database\Seeders;

use App\Models\BusinessDefault;
use App\Models\Order\LaserOrder;
use App\Models\Order\Order;
use App\Models\Order\RegularOrder;
use App\Models\Package\Package;
use App\Models\Part\Part;
use App\Models\Visit\LaserVisit;
use App\Models\Visit\RegularVisit;
use App\Models\Visit\Visit;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use TheClinic\Order\Laser\Calculations\TimeConsumptionCalculator;
use TheClinic\Visit\FastestVisit;
use TheClinic\Visit\WeeklyVisit;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriod;
use TheClinicDataStructures\DataStructures\Time\DSDateTimePeriods;
use TheClinicDataStructures\DataStructures\Time\DSWeekDaysPeriods;

class DatabaseVisitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        for ($i = 0; $i < count($orders = Order::all()); $i++) {
            if ($i % 2 === 0) {
                continue;
            }

            /** @var Order $order */
            $order = $orders[$i];
            if (($laserOrder = $order->laserOrder) !== null) {
                $this->makeVisitsForLaserOrder($laserOrder, $faker);
                continue;
            }

            if (($regularOrder = $order->regularOrder) !== null) {
                $this->makeVisitsForRegularOrder($regularOrder, $faker);
                continue;
            }
        }
    }

    public function makeVisitsForLaserOrder(LaserOrder $laserOrder, Generator $faker): void
    {
        for (
            $i = 0;
            $i <
                // $faker->numberBetween(2, 3)
                2
                //
            ;
            $i++
        ) {
            if (!($visit = new Visit)->save()) {
                throw new \RuntimeException('Failed to create a Visit model.', 500);
            }

            $now = new \DateTime();
            $futureVisits = LaserVisit::query()
                ->orderBy('visit_timestamp', 'asc')
                ->where('visit_timestamp', '>=', $now)
                ->get()
                ->all()
                //
            ;
            $futureVisits = LaserVisit::getDSLaserVisits($futureVisits, 'ASC');

            $gender = $laserOrder->order->user->gender;
            $parts = $laserOrder->parts;
            $packages = $laserOrder->packages;
            $time = (new TimeConsumptionCalculator)->calculate(Part::getDSParts($parts, $gender), Package::getDSPackages($packages, $gender));
            $time = 3600;

            if ($i === 0) {
                $visitTimestamp = (new WeeklyVisit(
                    $dsWekkDaysPeriods = $this->makeDSWekkDaysPeriods($faker),
                    $time,
                    $futureVisits,
                    BusinessDefault::first()->work_schedule,
                    BusinessDefault::first()->down_times
                ))->findVisit();
            } else {
                $visitTimestamp = (new FastestVisit(
                    new \DateTime(),
                    $time,
                    $futureVisits,
                    BusinessDefault::first()->work_schedule,
                    BusinessDefault::first()->down_times
                ))->findVisit();
            }

            $laserVisit = new LaserVisit;
            $laserVisit->{$laserOrder->getForeignKey()} = $laserOrder->{$laserOrder->getKeyName()};
            $laserVisit->{$visit->getForeignKey()} = $visit->{$visit->getKeyName()};
            $laserVisit->visit_timestamp = $visitTimestamp;
            $laserVisit->consuming_time = $time;

            if ($i === 0) {
                $laserVisit->week_days_periods = $dsWekkDaysPeriods;
            } else {
                $laserVisit->week_days_periods = null;
            }

            $laserVisit->date_time_period = null;

            if (!$laserVisit->save()) {
                throw new \RuntimeException('Failed to create a LaserVisit model.', 500);
            }
        }
    }

    public function makeVisitsForRegularOrder(RegularOrder $regularOrder, Generator $faker): void
    {
        for (
            $i = 0;
            $i <
                // $faker->numberBetween(2, 3)
                2
                //
            ;
            $i++
        ) {
            if (!($visit = new Visit)->save()) {
                throw new \RuntimeException('Failed to create a Visit model.', 500);
            }

            $now = new \DateTime();
            $futureVisits = RegularVisit::query()
                ->orderBy('visit_timestamp', 'asc')
                ->where('visit_timestamp', '>=', $now)
                ->get()
                ->all()
                //
            ;
            $futureVisits = RegularVisit::getDSRegularVisits($futureVisits, 'ASC');

            $businessDefault = BusinessDefault::first();
            $time = $businessDefault->default_regular_order_time_consumption;
            $time = 3600;

            if ($i === 0) {
                $visitTimestamp = (new WeeklyVisit(
                    $dsWekkDaysPeriods = $this->makeDSWekkDaysPeriods($faker),
                    $time,
                    $futureVisits,
                    BusinessDefault::first()->work_schedule,
                    BusinessDefault::first()->down_times
                ))->findVisit();
            } else {
                $visitTimestamp = (new FastestVisit(
                    new \DateTime(),
                    $time,
                    $futureVisits,
                    BusinessDefault::first()->work_schedule,
                    BusinessDefault::first()->down_times
                ))->findVisit();
            }

            $RegularVisit = new RegularVisit;
            $RegularVisit->{$regularOrder->getForeignKey()} = $regularOrder->{$regularOrder->getKeyName()};
            $RegularVisit->{$visit->getForeignKey()} = $visit->{$visit->getKeyName()};
            $RegularVisit->visit_timestamp = $visitTimestamp;
            $RegularVisit->consuming_time = $time;

            if ($i === 0) {
                $RegularVisit->week_days_periods = $dsWekkDaysPeriods;
            } else {
                $RegularVisit->week_days_periods = null;
            }

            $RegularVisit->date_time_period = null;

            if (!$RegularVisit->save()) {
                throw new \RuntimeException('Failed to create a RegularVisit model.', 500);
            }
        }
    }

    private function makeDSWekkDaysPeriods(Generator $faker): DSWeekDaysPeriods
    {
        $weekDays = $faker->randomElements(DSWeekDaysPeriods::$weekDays, 3);
        $dsWeekDaysPeriods = new DSWeekDaysPeriods($weekDays[0]);

        foreach ($weekDays as $weekDay) {
            $time = (new \DateTime)->setTime(6, 0);
            $dsDateTimePeriods = new DSDateTimePeriods;
            $this->moveToWeekDay($time, $weekDay);

            for ($i = 0; $i < $faker->numberBetween(1, 3); $i++) {
                $dsDateTimePeriods[] = new DSDateTimePeriod(
                    $t = (new \DateTime)->setTimestamp($time->modify('+1 hour')->getTimestamp()),
                    $t1 = (new \DateTime)->setTimestamp($time->modify('+4 hours')->getTimestamp())
                );
            }

            $dsWeekDaysPeriods[$weekDay] = $dsDateTimePeriods;
        }

        return $dsWeekDaysPeriods;
    }

    private function moveToWeekDay(\DateTime &$time, string $weekDay): void
    {
        if (!in_array($weekDay, DSWeekDaysPeriods::$weekDays)) {
            throw new \InvalidArgumentException('The variable $weekDay must be one weeks days name.The given name: ' . $weekDay);
        }

        $safety = 0;
        while ($time->format('l') !== $weekDay && $safety < 9) {
            $time->modify('+1 day');
            $safety++;
        }

        if ($time->format('l') !== $weekDay) {
            throw new \RuntimeException('safety limit reached!!!', 500);
        }
    }
}
