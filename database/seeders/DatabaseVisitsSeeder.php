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
use TheClinic\Order\Laser\Calculations\TimeConsumptionCalculator;
use TheClinic\Visit\FastestVisit;
use TheClinic\Visit\Utilities\DownTime;
use TheClinic\Visit\Utilities\SearchingBetweenDownTimes;
use TheClinic\Visit\Utilities\SearchingBetweenTimeRange;
use TheClinic\Visit\Utilities\WorkSchedule;

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

        /** @var Order $order */
        foreach (Order::all() as $order) {
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
        for ($i = 0; $i < $faker->numberBetween(2, 4); $i++) {
            if (!($visit = new Visit)->save()) {
                throw new \RuntimeException('Failed to create a Visit model.', 500);
            }

            $now = new \DateTime();
            $futureVisits = LaserVisit::query()
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

            $visitTimestamp = new FastestVisit(
                new \DateTime(),
                $time,
                $futureVisits,
                BusinessDefault::first()->work_schedule,
                BusinessDefault::first()->down_times,
                new SearchingBetweenDownTimes(new SearchingBetweenTimeRange, new DownTime),
                new WorkSchedule,
                new DownTime
            );

            $laserVisit = new LaserVisit;
            $laserVisit->{$laserOrder->getForeignKey()} = $laserOrder->{$laserOrder->getKeyName()};
            $laserVisit->{$visit->getForeignKey()} = $visit->{$visit->getKeyName()};
            $laserVisit->visit_timestamp = $visitTimestamp;
            $laserVisit->consuming_time = $time;
            $laserVisit->week_days_periods = null;
            $laserVisit->date_time_period = null;

            if (!$laserVisit->save()) {
                throw new \RuntimeException('Failed to create a LaserVisit model.', 500);
            }
        }
    }

    public function makeVisitsForRegularOrder(RegularOrder $regularOrder, Generator $faker): void
    {
        for ($i = 0; $i < $faker->numberBetween(2, 4); $i++) {
            if (!($visit = new Visit)->save()) {
                throw new \RuntimeException('Failed to create a Visit model.', 500);
            }

            $now = new \DateTime();
            $futureVisits = LaserVisit::query()
                ->where('visit_timestamp', '>=', $now)
                ->get()
                ->all()
                //
            ;
            $futureVisits = LaserVisit::getDSLaserVisits($futureVisits, 'ASC');

            $businessDefault = BusinessDefault::first();
            $time = $businessDefault->default_regular_order_time_consumption;

            $visitTimestamp = new FastestVisit(
                new \DateTime(),
                $time,
                $futureVisits,
                $businessDefault->work_schedule,
                $businessDefault->down_time,
                new SearchingBetweenDownTimes(new SearchingBetweenTimeRange, new DownTime),
                new WorkSchedule,
                new DownTime
            );

            $regularVisit = new RegularVisit;
            $regularVisit->{$regularOrder->getForeignKey()} = $regularOrder->{$regularOrder->getKeyName()};
            $regularVisit->{$visit->getForeignKey()} = $visit->{$visit->getKeyName()};
            $regularVisit->visit_timestamp = $visitTimestamp;
            $regularVisit->consuming_time = $time;
            $regularVisit->week_days_periods = null;
            $regularVisit->date_time_period = null;

            if (!$regularVisit->save()) {
                throw new \RuntimeException('Failed to create a RegularVisit model.', 500);
            }
        }
    }
}
