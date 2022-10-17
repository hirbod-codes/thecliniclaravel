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
}
