[![Latest Version on Packagist](https://img.shields.io/packagist/v/swiftmade/laravel-sendgrid-notification-channel.svg?style=flat-square)](https://packagist.org/packages/swiftmade/laravel-sendgrid-notification-channel)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/swiftmade/laravel-sendgrid-notification-channel.svg?style=flat-square)](https://packagist.org/packages/swiftmade/laravel-sendgrid-notification-channel)

Allows you to send Laravel notifications using Sendgrid's [Dynamic Transactional Templates](https://docs.sendgrid.com/ui/sending-email/how-to-send-an-email-with-dynamic-transactional-templates) feature. Supports Laravel 7.x, 8.x, 9.x and 10.x.

(For older versions of Laravel, install v1)

## Contents

-   [Installation](#installation)
-   [Usage](#usage)
-   [Changelog](#changelog)
-   [Testing](#testing)
-   [Security](#security)
-   [Contributing](#contributing)
-   [Credits](#credits)
-   [License](#license)

## Installation

To get started, you need to require this package:

```bash
composer require swiftmade/laravel-sendgrid-notification-channel
```

The service provider will be auto-detected by Laravel. If you've turned auto-discovery off, add the following service provider in your `config/app.php`.

```
NotificationChannels\SendGrid\SendGridServiceProvider::class,
```

Next, make sure you have a valid Sendgrid API key in `config/services.php`. You may copy the example configuration below to get started:

```php
return [

    // other services...

    // add this...
    'sendgrid' => [
        'api_key' => env('SENDGRID_API_KEY'),
    ],
];
```

## Usage

To send an email using dynamic templates, you need to:

1. Return `SendGridChannel::class` in the `via()` method. (Not `mail`)
2. Add and implement the `toSendGrid($notifiable){ }` method.

Example:

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\SendGrid\SendGridChannel;

class ExampleNotification extends Notification
{
    public function via($notifiable)
    {
        return [
            SendGridChannel::class,
            // And any other channels you want can go here...
        ];
    }

    // ...

    public function toSendGrid($notifiable)
    {
        return (new SendGridMessage('Your SendGrid template ID'))
            /**
             * optionally set the from address.
             * by default this comes from config/mail.from
             * ->from('no-reply@test.com', 'App name')
             */
            /**
             * optionally set the recipient.
             * by default it's $notifiable->routeNotificationFor('mail')
             * ->to('hello@example.com', 'Mr. Smith')
             */
            ->payload([
                "template_var_1" => "template_value_1"
            ]);
	}
}

```

`toSendGrid` method will receive a `$notifiable` entity and should return a `NotificationChannels\SendGrid\SendGridMessage` instance.

💡 Unless you set it explicitly, the **From** address will be set to `config('mail.from.address')` and the **To** value will be what returns from `$notifiable->routeNotificationFor('mail');`

### Sandbox Mode

The sandbox mode is **off** by default. You can use the `setSandboxMode($bool)` method to enable/disable it.

Example:

```php
return (new SendGridMessage('Your SendGrid template ID'))
    ->setSandboxMode(true)
    ->payload([
        'template_var_1' => 'template_value_1',
        'template_var_2' => [
            'value_1',
            'value_2',
            'value_3',
        ],
    ]);
```

When making a request with sandbox mode enabled, Sendgrid will validate the form, type, and shape of your request. No email will be sent. You can read more about the sandbox mode [here](https://docs.sendgrid.com/for-developers/sending-email/sandbox-mode).

### Attachments

You can attach or embed (inline attachment) files to your messages. `SendGridMessage` object exposes the following methods to help you do that:

-   `attach($file, $options)`
-   `attachData($data, $name, $options)`
-   `embed($file, $options)`
-   `embedData($data, $name, $options)`

**Good to know:**

-   While using `attachData` and `embedData` you must always pass the `mime` key in the options array.
-   You can use the `as` key in the options to change the filename to appears in the email. (e.g. `attach($file, ['as' => 'invoice-3252.pdf'])`)
-   `embed` and `embedData` methods will return the ContentID with `cid:` in front (e.g. `embed('avatar.jpg') -> "cid:avatar.jpg"`).

### Full Access to the Sendgrid Mail Object

If you need more customization options, you can work directly with the underlying Sendgrid Mail object.
To utilize this, simply pass a callback using the `customize` method.

```php
use SendGrid\Mail\Mail;

return (new SendGridMessage('Your SendGrid template ID'))
    ->payload([
        'template_var_1' => 'template_value_1',
        'template_var_2' => [
            'value_1',
            'value_2',
            'value_3',
        ],
    ])
    ->customize(function (Mail $mail) {
        // Send a carbon copy (cc) to another address
        $mail->addCc('test@test.com');
        // Send a blind carbon copy (bcc) to another address
        $mail->addBcc('bcc@test.com');
    });
```

For all the options, you can see Sendgrid's Mail class [here](https://github.com/sendgrid/sendgrid-php/blob/main/lib/mail/Mail.php).

### Accessing SendGrid Response

After the notification is sent, Laravel will emit `Illuminate\Notifications\Events\NotificationSent` event. You can listen to this event to get the SendGrid response object and/or message ID.

```php
use Illuminate\Notifications\Events\NotificationSent;

Event::listen(NotificationSent::class, function (NotificationSent $event) {
    /**
     * @var \SendGrid\Response $response
     */
    $response = $event->response;
});
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

```bash
$ composer test
```

## Security

If you discover any security related issues, please email hello@swiftmade.co instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

-   [swiftmade](https://github.com/swiftmade)
-   [cuonggt](https://github.com/cuonggt/laravel-sendgrid-notification-channel)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
