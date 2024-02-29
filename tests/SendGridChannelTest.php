<?php

namespace NotificationChannels\SendGrid\Test;

use Mockery;
use SendGrid;
use SendGrid\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use NotificationChannels\SendGrid\SendGridChannel;
use NotificationChannels\SendGrid\SendGridMessage;
use Illuminate\Notifications\Events\NotificationSent;

class SendGridChannelTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testEmailIsSentViaSendGrid()
    {
        Event::fake();

        $notification = new class extends Notification {
            public function via()
            {
                return [SendGridChannel::class];
            }

            public function toSendGrid($notifiable)
            {
                return (new SendGridMessage('sendgrid-template-id'))
                    ->from('test@example.com', 'Example User')
                    ->to('test+test1@example.com', 'Example User1')
                    ->replyTo('replyto@example.com', 'Reply To')
                    ->payload([
                        'bar' => 'foo',
                        'baz' => 'foo2',
                    ]);
            }
        };

        $notifiable = new class {
            use Notifiable;
        };

        $channel = new SendGridChannel(
            $this->mockSendgrid()
        );

        $this->app->instance(SendGridChannel::class, $channel);

        $message = $notification->toSendGrid($notifiable);

        $this->assertEquals($message->templateId, 'sendgrid-template-id');
        $this->assertEquals($message->from->getEmail(), 'test@example.com');
        $this->assertEquals($message->from->getName(), 'Example User');
        $this->assertEquals($message->tos[0]->getEmail(), 'test+test1@example.com');
        $this->assertEquals($message->tos[0]->getName(), 'Example User1');
        $this->assertEquals($message->payload['bar'], 'foo');
        $this->assertEquals($message->payload['baz'], 'foo2');
        $this->assertEquals($message->replyTo->getEmail(), 'replyto@example.com');
        $this->assertEquals($message->replyTo->getName(), 'Reply To');
        $this->assertEquals($message->sandboxMode, false);

        $notifiable->notify($notification);

        Event::assertDispatched(
            NotificationSent::class,
            fn ($event) => $event->channel === SendGridChannel::class
        );

        Event::assertDispatched(
            NotificationSent::class,
            fn ($event) => $event->response instanceof Response
        );
    }

    public function testDefaultToAddress()
    {
        Event::fake();

        $channel = new SendGridChannel($this->mockSendgrid());

        $notification = new class extends Notification {
            public $sendgridMessage;

            public function via()
            {
                return [SendGridChannel::class];
            }

            public function toSendGrid($notifiable)
            {
                $this->sendgridMessage = (new SendGridMessage('sendgrid-template-id'))
                    ->from('test@example.com', 'Example User')
                    ->replyTo('replyto@example.com', 'Reply To')
                    ->payload([
                        'bar' => 'foo',
                        'baz' => 'foo2',
                    ]);

                return $this->sendgridMessage;
            }
        };

        $notifiable = new class {
            use Notifiable;

            public function routeNotificationForMail($notification)
            {
                return 'john@example.com';
            }
        };

        $channel->send($notifiable, $notification);
        $message = $notification->sendgridMessage;
        $this->assertEquals($message->tos[0]->getEmail(), 'john@example.com');

        // Let's also support returning an array (email => name)
        // https://laravel.com/docs/10.x/notifications#customizing-the-recipient
        $notifiableWithEmailAndName = new class {
            use Notifiable;

            public function routeNotificationForMail($notification)
            {
                return [
                    'john@example.com' => 'John Doe',
                ];
            }
        };

        $channel->send($notifiableWithEmailAndName, $notification);
        $message = $notification->sendgridMessage;

        $this->assertEquals($message->tos[0]->getEmail(), 'john@example.com');
        $this->assertEquals($message->tos[0]->getName(), 'John Doe');
    }

    private function mockSendgrid($statusCode = 200)
    {
        $response = Mockery::mock(Response::class);
        $response->shouldReceive('statusCode')->andReturn($statusCode);

        $sendgrid = Mockery::mock(new SendGrid('x'));
        $sendgrid->shouldReceive('send')->andReturn($response);

        return $sendgrid;
    }
}
