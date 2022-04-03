<?php

namespace Database\Seeders;

use App\Models\BusinessDefault;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseBusinessDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        $businessDefault = new BusinessDefault;
        $businessDefault->default_regular_order_price = $faker->numberBetween(5000000, 10000000);
        $businessDefault->default_regular_order_time_consumption = $faker->numberBetween(600, 1000);

        if (!$businessDefault->save()) {
            throw new \Exception('Failed to create the business default record.');
        }
    }
}
