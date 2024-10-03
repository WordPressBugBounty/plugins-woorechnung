<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Migration_2_0_0_Refactoring' ) ):

final class FP_Migration_2_0_0_Refactoring
{
    /**
     * The plugin update object
     *
     * @var FP_Plugin_Update
     */
    private $update;

    /**
     * The old settings that are potentially in database.
     *
     * @var array<string, mixed>
     */
    private $_old_settings = array();

    /**
     * Create a new instance of this migration.
     *
     * @param  FP_Plugin_Update $update
     * @return void
     */
    public function __construct(FP_Plugin_Update $update)
    {
        $this->update = $update;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!$this->is_updated()) {
            $this->update_plugin_settings();
        }
    }

    /**
     * Update the plugin settings from version 1 to version 2.
     *
     * @return void
     */
    public function update_plugin_settings()
    {
        $this->load_old_settings();
        $this->update_general_settings();
        $this->update_invoice_settings();
        $this->update_mailing_settings();
        $this->mark_as_updated();
    }

    /**
     * Load the old plugin settings from database.
     *
     * @return void
     */
    private function load_old_settings()
    {
        $this->load_settings_section('woo_invoices_plugin_settings');
        $this->load_settings_section('woo_invoices_plugin_settings_invoice');
        $this->load_settings_section('woo_invoices_plugin_settings_invoice_mail');
    }

    /**
     * Retrive an old setting section by its key.
     *
     * @param  string $key
     * @return mixed
     */
    private function load_settings_section( $key )
    {
        $this->_old_settings = array_merge($this->_old_settings, get_option( $key, array() ) );
    }

    /**
     * Retrive an old setting by its key.
     *
     * @param  string $key
     * @return mixed
     */
    private function load_setting($key)
    {
        return isset($this->_old_settings[$key]) ? $this->_old_settings[$key] : null;
    }

    /**
     * Update the general settings of the plugin.
     *
     * @return void
     */
    private function update_general_settings()
    {
        $shop_url = get_home_url();
        $shop_token = $this->load_setting( 'woo_invoices_api_key' );

        $new_settings = $this->update->settings();
        $new_settings->set_shop_url( $shop_url );
        $new_settings->set_shop_token( $shop_token );
    }

    /**
     * Update the plugin invoice settings.
     *
     * @return void
     */
    private function update_invoice_settings()
    {
        $invoice_inactive = $this->load_setting('woo_invoices_deactivate');
        $invoice_status = $this->load_setting('woo_invoices_status');
        $invoice_zero = $this->load_setting('woo_zero_invoices');
        $invoice_open = $this->load_setting('woo_open_invoice');
        $line_description = $this->load_setting('woo_line_description');
        $shipping_code = $this->load_setting('woo_invoice_shipping_code');
        $order_prefix = $this->load_setting('woo_order_number_prefix');
        $order_suffix = $this->load_setting('woo_order_number_suffix');
        $line_description = $this->load_setting('woo_line_description');
        $payment_methods = $this->load_setting('woo_payment_methods');

        $new_settings = $this->update->settings();
        $new_settings->set_create_invoices( ! $invoice_inactive );
        $new_settings->set_invoice_for_states( $invoice_status );
        $new_settings->set_cancel_invoices( true );
        $new_settings->set_customer_link( true );
        $new_settings->set_zero_value_invoices( $invoice_zero );
        $new_settings->set_open_invoices( $invoice_open );
        $new_settings->set_line_description( $line_description );
        $new_settings->set_article_number_shipping( $shipping_code );
        $new_settings->set_order_number_prefix( $order_prefix );
        $new_settings->set_order_number_suffix( $order_suffix );

        // Payment Methods
        $gateways = WC()->payment_gateways()->get_available_payment_gateways();
        $gateways = !empty( $gateways ) ? $gateways : array();
        $methods = array();

        foreach ( $gateways as $gateway ) {
            $gateway_id = $gateway->id;
            $gateway_name = $gateway->get_title();
            $methods[ $gateway_name ] = $gateway_id;
        }

        $paid_methods = array();
        foreach ( $this->_old_settings as $old_setting => $status ) {
            if ( strpos( $old_setting, 'woo_payment_methods_' ) !== false ) {
                $method = str_replace( 'woo_payment_methods_', '', $old_setting );
                if ( $status == 'payed' ) {
                    $paid_methods[] = $methods[ $method ];
                }
            }
        }

        $new_settings->set_paid_for_methods($paid_methods);
    }

    /**
     * Update the plugin mailing settings.
     *
     * @return void
     */
    private function update_mailing_settings()
    {
        $mailing_active  = $this->load_setting('woo_send_invoices');
        $mailing_attach = $this->load_setting('woo_send_invoices_type');
        $mailing_status = $this->load_setting('woo_sending_status');
        $mailing_subject = $this->load_setting('woo_invoice_mail_subject');
        $mailing_text = $this->load_setting('woo_invoice_mail_body_text');
        $mailing_html = $this->load_setting('woo_invoice_mail_body_html');

        $mailing_config = 'none';
        $mailing_config = $mailing_active ? 'separate' : $mailing_config;
        $mailing_config = $mailing_attach ? 'append' : $mailing_config;

        $new_settings = $this->update->settings();
        $new_settings->set_invoice_email( $mailing_config );
        $new_settings->set_email_for_states( $mailing_status );
        $new_settings->set_email_filename( 'Rechnung' );
        $new_settings->set_email_subject( $mailing_subject );
        $new_settings->set_email_content_text( $mailing_text );
        $new_settings->set_email_content_html( $mailing_html );
    }

    /**
     * Decide if the plugin settings have been updated.
     *
     * @return bool
     */
    private function is_updated()
    {
        return get_option('woorechnung_updated_2_0_0', 'no') == 'yes';
    }

    /**
     * Mark the plugin settings as updated.
     *
     * @return mixed
     */
    private function mark_as_updated()
    {
        return add_option('woorechnung_updated_2_0_0', 'yes');
    }
}

endif;
