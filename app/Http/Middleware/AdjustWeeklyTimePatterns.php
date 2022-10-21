<?php

namespace App\Http\Middleware;

use App\Http\Requests\Visits\LaserStoreRequest;
use App\Http\Requests\Visits\RegularStoreRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\DataStructures\Time\DSWeeklyTimePatterns;

class AdjustWeeklyTimePatterns
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Str::contains($request->path(), 'laser')) {
            $validator = Validator::make($t = $request->all(), (new LaserStoreRequest())->rules(), (new LaserStoreRequest())->messages(), (new LaserStoreRequest())->attributes());
        } else {
            $validator = Validator::make($t = $request->all(), (new RegularStoreRequest())->rules(), (new RegularStoreRequest())->messages(), (new RegularStoreRequest())->attributes());
        }

        if ($validator->fails()) {
            return response()->json($validator->errors()->toArray());
        }

        $locale = session()->get('locale', App::getLocale());

        if (!isset($request->weeklyTimePatterns) || $locale === 'en') {
            return $next($request);
        }

        if ($locale === 'fa') {
            $request->merge(['weeklyTimePatterns' => $this->convertToUTC($request->weeklyTimePatterns)]);
            $t = $request->all();
        }

        return $next($request);
    }

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
