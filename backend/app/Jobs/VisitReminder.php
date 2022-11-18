<?php

namespace App\Jobs;

use HirbodKhatami\SmsPackage\Exceptions\SmsNotSentException;
use App\Models\BusinessDefault;
use App\Models\User;
use App\Models\Visit\LaserVisit;
use App\Models\Visit\RegularVisit;
use App\Models\Visit\Visit;
use App\Notifications\VisitAlert;
use App\Notifications\VisitAlertFailed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VisitReminder implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        foreach (Visit::query()
            ->whereHas('laserVisit', function ($query) {
                $query
                    ->where('visitor_reminded', '=', false)
                    ->where('visit_timestamp', '>=', (new \DateTime)->getTimestamp())
                    ->where('visit_timestamp', '<=', BusinessDefault::first()->visit_alert_deley)
                    //
                ;
            })
            ->get()
            ->all() as $visit) {
            /**
             * @var Visit $visit
             * @var LaserVisit $laserVisit
             */
            $laserVisit = $visit->laserVisit;
            /** @var int $visitTimestamp */
            $visitTimestamp = $laserVisit->visit_timestamp;
            /** @var User $user */
            $user = $laserVisit->laserOrder->order->user;

            $this->sendNotification($user, $visitTimestamp, $visit->getKey());
        }

        foreach (Visit::query()
            ->whereHas('regularVisit', function ($query) {
                $query
                    ->where('visitor_reminded', '=', false)
                    ->where('visit_timestamp', '>=', (new \DateTime)->getTimestamp())
                    ->where('visit_timestamp', '<=', BusinessDefault::first()->visit_alert_deley)
                    //
                ;
            })
            ->get()
            ->all() as $visit) {
            /**
             * @var Visit $visit
             * @var RegularVisit $laserVisit
             */
            $regularVisit = $visit->regularVisit;
            /** @var int $visitTimestamp */
            $visitTimestamp = $regularVisit->visit_timestamp;
            /** @var User $user */
            $user = $regularVisit->regularOrder->order->user;

            $this->sendNotification($user, $visitTimestamp, $visit->getKey());
        }
    }

    private function sendNotification(User $user, int $visitTimestamp, int $visitKey): void
    {
        try {
            if ($user === null) {
                throw new ModelNotFoundException('The user has not found.', 404);
            }

            $user->notify((new VisitAlert($visitTimestamp)));
        } catch (SmsNotSentException $th) {
            Log::error('System failed to notify user with username: \'' . $user->username . '\' for it\'s visit due at: ' . (new \DateTime)->setTimestamp($visitTimestamp)->format("Y-m-d H:i:s l P"), []);

            $admin = User::query()
                ->where('username', '=', 'hirbod')
                ->firstOrFail();

            try {
                $admin->notify((new VisitAlertFailed($visitTimestamp, $user)));
            } catch (SmsNotSentException $th) {
                Log::error('System failed to notify user with username: \'' . $admin->username . '\' for user visit due at: ' . (new \DateTime)->setTimestamp($visitTimestamp)->format("Y-m-d H:i:s l P") . ' with username: ' . $user->username, []);
            }
        } catch (ModelNotFoundException $e) {
            if ($e->getMessage() === '') {
                Log::error('Failed to find the user model.The Visit model id is: ' . strval($visitKey), []);
            } else {
                Log::error('Failed to find the admin model!!!', []);
            }
        }
    }
}
