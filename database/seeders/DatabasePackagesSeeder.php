<?php

namespace Database\Seeders;

use App\Models\Package\Package;
use App\Models\Package\PartPackage;
use App\Models\Part\Part;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use TheClinic\Order\Laser\Calculations\PriceCalculator;
use TheClinicDataStructures\DataStructures\Order\DSPackage;
use TheClinicDataStructures\DataStructures\Order\DSPackages;

class DatabasePackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $gender = $faker->randomElement(['Male', 'Female']);
            
            $parts = Part::query()->where('gender', '=', $gender)->inRandomOrder()->take($faker->numberBetween(2, 5))->get()->all();

            $price = (new PriceCalculator)->calculate(Part::getDSParts($parts, $gender), new DSPackages($gender));

            $package = Package::factory()->setPrice($price)->setGender($gender)->create();
            $packageId = $package->{$package->getKeyName()};

            foreach ($parts as $part) {
                $partId = $part->{$part->getKeyName()};

                PartPackage::factory()
                    ->setPartFK($partId)
                    ->setPackageFK($packageId)
                    ->create();
            }
        }
    }
}
