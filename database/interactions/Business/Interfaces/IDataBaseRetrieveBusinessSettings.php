<?php

namespace Database\Interactions\Business\Interfaces;

use App\DataStructures\Time\DSDownTimes;
use App\DataStructures\Time\DSWeeklyTimePatterns;

interface IDataBaseRetrieveBusinessSettings
{
    public function getWorkSchdule(): DSWeeklyTimePatterns;

    public function getDownTimes(): DSDownTimes;
}
