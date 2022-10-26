<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        /** @var Application $app */
        $app = $this->app;

        if (!$app->routesAreCached()) {
            Passport::routes(function ($router) {
                // $router->forAccessTokens();
                // $router->forPersonalAccessTokens();
                // $router->forTransientTokens();
            });
            // Passport::routes();
        }

        Passport::hashClientSecrets();

        $minutes = config('session.lifetime');
        $delay = (new \DateTime)->modify('+' . $minutes . ' minutes');
        Passport::tokensExpireIn($delay);
        Passport::refreshTokensExpireIn($delay);
        Passport::personalAccessTokensExpireIn($delay);

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Verify Email Address')
                ->line('Click the button below to verify your email address.')
                ->action('Verify Email Address', $url);
        });
    }
}
