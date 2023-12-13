<?php

namespace NotificationChannels\SendGrid;

use SendGrid;
use Exception;
use Illuminate\Notifications\Notification;
use NotificationChannels\SendGrid\Exceptions\CouldNotSendNotification;

class SendGridChannel
{
    /**
     * @var SendGrid
     */
    private $sendGrid;

    public function __construct(SendGrid $sendGrid)
    {
        $this->sendGrid = $sendGrid;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @throws \NotificationChannels\SendGrid\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        if (! method_exists($notification, 'toSendGrid')) {
            throw new Exception('You must implement toSendGrid in the notification class for SendGrid channel.');
        }

        /**
         * @var SendGridMessage
         */
        $message = $notification->toSendGrid($notifiable);

        if (empty($message->from)) {
            $message->from(
                config('mail.from.address'),
                config('mail.from.name')
            );
        }

        if (empty($message->tos)) {
            $to = $notifiable->routeNotificationFor('mail');

            // Handle the case where routeNotificationForMail returns an array (email => name)
            if (is_array($to)) {
                reset($to);
                $message->to(key($to), current($to));
            } else {
                $message->to($to);
            }
        }

        if (! ($message instanceof SendGridMessage)) {
            throw new Exception('toSendGrid must return an instance of SendGridMessage.');
        }

        $response = $this->sendGrid->send($message->build());

        if ($response->statusCode() < 200 || $response->statusCode() >= 300) {
            throw CouldNotSendNotification::serviceRespondedWithAnError(
                $response->body()
            );
        }

        return $response;
    }
}
