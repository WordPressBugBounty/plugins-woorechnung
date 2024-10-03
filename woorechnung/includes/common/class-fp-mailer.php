<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Mailer') ):

/**
 * Faktur Pro Mailer Class.
 *
 * This class provides an interface to the mail functionality.
 * When the shop admin wants invoices to be sent to the customer
 * via email, this class is being used to configure and send
 * the mail.
 *
 * @version  1.0.0
 * @package  FakturPro\Common
 * @author   Zweischneider
 */
final class FP_Mailer
{
    /**
     * The header for plain text emails.
     *
     * @var string HEADER_TEXT_PLAIN
     */
    const HEADER_TEXT_PLAIN = 'Content-Type: text/plain; charset=UTF-8';

    /**
     * The header for emails with html content.
     *
     * @var string HEADER_TEXT_HTML
     */
    const HEADER_TEXT_HTML = 'Content-Type: text/html; charset=UTF-8';

    /**
     * The headers to use for the mail transfer.
     *
     * @var array<string> $headers
     */
    private $headers = array();

    /**
     * The recipients to send the email to.
     *
     * @var array<string> $recipients
     */
    private $recipients = array();

    /**
     * The file attachments of the outgoing email.
     *
     * @var array<string> $attachments
     */
    private $attachments = array();

    /**
     * The subject of the outgoing email.
     *
     * @var string|null $subject
     */
    private $subject;

    /**
     * The actual contents of the outgoing mail.
     *
     * @var string|null $contents
     */
    private $contents;

    /**
     * Create a new instance of this mailer class.
     *
     * @param  FP_Plugin $plugin
     */
    public function __construct( FP_Plugin $plugin )
    {
        $this->clear();
    }

    /**
     * Send a mail using the parameters configured using wp_mail.
     *
     * @return void
     */
    public function send_email()
    {
        $this->send();
        $this->clear();
    }

    /**
     * Clear all attributes previously configured.
     *
     * @return void
     */
    private function clear()
    {
        $this->headers = array( self::HEADER_TEXT_HTML );
        $this->recipients = array();
        $this->attachments = array();
        $this->subject = null;
        $this->contents = null;
    }

    /**
     * Send the email with the configured attributes.
     *
     * @return void
     */
    private function send()
    {
        wp_mail(
            $this->recipients,
            $this->subject,
            $this->contents,
            $this->headers,
            $this->attachments
        );
    }

    /**
     * Set the headers for the outgoing mail.
     *
     * @param  array<string>|null $headers
     * @return void
     */
    public function set_headers( $headers )
    {
        $this->headers = is_array( $headers ) ? $headers : array();
    }

    /**
     * Add a header to the outgoing mail.
     *
     * @param  string $header
     * @return void
     */
    public function add_header( $header )
    {
        $this->headers[] = $header;
    }

    /**
     * Set the default headers for outgoing mails.
     *
     * @return void
     */
    public function set_default_header()
    {
        $this->add_header( self::HEADER_TEXT_HTML );
    }

    /**
     * Add a recipient to the outgoing mail.
     *
     * @param  string $recipient
     * @return void
     */
    public function add_recipient( $recipient )
    {
        $this->recipients[] = $recipient;
    }

    /**
     * Add multiple recipients to the outgoing mail.
     *
     * @param  array<string> $recipients
     * @return void
     */
    public function add_recipients( $recipients )
    {
        $this->recipients = array_merge( $this->recipients, $recipients );
    }

    /**
     * Add multiple carbon copy recipients to the outgoing mail.
     *
     * @param  array<string> $recipients
     * @return void
     */
    public function add_copy_recipients( $recipients )
    {
        foreach ( $recipients as $recipient ) {
            $this->add_header( 'Cc: ' . $recipient );
        }
    }

    /**
     * Add multiple blind carbon copy recipients to the outgoing mail.
     *
     * @param  array<string> $recipients
     * @return void
     */
    public function add_blind_copy_recipients( $recipients )
    {
        foreach ( $recipients as $recipient ) {
            $this->add_header( 'Bcc: ' . $recipient );
        }
    }

    /**
     * Attach a file to the outgoing mail.
     *
     * @param  string $attachment
     * @return void
     */
    public function add_attachment( $attachment )
    {
        $this->attachments[] = $attachment;
    }

    /**
     * Set the subject of the outgoing mail.
     *
     * @param  string $subject
     * @return void
     */
    public function set_subject( $subject )
    {
        $this->subject = $subject;
    }

    /**
     * Set the contents of the outgoing mail.
     *
     * @param  string $contents
     * @return void
     */
    public function set_contents( $contents )
    {
        $this->contents = $contents;
    }
}

endif;
