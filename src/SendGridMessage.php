<?php

namespace NotificationChannels\SendGrid;

use RuntimeException;
use SendGrid\Mail\To;
use SendGrid\Mail\From;
use SendGrid\Mail\Mail;
use SendGrid\Mail\ReplyTo;
use SendGrid\Mail\Attachment;
use Illuminate\Support\Facades\File;

class SendGridMessage
{
    /**
     * The "from" for the message.
     *
     * @var \SendGrid\Mail\From
     */
    public $from;

    /**
     * Recipients of the message.
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
     * The customizations callbacks for SendGrid Mail object.
     *
     * @var array
     */
    private $customizeCallbacks = [];

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
     * Attach a file to the message.
     *
     * array(
     *  'as' => 'name.pdf',
     *  'mime' => 'application/pdf',
     * )
     *
     * @param  string  $file
     * @param  array  $options
     * @return $this
     */
    public function attach($file, array $options = [])
    {
        if (! isset($options['mime'])) {
            $options['mime'] = File::mimeType($file);
        }

        // TODO: Support "Attachable" and "Attachment" types.

        return $this->attachData(
            file_get_contents($file),
            $file,
            $options
        );
    }

    /**
     * Attach in-memory data as an attachment.
     *
     * @param  string  $data
     * @param  string  $name
     * @param  array  $options
     * @return $this
     */
    public function attachData($data, $name, array $options)
    {
        if (! isset($options['mime'])) {
            throw new RuntimeException(
                'Cannot predict mimetype of "' . $name . '". '
                    . 'Provide a valid \'mime\' in $options parameter.'
            );
        }

        $showFilenameAs = isset($options['as'])
            ? $options['as']
            : basename($name);

        $attachment = new Attachment(
            base64_encode($data),
            $options['mime'],
            $showFilenameAs,
            isset($options['inline']) ? 'inline' : 'attachment'
        );

        if (isset($options['inline'])) {
            $attachment->setContentID($showFilenameAs);
        }

        $this->attachments[] = $attachment;

        return $this;
    }

    /**
     * Add inline attachment from a file in the message and get the CID.
     *
     * array(
     *  'as' => 'name.pdf',
     *  'mime' => 'application/pdf',
     * )
     *
     * @param  string  $file
     * @return string
     */
    public function embed($file, array $options = [])
    {
        if (! isset($options['mime'])) {
            $options['mime'] = File::mimeType($file);
        }

        // TODO: Support "Attachable" and "Attachment" types.

        return $this->embedData(
            file_get_contents($file),
            $file,
            $options
        );
    }

    /**
     * Add inline attachments from in-memory data in the message and get the CID.
     *
     * @param  string  $data
     * @param  string  $name
     * @param  string|null  $contentType
     * @return string
     */
    public function embedData($data, $name, array $options)
    {
        $this->attachData($data, $name, array_merge(
            $options,
            ['inline' => true]
        ));

        $lastIndex = count($this->attachments) - 1;

        return "cid:" . $this->attachments[$lastIndex]->getContentID();
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

        foreach ($this->attachments as $attachment) {
            $email->addAttachment($attachment);
        }

        if (count($this->customizeCallbacks)) {
            foreach ($this->customizeCallbacks as $callback) {
                $callback($email);
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

    /**
     * Pass a callback that will be called with the SendGrid message
     * before it is sent. This allows you to fully customize the message using the SendGrid library's API.
     */
    public function customize($callback)
    {
        $this->customizeCallbacks[] = $callback;

        return $this;
    }
}
