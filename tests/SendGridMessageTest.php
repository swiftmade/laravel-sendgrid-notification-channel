<?php

namespace NotificationChannels\SendGrid\Test;

use SendGrid\Mail\Attachment;
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

    public function testAttachmentFromPath()
    {
        $path = __DIR__ . '/fixtures/blank.jpg';

        $message = new SendGridMessage('template-id');
        $message->attach(__DIR__ . '/fixtures/blank.jpg');

        /**
         * @var Attachment
         */
        $attachment = $message->attachments[0];

        // Contents are base64-encoded
        $this->assertEquals(
            base64_encode(file_get_contents($path)),
            $attachment->getContent()
        );

        $this->assertEquals('blank.jpg', $attachment->getFilename());
        $this->assertEquals('image/jpeg', $attachment->getType());
        $this->assertEquals('attachment', $attachment->getDisposition());

        // Let's test the options array.
        $message->attach(__DIR__ . '/fixtures/blank.jpg', [
            'as' => 'custom.png',
            'mime' => 'image/png',
        ]);

        /**
         * @var Attachment
         */
        $attachment2 = $message->attachments[1];
        $this->assertEquals('custom.png', $attachment2->getFilename());
        $this->assertEquals('image/png', $attachment2->getType());
        $this->assertEquals('attachment', $attachment2->getDisposition());
    }

    public function testAttachmentFromData()
    {
        $path = __DIR__ . '/fixtures/blank.jpg';
        $contents = file_get_contents($path);

        $message = new SendGridMessage('template-id');
        $message->attachData($contents, 'blank.jpg', ['mime' => 'image/jpeg']);

        /**
         * @var Attachment
         */
        $attachment = $message->attachments[0];

        // Contents are base64-encoded
        $this->assertEquals(
            base64_encode($contents),
            $attachment->getContent()
        );

        $this->assertEquals('blank.jpg', $attachment->getFilename());
        $this->assertEquals('image/jpeg', $attachment->getType());
        $this->assertEquals('attachment', $attachment->getDisposition());
    }

    public function testEmbeddingFromPath()
    {
        $path = __DIR__ . '/fixtures/blank.jpg';

        $message = new SendGridMessage('template-id');
        $contentId = $message->embed(__DIR__ . '/fixtures/blank.jpg');

        $this->assertEquals('cid:blank.jpg', $contentId);

        /**
         * @var Attachment
         */
        $attachment = $message->attachments[0];

        // Contents are base64-encoded
        $this->assertEquals(
            base64_encode(file_get_contents($path)),
            $attachment->getContent()
        );

        $this->assertEquals('blank.jpg', $attachment->getFilename());
        $this->assertEquals('image/jpeg', $attachment->getType());
        $this->assertEquals('inline', $attachment->getDisposition());
        $this->assertEquals('blank.jpg', $attachment->getContentID());
    }

    public function testEmbeddingFromData()
    {
        $path = __DIR__ . '/fixtures/blank.jpg';
        $contents = file_get_contents($path);

        $message = new SendGridMessage('template-id');
        $contentId = $message->embedData($contents, 'blank.png', ['mime' => 'image/png']);

        $this->assertEquals('cid:blank.png', $contentId);

        /**
         * @var Attachment
         */
        $attachment = $message->attachments[0];

        // Contents are base64-encoded
        $this->assertEquals(
            base64_encode(file_get_contents($path)),
            $attachment->getContent()
        );

        $this->assertEquals('blank.png', $attachment->getFilename());
        $this->assertEquals('image/png', $attachment->getType());
        $this->assertEquals('inline', $attachment->getDisposition());
        $this->assertEquals('blank.png', $attachment->getContentID());
    }

    public function testCustomizeCallback()
    {
        $message = new SendGridMessage('template-id');
        $mailRef = null;

        $message->customize(function ($mail) use (&$mailRef) {
            $mailRef = $mail;
        });

        $message->build();
        $this->assertInstanceOf(\SendGrid\Mail\Mail::class, $mailRef);
    }
}
