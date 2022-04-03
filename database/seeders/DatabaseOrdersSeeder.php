<?php

namespace Database\Seeders;

use App\Models\Order\LaserOrder;
use App\Models\Order\LaserOrderPackage;
use App\Models\Order\LaserOrderPart;
use App\Models\Order\RegularOrder;
use App\Models\Package\Package;
use App\Models\Part\Part;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use TheClinic\Order\Laser\Calculations\PriceCalculator;
use TheClinic\Order\Laser\Calculations\TimeConsumptionCalculator;

class DatabaseOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        /** @var \App\Models\User $user */
        foreach (User::get()->all() as $user) {
            for ($i = 0; $i < $faker->numberBetween(2, 6); $i++) {
                $order = $user->orders()->create();
                $orderId = $order->{$order->getKeyName()};

                if ($i === 0) {
                    regular:
                    $price = $faker->numberBetween(1000000, 20000000);
                    $time = $faker->numberBetween(600, 5400);

                    $regularOrder = RegularOrder::factory()
                        ->setPrice($price)
                        ->setNeedeTime($time)
                        ->setOrderFK($orderId)
                        ->create();

                    continue;
                }

                if ($i === 1) {
                    laser:
                    $gender = $user->gender;
                    $parts = Part::query()->where('gender', '=', $gender)->inRandomOrder()->take($faker->numberBetween(1, 7))->get()->all();
                    $packages = Package::query()->where('gender', '=', $gender)->inRandomOrder()->take($faker->numberBetween(1, 3))->get()->all();

                    $price = (new PriceCalculator)->calculateWithoutDiscount(Part::getDSParts($parts, $gender), Package::getDSPackages($packages, $gender));
                    $priceWithDiscount = (new PriceCalculator)->calculate(Part::getDSParts($parts, $gender), Package::getDSPackages($packages, $gender));

                    $time = (new TimeConsumptionCalculator)->calculate(Part::getDSParts($parts, $gender), Package::getDSPackages($packages, $gender));

                    $laserOrder = LaserOrder::factory()
                        ->setPriceWithDiscount($priceWithDiscount)
                        ->setPrice($price)
                        ->setNeedeTime($time)
                        ->setOrderFK($orderId)
                        ->create();
                    $laserOrderId = $laserOrder->{$laserOrder->getKeyName()};

                    foreach ($parts as $part) {
                        $partId = $part->{$part->getKeyName()};

                        LaserOrderPart::factory()
                            ->setLaserOrderFK($laserOrderId)
                            ->setPartFK($partId)
                            ->create();
                    }

                    foreach ($packages as $package) {
                        $packageId = $package->{$package->getKeyName()};

                        LaserOrderPackage::factory()
                            ->setLaserOrderFK($laserOrderId)
                            ->setPackageFK($packageId)
                            ->create();
                    }

                    continue;
                }

                if ($faker->numberBetween(0, 1) === 0) {
                    goto laser;
                } else {
                    goto regular;
                }
            }
        }
    }
}
