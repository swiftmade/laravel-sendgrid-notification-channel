<?php

namespace NotificationChannels\SendGrid;

use Illuminate\Notifications\Notification;
use Illuminate\Support\ServiceProvider;
use SendGrid;

class SendGridServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->app->when(SendGridChannel::class)
            ->needs(SendGrid::class)
            ->give(function () {
                return new SendGrid(
                    $this->app['config']['services.sendgrid.api_key']
                );
            });
    }
}
