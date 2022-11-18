<?php

namespace App\Http\Requests;

use App\DataStructures\Time\DSWeeklyTimePatterns;

trait TimeZoneConversionTrait
{
    private function convertToUTC(array $weeklyTimePatterns): array
    {
        $newWeekDaysPeriods = [];
        foreach ($weeklyTimePatterns as $weekDay => $timePeriods) {
            $newWeekDaysPeriods[$weekDay] = [];

            foreach ($timePeriods as $timePeriod) {
                $start = new \DateTime($timePeriod['start'], new \DateTimeZone('Asia/Tehran'));
                $end = new \DateTime($timePeriod['end'], new \DateTimeZone('Asia/Tehran'));

                $actualDay = (new \DateTime($start->format('Y-m-d')));
                $actualDay->setTime(0, 0, 0);

                $startUTC = (new \DateTime())->setTimestamp($start->getTimestamp())->setTimezone(new \DateTimeZone('UTC'));
                $endUTC = (new \DateTime())->setTimestamp($end->getTimestamp())->setTimezone(new \DateTimeZone('UTC'));

                if (
                    $start->format('Y-m-d') !== $startUTC->format('Y-m-d') &&
                    $end->format('Y-m-d') !== $endUTC->format('Y-m-d')
                ) {
                    if ($startUTC->getTimestamp() < $actualDay->getTimestamp()) {
                        $theOtherDayKey = array_search($startUTC->format('l'), DSWeeklyTimePatterns::$weekDays) - 1;
                        $theOtherDay = DSWeeklyTimePatterns::$weekDays[$theOtherDayKey];
                    } elseif ($startUTC->getTimestamp() > $actualDay->getTimestamp()) {
                        $theOtherDayKey = array_search($startUTC->format('l'), DSWeeklyTimePatterns::$weekDays) + 1;
                        $theOtherDay = DSWeeklyTimePatterns::$weekDays[$theOtherDayKey];
                    }

                    if (!isset($newWeekDaysPeriods[$theOtherDay])) {
                        $newWeekDaysPeriods[$theOtherDay] = [];
                    }

                    $newWeekDaysPeriods[$theOtherDay][] = ['start' => $startUTC->format('H:i:s'), 'end' => $endUTC->format('H:i:s')];
                } elseif ($start->format('Y-m-d') !== $startUTC->format('Y-m-d')) {
                    $theOtherDayKey = array_search($startUTC->format('l'), DSWeeklyTimePatterns::$weekDays) - 1;
                    $theOtherDay = DSWeeklyTimePatterns::$weekDays[$theOtherDayKey];

                    if (!isset($newWeekDaysPeriods[$theOtherDay])) {
                        $newWeekDaysPeriods[$theOtherDay] = [];
                    }

                    $newWeekDaysPeriods[$theOtherDay][] = ['start' => $startUTC->format('H:i:s'), 'end' => ' 23:59:59'];
                    $newWeekDaysPeriods[$weekDay][] = ['start' => '00:00:00', 'end' => $endUTC->format('H:i:s')];
                } elseif ($end->format('Y-m-d') !== $endUTC->format('Y-m-d')) {
                    $theOtherDayKey = array_search($endUTC->format('l'), DSWeeklyTimePatterns::$weekDays) + 1;
                    $theOtherDay = DSWeeklyTimePatterns::$weekDays[$theOtherDayKey];

                    if (!isset($newWeekDaysPeriods[$theOtherDay])) {
                        $newWeekDaysPeriods[$theOtherDay] = [];
                    }

                    $newWeekDaysPeriods[$theOtherDay][] = ['start' => '00:00:00', 'end' => $endUTC->format('H:i:s')];
                    $newWeekDaysPeriods[$weekDay][] = ['start' => $startUTC->format('H:i:s'), 'end' => ' 23:59:59'];
                } else {
                    $newWeekDaysPeriods[$weekDay][] = ['start' => $startUTC->format('H:i:s'), 'end' => $endUTC->format('H:i:s')];
                }
            }
        }

        return $newWeekDaysPeriods;
    }
}
