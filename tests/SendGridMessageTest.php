<?php

namespace NotificationChannels\SendGrid\Test;

use PHPUnit\Framework\TestCase;
use NotificationChannels\SendGrid\SendGridMessage;

class SendGridMessageTest extends TestCase
{
    public function testDynamicVariables()
    {
        $message = new SendGridMessage('template-id');
        $message->payload($payload = [
            'bar' => 'string',
            'baz' => null,
            'foo' => [
                'nested' => 'array',
                'number' => 1235,
                'array' => [1, 2, 3],
            ],
        ]);

        $mail = $message->build();
        $this->assertEquals(
            $payload,
            $mail->getPersonalization()->getDynamicTemplateData()
        );
    }

    public function testSandboxMode()
    {
        $message = new SendGridMessage('template-id');
        $this->assertFalse($message->sandboxMode, 'Sandbox mode is turned off by default');
        $this->assertFalse(
            $message->build()->getMailSettings()->getSandboxMode()->getEnable(),
            'Sandbox mode is disabled in Sendgrid mail settings'
        );

        $message->enableSandboxMode();
        $this->assertTrue($message->sandboxMode, 'Sandbox mode can be enabled');
        $this->assertTrue(
            $message->build()->getMailSettings()->getSandboxMode()->getEnable(),
            'Sandbox mode is enabled in Sendgrid mail settings'
        );

        $message->setSandboxMode(false);
        $this->assertFalse($message->sandboxMode, 'Sandbox mode can be turned off');
        $this->assertFalse(
            $message->build()->getMailSettings()->getSandboxMode()->getEnable(),
            'Sandbox mode is disabled in Sendgrid mail settings'
        );
    }
}
