<?php

namespace Database\Seeders;

use App\Models\BusinessDefault;
use Database\Factories\WorkScheduleFactory;
use Faker\Factory;
use Illuminate\Database\Seeder;
use App\DataStructures\Time\DSDownTimes;
use App\Models\Business;

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

        foreach (['laser', 'regular'] as $BusinessName) {
            $business = new Business(['name' => $BusinessName]);
            $business->saveOrFail();

            $dsWorkSchedule = (new WorkScheduleFactory())->generateWorkSchedule();
            $dsDown_times = new DSDownTimes();

            $businessDefault = new BusinessDefault;
            $businessDefault->{$business->getForeignKey()} = $business->getKey();
            $businessDefault->genders = ['Male', 'Female'];
            $businessDefault->min_age = 16;
            $businessDefault->visit_alert_deley = intval(3600 * 6);
            if ($BusinessName === 'regular') {
                $businessDefault->default_regular_order_price = $faker->numberBetween(5000000, 10000000);
                $businessDefault->default_regular_order_time_consumption = $faker->numberBetween(600, 1000);
            }
            $businessDefault->work_schedule = $dsWorkSchedule;
            $businessDefault->down_times = $dsDown_times;

            $businessDefault->saveOrFail();
        }
    }
}
