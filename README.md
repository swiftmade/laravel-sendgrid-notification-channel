[![Latest Version on Packagist](https://img.shields.io/packagist/v/swiftmade/laravel-notification-sendgrid-channel.svg?style=flat-square)](https://packagist.org/packages/swiftmade/laravel-notification-sendgrid-channel)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/swiftmade/laravel-notification-sendgrid-channel/master.svg?style=flat-square)](https://travis-ci.org/swiftmade/laravel-notification-sendgrid-channel)
[![StyleCI](https://styleci.io/repos/:style_ci_id/shield)](https://styleci.io/repos/:style_ci_id)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/:sensio_labs_id.svg?style=flat-square)](https://insight.sensiolabs.com/projects/:sensio_labs_id)
[![Quality Score](https://img.shields.io/scrutinizer/g/swiftmade/laravel-notification-sendgrid-channel.svg?style=flat-square)](https://scrutinizer-ci.com/g/swiftmade/laravel-notification-sendgrid-channel)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/swiftmade/laravel-notification-sendgrid-channel/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/swiftmade/laravel-notification-sendgrid-channel/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/swiftmade/laravel-notification-sendgrid-channel.svg?style=flat-square)](https://packagist.org/packages/swiftmade/laravel-notification-sendgrid-channel)

This package makes it easy to send notifications using [SendGrid](https://sendgrid.com) with Laravel 5.5+, 6.x and 7.x

## Contents

- [Installation](#installation)
- [Usage](#usage)
- [Changelog](#changelog)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


## Installation

To get started, you need to require this package:

```bash
composer require swiftmade/laravel-notification-sendgrid-channel
```

Next, make sure you have a valid sendgrid api key at `config/services.php`. You may copy the example configuration below to get started:

```php
    'sendgrid' => [
        'api_key' => env('SENDGRID_API_KEY'),
    ],
```

## Usage

You should add a `toSendGrid` method into the notification class. This method will receive a `$notifiable` entity and should return a  `NotificationChannels\SendGrid\SendGridMessage` instance:

```php
    /**
     * Get the SendGrid representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \NotificationChannels\SendGrid\SendGridMessage
     */
    public function toSendGrid($notifiable)
    {
        return (new SendGridMessage('Your SendGrid template ID'))
                    ->payload([
						"template_var_1" => "template_value_1"
					])
                    ->from('test@example.com', 'Example User')
                    ->to('test+test1@example.com', 'Example User1');
	}
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email hello@swiftmade.co instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [swiftmade](https://github.com/swiftmade)
- [cuonggt](https://github.com/cuonggt/sendgrid)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
