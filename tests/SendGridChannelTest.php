<?php

namespace NotificationChannels\SendGrid\Test;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Mockery;
use NotificationChannels\SendGrid\SendGridChannel;
use NotificationChannels\SendGrid\SendGridMessage;
use PHPUnit\Framework\TestCase;
use SendGrid;

class SendGridChannelTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testEmailIsSentViaSendGrid()
    {
        $notification = new NotificationSendGridChannelTestNotification;
        $notifiable = new NotificationSendGridChannelTestNotifiable;

        $channel = new SendGridChannel(
            $sendgrid = Mockery::mock(new SendGrid('x'))
        );

        $message = $notification->toSendGrid($notifiable);

        $this->assertEquals($message->templateId, 'sendgrid-template-id');
        $this->assertEquals($message->from->getEmail(), 'test@example.com');
        $this->assertEquals($message->from->getName(), 'Example User');
        $this->assertEquals($message->tos[0]->getEmail(), 'test+test1@example.com');
        $this->assertEquals($message->tos[0]->getName(), 'Example User1');
        $this->assertEquals($message->payload['bar'], 'foo');
        $this->assertEquals($message->payload['baz'], 'foo2');

        // TODO: Verify that the Mail instance passed contains all the info from above
        $sendgrid->shouldReceive('send')->once();

        $channel->send($notifiable, $notification);
    }
}

class NotificationSendGridChannelTestNotifiable
{
    use Notifiable;
}

class NotificationSendGridChannelTestNotification extends Notification
{
    public function toSendGrid($notifiable)
    {
        return (new SendGridMessage('sendgrid-template-id'))
            ->from('test@example.com', 'Example User')
            ->to('test+test1@example.com', 'Example User1')
            ->payload([
                'bar' => 'foo',
                'baz' => 'foo2',
            ]);
    }
}
