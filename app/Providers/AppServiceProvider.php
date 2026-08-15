<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        
        VerifyEmail::createUrlUsing(function ($notifiable) {
            $id = $notifiable->getKey();
            $hash = sha1($notifiable->getEmailForVerification());
            $expires = now()->addMinutes(60)->timestamp;

            $signature = hash_hmac(
                'sha256',
                "verification.verify|{$id}|{$hash}|{$expires}",
                config('app.key')
            );

            $frontendUrl = config('app.frontend_url');

            return "{$frontendUrl}/verify-email?id={$id}&hash={$hash}&expires={$expires}&signature={$signature}";
        });
    }
}
