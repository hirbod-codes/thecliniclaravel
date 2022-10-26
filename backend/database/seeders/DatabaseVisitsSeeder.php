<?php

namespace Database\Seeders;

use App\Models\BusinessDefault;
use App\Models\Order\LaserOrder;
use App\Models\Order\Order;
use App\Models\Order\RegularOrder;
use App\Models\Package\Package;
use App\Models\Part\Part;
use App\Models\Visit\Visit;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Seeder;
use App\PoliciesLogic\Order\Laser\Calculations\TimeConsumptionCalculator;
use App\PoliciesLogic\Visit\FastestVisit;
use App\PoliciesLogic\Visit\WeeklyVisit;
use App\DataStructures\Time\DSTimePattern;
use App\DataStructures\Time\DSTimePatterns;
use App\DataStructures\Time\DSWeeklyTimePatterns;
use App\DataStructures\Visit\Laser\DSLaserVisits;
use App\DataStructures\Visit\Regular\DSRegularVisits;
use Database\Interactions\Business\DataBaseRetrieveBusinessSettings;
use Database\Interactions\Visits\Creation\DataBaseCreateLaserVisit;
use Database\Interactions\Visits\Creation\DataBaseCreateRegularVisit;
use Database\Interactions\Visits\Retrieval\DataBaseRetrieveLaserVisits;
use Database\Interactions\Visits\Retrieval\DataBaseRetrieveRegularVisits;

class DatabaseVisitsSeeder extends Seeder
{
    private DSLaserVisits $dsLaserVisits;

    private DSRegularVisits $dsRegularVisits;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        $this->dsLaserVisits = new DSLaserVisits("Natural");
        $this->dsRegularVisits = new DSRegularVisits("Natural");

        for ($i = 0; $i < count($orders = Order::all()); $i++) {
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
        $this->dsLaserVisits->setSort('ASC');
        $this->dsRegularVisits->setSort("ASC");
    }

    public function makeVisitsForLaserOrder(LaserOrder $laserOrder, Generator $faker): void
    {
        for ($i = 0; $i < 2; $i++) {
            ($visit = new Visit)->saveOrFail();

            $futureVisits = (new DataBaseRetrieveLaserVisits)->getDSFutureVisits();

            $gender = $laserOrder->order->user->gender;
            $parts = $laserOrder->parts;
            $packages = $laserOrder->packages;
            $time = (new TimeConsumptionCalculator)->calculate(Part::getDSParts($parts, $gender), Package::getDSPackages($packages, $gender));

            $dataBaseRetrieveBusinessSettings = (new DataBaseRetrieveBusinessSettings);

            if ($i === 0) {
                $iFindVisit = new WeeklyVisit(
                    $dsWekkDaysPeriods = $this->makeDSWekkDaysPeriods($faker),
                    $time,
                    $futureVisits,
                    $dataBaseRetrieveBusinessSettings->getWorkSchdule(),
                    $dataBaseRetrieveBusinessSettings->getDownTimes()
                );
            } else {
                $iFindVisit = new FastestVisit(
                    new \DateTime(),
                    $time,
                    $futureVisits,
                    $dataBaseRetrieveBusinessSettings->getWorkSchdule(),
                    $dataBaseRetrieveBusinessSettings->getDownTimes()
                );
            }

            $laserVisit = (new DataBaseCreateLaserVisit)->createLaserVisit($laserOrder, $iFindVisit);

            $this->dsLaserVisits[] = $dsLaserVisit = $laserVisit->getDSLaserVisit();
        }
    }

    public function makeVisitsForRegularOrder(RegularOrder $regularOrder, Generator $faker): void
    {
        for ($i = 0; $i < 2; $i++) {
            ($visit = new Visit)->saveOrFail();

            $futureVisits = (new DataBaseRetrieveRegularVisits)->getDSFutureVisits();

            $time = $regularOrder->needed_time;

            $dataBaseRetrieveBusinessSettings = (new DataBaseRetrieveBusinessSettings);

            if ($i === 0) {
                $iFindVisit = new WeeklyVisit(
                    $dsWekkDaysPeriods = $this->makeDSWekkDaysPeriods($faker),
                    $time,
                    $futureVisits,
                    $dataBaseRetrieveBusinessSettings->getWorkSchdule(),
                    $dataBaseRetrieveBusinessSettings->getDownTimes()
                );
            } else {
                $iFindVisit = new FastestVisit(
                    new \DateTime(),
                    $time,
                    $futureVisits,
                    $dataBaseRetrieveBusinessSettings->getWorkSchdule(),
                    $dataBaseRetrieveBusinessSettings->getDownTimes()
                );
            }

            $regularVisit = (new DataBaseCreateRegularVisit)->createRegularVisit($regularOrder, $iFindVisit);

            $this->dsRegularVisits[] = $regularVisit->getDSRegularVisit();
        }
    }

    private function makeDSWekkDaysPeriods(Generator $faker): DSWeeklyTimePatterns
    {
        $weekDays = $faker->randomElements(DSWeeklyTimePatterns::$weekDays, 3);
        $dsWeekDaysPeriods = new DSWeeklyTimePatterns($weekDays[0]);

        foreach ($weekDays as $weekDay) {
            $time = (new \DateTime)->setTime(6, 0);
            $dsDateTimePeriods = new DSTimePatterns;

            for ($i = 0; $i < $faker->numberBetween(1, 3); $i++) {
                $dsDateTimePeriods[] = new DSTimePattern(
                    $t = (new \DateTime)->setTimestamp($time->modify('+1 hour')->getTimestamp())->format("H:i:s"),
                    $t1 = (new \DateTime)->setTimestamp($time->modify('+4 hours')->getTimestamp())->format("H:i:s")
                );
            }

            $dsWeekDaysPeriods[$weekDay] = $dsDateTimePeriods;
        }

        return $dsWeekDaysPeriods;
    }

    private function moveToWeekDay(\DateTime &$time, string $weekDay): void
    {
        if (!in_array($weekDay, DSWeeklyTimePatterns::$weekDays)) {
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
