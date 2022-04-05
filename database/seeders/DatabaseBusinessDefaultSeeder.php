<?php

namespace Database\Seeders;

use App\Models\BusinessDefault;
use Database\Factories\WorkScheduleFactory;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use TheClinicDataStructures\DataStructures\Time\DSDownTimes;

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

        $dsWorkSchedule = (new WorkScheduleFactory())->generateWorkSchedule();
        $dsDown_times = new DSDownTimes();

        $businessDefault = new BusinessDefault;
        $businessDefault->default_regular_order_price = $faker->numberBetween(5000000, 10000000);
        $businessDefault->default_regular_order_time_consumption = $faker->numberBetween(600, 1000);
        $businessDefault->work_schedule = $dsWorkSchedule;
        $businessDefault->down_times = $dsDown_times;

        if (!$businessDefault->save()) {
            throw new \Exception('Failed to create the business default record.');
        }
    }
}
