<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'FP_Client' ) ):

/**
 * Faktur Pro HTTP Client Component
 *
 * @class    FP_Client
 * @version  1.0.0
 * @package  FakturPro\Common
 * @author   Zweischneider
 */
final class FP_Client extends FP_Abstract_Client
{
    /**
     * Download invoice as PDF.
     *
     * Retrieve an invoice given its key. A base64 encoded
     * string of the invoice pdf is returned from the server.
     *
     * @param  string $key
     * @return array<string, mixed>
     */
    public function get_invoice( $key )
    {
        if ( empty( $key ) ) {
            throw new \Exception( "Can't fetch invoice without uuid" );
        }

        return $this->send_get( 'shop/invoices/' . $key );
    }

    /**
     * Create a new invoice from the order data.
     *
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create_invoice( $data )
    {
        return $this->send_post( 'shop/invoices', $data );
    }

    /**
     * Complete an invoice, given its key.
     *
     * @param  string $key
     * @return array<string, mixed>
     */
    public function complete_invoice( $key )
    {
        if ( empty( $key ) ) {
            throw new \Exception( "Can't complete invoice without uuid" );
        }

        return $this->send_put( 'shop/invoices/' . $key . '/complete' );
    }

    /**
     * Cancel an invoice, given its key.
     *
     * @param  string $key
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function cancel_invoice( $key, $data )
    {
        if ( empty( $key ) ) {
            throw new \Exception( "Can't cancel invoice without uuid" );
        }

        return $this->send_put( 'shop/invoices/' . $key . '/cancel', $data );
    }

    /**
     * Refund an invoice, given its it and data.
     *
     * @param  string $key
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function refund_invoice( $key, $data )
    {
        if ( empty( $key ) ) {
            throw new \Exception( "Can't refund invoice without uuid" );
        }

        return $this->send_put( 'shop/invoices/' . $key . '/refund' );
    }
}

endif;
