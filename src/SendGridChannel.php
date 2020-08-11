<?php

namespace NotificationChannels\SendGrid;

use Exception;
use Illuminate\Notifications\Notification;
use NotificationChannels\SendGrid\Exceptions\CouldNotSendNotification;
use SendGrid;

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

        $message = $notification->toSendGrid($notifiable);

        if (! ($message instanceof SendGridMessage)) {
            throw new Exception('toSendGrid must return an instance of SendGridMessage.');
        }

        try {
            $this->sendGrid->send($message->build());
        } catch (Exception $e) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($e->getMessage());
        }
    }
}
