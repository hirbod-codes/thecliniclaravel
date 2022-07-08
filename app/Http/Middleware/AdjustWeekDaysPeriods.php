<?php

namespace App\Http\Middleware;

use App\Http\Requests\Visits\LaserStoreRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use TheClinicDataStructures\DataStructures\Time\DSWeekDaysPeriods;

class AdjustWeekDaysPeriods
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
        $validator = Validator::make($t = $request->all(), (new LaserStoreRequest())->rules(), (new LaserStoreRequest())->messages(), (new LaserStoreRequest())->attributes());
        if ($validator->fails()) {
            return response()->json($validator->errors()->toArray());
        }

        $locale = session()->get('locale', App::getLocale());

        if (!isset($request->weekDaysPeriods) || $locale === 'en') {
            return $next($request);
        }

        if ($locale === 'fa') {
            $request->merge(['weekDaysPeriods'=> $this->convertToUTC($request->weekDaysPeriods)]);
            $t = $request->all();
        }

        return $next($request);
    }

    private function convertToUTC(array $weekDaysPeriods): array
    {
        $newWeekDaysPeriods = [];
        foreach ($weekDaysPeriods as $weekDay => $timePeriods) {
            $newWeekDaysPeriods[$weekDay] = [];

            foreach ($timePeriods as $timePeriod) {
                $start = new \DateTime($timePeriod['start'], new \DateTimeZone('Asia/Tehran'));
                $end = new \DateTime($timePeriod['end'], new \DateTimeZone('Asia/Tehran'));

                $actualDay = (new \DateTime($start->format('Y-m-d' . ' 00:00:00')));

                $startUTC = (new \DateTime())->setTimestamp($start->getTimestamp())->setTimezone(new \DateTimeZone('UTC'));
                $endUTC = (new \DateTime())->setTimestamp($end->getTimestamp())->setTimezone(new \DateTimeZone('UTC'));

                if (
                    $start->format('Y-m-d') !== $startUTC->format('Y-m-d') &&
                    $end->format('Y-m-d') !== $endUTC->format('Y-m-d')
                ) {
                    if ($startUTC->getTimestamp() < $actualDay->getTimestamp()) {
                        $theOtherDayKey = array_search($startUTC->format('l'), DSWeekDaysPeriods::$weekDays) - 1;
                        $theOtherDay = DSWeekDaysPeriods::$weekDays[$theOtherDayKey];
                    } elseif ($startUTC->getTimestamp() > $actualDay->getTimestamp()) {
                        $theOtherDayKey = array_search($startUTC->format('l'), DSWeekDaysPeriods::$weekDays) + 1;
                        $theOtherDay = DSWeekDaysPeriods::$weekDays[$theOtherDayKey];
                    }

                    if (!isset($newWeekDaysPeriods[$theOtherDay])) {
                        $newWeekDaysPeriods[$theOtherDay] = [];
                    }

                    $newWeekDaysPeriods[$theOtherDay][] = ['start' => $startUTC->format('Y-m-d H:i:s'), 'end' => $endUTC->format('Y-m-d H:i:s')];
                } elseif ($start->format('Y-m-d') !== $startUTC->format('Y-m-d')) {
                    $theOtherDayKey = array_search($startUTC->format('l'), DSWeekDaysPeriods::$weekDays) - 1;
                    $theOtherDay = DSWeekDaysPeriods::$weekDays[$theOtherDayKey];

                    if (!isset($newWeekDaysPeriods[$theOtherDay])) {
                        $newWeekDaysPeriods[$theOtherDay] = [];
                    }

                    $newWeekDaysPeriods[$theOtherDay][] = ['start' => $startUTC->format('Y-m-d H:i:s'), 'end' => $startUTC->format('Y-m-d') . ' 23:59:59'];
                    $newWeekDaysPeriods[$weekDay][] = ['start' => $endUTC->format('Y-m-d') . ' 00:00:00', 'end' => $endUTC->format('Y-m-d H:i:s')];
                } elseif ($end->format('Y-m-d') !== $endUTC->format('Y-m-d')) {
                    $theOtherDayKey = array_search($startUTC->format('l'), DSWeekDaysPeriods::$weekDays) + 1;
                    $theOtherDay = DSWeekDaysPeriods::$weekDays[$theOtherDayKey];

                    if (!isset($newWeekDaysPeriods[$theOtherDay])) {
                        $newWeekDaysPeriods[$theOtherDay] = [];
                    }

                    $newWeekDaysPeriods[$theOtherDay][] = ['start' => $endUTC->format('Y-m-d') . ' 00:00:00', 'end' => $endUTC->format('Y-m-d H:i:s')];
                    $newWeekDaysPeriods[$weekDay][] = ['start' => $startUTC->format('Y-m-d H:i:s'), 'end' => $startUTC->format('Y-m-d') . ' 23:59:59'];
                } else {
                    $newWeekDaysPeriods[$weekDay][] = ['start' => $startUTC->format('Y-m-d H:i:s'), 'end' => $endUTC->format('Y-m-d H:i:s')];
                }
            }
        }

        return $newWeekDaysPeriods;
    }
}
