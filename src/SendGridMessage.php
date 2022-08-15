<?php

namespace NotificationChannels\SendGrid;

use SendGrid\Mail\To;
use SendGrid\Mail\From;
use SendGrid\Mail\Mail;
use SendGrid\Mail\ReplyTo;

class SendGridMessage
{
    /**
     * The "from" for the message.
     *
     * @var \SendGrid\Mail\From
     */
    public $from;

    /**
     * The "tos" for the message.
     *
     * @var array
     */
    public $tos = [];

    /**
     * The reply to address for the message.
     */
    public $replyTo;

    /**
     * The SendGrid Template ID for the message.
     *
     * @var string
     */
    public $templateId;

    /**
     * The SendGrid Template vars for the message.
     *
     * @var array
     */
    public $payload = [];

    /**
     * The sandbox mode for SendGrid
     *
     * @var bool
     */
    public $sandboxMode = false;

    /**
     * Create a new SendGrid channel instance.
     *
     * @param  string  $templateId
     * @return void
     */
    public function __construct($templateId)
    {
        $this->templateId = $templateId;
    }

    /**
     * Set the "from".
     *
     * @param  string  $email
     * @param  string  $name
     * @return $this
     */
    public function from($email, $name)
    {
        $this->from = new From($email, $name);

        return $this;
    }

    /**
     * Set the "tos".
     *
     * @param  string  $email
     * @param  string  $name
     * @param  array  $data
     * @return $this
     */
    public function to($email, $name = null, $data = [])
    {
        $this->tos = array_merge($this->tos, [new To($email, $name, $data)]);

        return $this;
    }

    public function replyTo($email, $name = null)
    {
        $this->replyTo = new ReplyTo($email, $name);

        return $this;
    }

    public function payload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * @return Mail
     */
    public function build(): Mail
    {
        $email = new Mail(
            $this->from,
            $this->tos
        );

        $email->setReplyTo($this->replyTo);

        $email->setTemplateId($this->templateId);

        if ($this->sandboxMode) {
            $email->enableSandBoxMode();
        }

        foreach ($this->payload as $key => $value) {
            $email->addDynamicTemplateData((string) $key, (string) $value);
        }

        return $email;
    }

    /**
     * Enabling sandbox mode allows you to send a test email to
     * ensure that your request body is formatted correctly
     * without delivering the email to any of your recipients.
     *
     * @see https://docs.sendgrid.com/for-developers/sending-email/sandbox-mode
     * @return $this
     */
    public function enableSandboxMode( $enabled = true)
    {
        $this->sandboxMode = $enabled;

        return $this;
    }
}
