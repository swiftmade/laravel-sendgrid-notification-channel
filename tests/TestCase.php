<?php

namespace NotificationChannels\SendGrid\Test;

use NotificationChannels\SendGrid\SendGridServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SendGridServiceProvider::class,
        ];
    }
}
