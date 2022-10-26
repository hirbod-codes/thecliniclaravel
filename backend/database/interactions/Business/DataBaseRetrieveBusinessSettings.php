<?php

namespace Database\Interactions\Business;

use App\DataStructures\Time\DSDownTimes;
use App\DataStructures\Time\DSWeeklyTimePatterns;
use App\Models\BusinessDefault;
use Database\Interactions\Business\Interfaces\IDataBaseRetrieveBusinessSettings;

class DataBaseRetrieveBusinessSettings implements IDataBaseRetrieveBusinessSettings
{
    public function getWorkSchdule(): DSWeeklyTimePatterns
    {
        return BusinessDefault::query()->firstOrFail()->work_schedule;
    }

    public function getDownTimes(): DSDownTimes
    {
        return BusinessDefault::query()->firstOrFail()->down_times;
    }

    public function getDefaultRegularOrderPrice(): int
    {
        return BusinessDefault::query()->firstOrFail()->default_regular_order_price;
    }

    public function getDefaultRegularOrderTimeConsumption(): int
    {
        return BusinessDefault::query()->firstOrFail()->default_regular_order_time_consumption;
    }
}
