<?php

namespace Database\Seeders;

use App\Models\Package\Package;
use App\Models\Package\PartPackage;
use App\Models\Part\Part;
use Illuminate\Database\Seeder;
use App\PoliciesLogic\Order\Laser\Calculations\PriceCalculator;
use App\DataStructures\Order\DSPackages;

class DatabasePackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 10; $i++) {
            $gender = rand(0, 1) === 1 ? "Male" : "Female";

            $parts = Part::query()->where('gender', '=', $gender)->inRandomOrder()->take(rand(2, 5))->get()->all();

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
