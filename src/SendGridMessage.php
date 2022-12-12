<?php

namespace NotificationChannels\SendGrid;

use SendGrid\Mail\To;
use SendGrid\Mail\From;
use SendGrid\Mail\Mail;
use SendGrid\Mail\ReplyTo;
use SendGrid\Mail\Attachment;

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
     * An array of attachments for the message.
     *
     * @var array
     */
    public $attachments = [];

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

    public function attach($attachments)
    {
        /*
        $attachments should be an array of individual attachments. content should be base64 encoded.

        Example:
        $attachments = array(
            array(
                'content' => base64_encode($content),
                'type' => 'application/pdf',
                'filename' => 'filename.pdf'
            )
        );
        */

        $this->attachments = $attachments;

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

        $this->sandboxMode
            ? $email->enableSandBoxMode()
            : $email->disableSandBoxMode();

        foreach ($this->payload as $key => $value) {
            $email->addDynamicTemplateData((string) $key, $value);
        }

        if (is_array($this->attachments) && !empty($this->attachments)) {
            foreach ($this->attachments as $attachment) {
                $disposition = (isset($attachment['disposition'])) ? strtolower($attachment['disposition']) : "attachment";

                $sgAttachment = new Attachment();
                $sgAttachment->setType($attachment['type']);
                $sgAttachment->setContent($attachment['content']);
                $sgAttachment->setDisposition($disposition);
                $sgAttachment->setFilename($attachment['filename']);

                if ($disposition === "inline") {
                    $sgAttachment->setContentID($attachment['filename']);
                }

                $email->addAttachment($sgAttachment);
            }
        }

        return $email;
    }

    /**
     * @param bool $sandboxMode
     */
    public function setSandboxMode($sandboxMode)
    {
        $this->sandboxMode = $sandboxMode;

        return $this;
    }

    /**
     * Enabling sandbox mode allows you to send a test email to
     * ensure that your request body is formatted correctly
     * without delivering the email to any of your recipients.
     *
     * @see https://docs.sendgrid.com/for-developers/sending-email/sandbox-mode
     * @return $this
     */
    public function enableSandboxMode()
    {
        return $this->setSandboxMode(true);
    }
}
