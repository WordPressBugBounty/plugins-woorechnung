<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'FP_Logger' ) ):

/**
 * Faktur Pro Logger Component following RFC 5424.
 *
 * @class    FP_Logger
 * @version  1.0.0
 * @package  FakturPro\Common
 * @author   Zweischneider
 */
final class FP_Logger extends FP_Abstract_Logger
{
    /**
     * Log that a customer successfully downloaded his invoice.
     *
     * @return void
     */
    public function customer_download_success()
    {
        $this->notice( 'customer successfully downloaded invoice' );
    }

    /**
     * Log that a customer failed to download his invoice.
     *
     * @return void
     */
    public function customer_download_failed()
    {
        $this->error( 'customer invoice download failed' );
    }

    /**
     * Log that the creation of an invoice succeeded.
     *
     * @return void
     */
    public function create_invoice_success()
    {
        $this->notice( 'succeeded to create an invoice' );
    }

    /**
     * Log that the creation of an invoice failed.
     *
     * @return void
     */
    public function create_invoice_failed()
    {
        $this->error( 'failed to create an invoice' );
    }

    /**
     * Log that the download of an invoice succeeded.
     *
     * @return void
     */
    public function fetch_invoice_success()
    {
        $this->notice( 'fetch an invoice success' );
    }

    /**
     * Log that the download of an invoice failed.
     *
     * @return void
     */
    public function fetch_invoice_failed()
    {
        $this->error( 'failed to fetch an invoice' );
    }

    /**
     * Log that the cancellation of an invoice succeeded.
     *
     * @return void
     */
    public function cancel_invoice_success()
    {
        $this->notice( 'succeeded to cancel invoice' );
    }

    /**
     * Log that the cancellation of an invoice failed.
     *
     * @return void
     */
    public function cancel_invoice_failed()
    {
        $this->error( 'failed to cancel invoice' );
    }

    /**
     * Log that an invoice has been mailed successfully.
     *
     * @return void
     */
    public function send_invoice_as_email_success()
    {
        $this->notice( 'succeeded to send an invoice as email' );
    }

    /**
     * Log that an invoice has not been been mailed.
     *
     * @return void
     */
    public function send_invoice_as_email_failed()
    {
        $this->error( 'failed to send an invoice as an email' );
    }

    /**
     * Log that an invoice has been appended successfully.
     *
     * @return void
     */
    public function append_invoice_to_email_success()
    {
        $this->notice( 'succeeded to append invoice to email' );
    }

    /**
     * Log that an invoice has not been appended to email.
     *
     * @return void
     */
    public function append_invoice_to_email_failed()
    {
        $this->error( 'failed to append invoice to email' );
    }


}

endif;
