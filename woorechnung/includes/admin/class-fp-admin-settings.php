<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Admin_Settings' ) ):

/**
 * Faktur Pro Admin Settings Component
 *
 * This class loads the settings page for the plugins admin backend.
 * The settings are to be set by the site admin in order to prepare the
 * plugin for work.
 *
 * @version  1.0.0
 * @package  FakturPro\Admin
 * @author   Zweischneider
 */
final class FP_Admin_Settings extends FP_Abstract_Module
{
    /**
     * @var string
     */
    private static $field_error_css = 'color:#000000; border-color:#ff0000; background-color:#ffcccc;';

    /**
     * @var array<string>
     */
    private $error_fields = array();

    /**
     * Initialize the hooks of this module.
     *
     * @return void
     */
    public function init_hooks()
    {
        if ( is_admin() ) {
            $this->add_filter( 'plugin_action_links_' . plugin_basename( $this->_plugin->get_file() ), 'plugin_action_links' );
            $this->add_filter( 'woocommerce_settings_tabs_array', 'add_settings_tab', 50 );
            $this->add_action( 'woocommerce_settings_tabs_fakturpro', 'add_settings' );
            $this->add_action( 'woocommerce_update_options_fakturpro', 'update_settings' );
            $this->add_action( 'woocommerce_admin_field_description', 'output_description', 10, 1 );
            $this->add_filter( 'option_fakturpro_email_content_html', 'html_content_filter', 10, 2 );
        }
    }

    /**
     * Show action links on the plugin screen
     *
     * @param mixed $links
     *
     * @return array<string>
     */
    public function plugin_action_links($links)
    {
        return array_merge( array(
            '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=fakturpro' ) ) . '">Einstellungen</a>',
        ), $links );
    }

    /**
     * Add the settings tab to the WooCommerce settings tabs.
     *
     * @param  array<string, string>|null $settings_tabs
     * @return array<string, string>
     */
    public function add_settings_tab( $settings_tabs )
    {
        $settings_tabs = is_array( $settings_tabs ) ? $settings_tabs : array();
        $settings_tabs['fakturpro'] = 'Faktur Pro';
        return $settings_tabs;
    }

    /**
     * Add the settings fields to the WooCommerce tab.
     *
     * @return void
     */
    public function add_settings()
    {
        woocommerce_admin_fields( $this->get_settings() );
    }

    /**
     * Validates the value and returns it if all is allowed otherwise it returns false.
     *
     * @param  string $value
     * @return string|false
     */
    public function validate_allowed_chars( $value )
    {
        return preg_match('/[\$\/\"\'\\\\]+/i', $value) === 0 ? $value : false;
    }

    /**
     * Get field css.
     *
     * @param  string $field_id
     * @return string|null
     */
    public function field_css($field_id)
    {
        return in_array($field_id, $this->error_fields) ? self::$field_error_css : null;
    }

    /**
     * Update the settings with the current values.
     *
     * @return void
     */
    public function update_settings()
    {
        $this->error_fields = array();
        $data = array();

        if ( isset( $_POST['fakturpro_shop_url'] ) ) {
            $fakturpro_shop_url = sanitize_url( $_POST['fakturpro_shop_url'] );
            if ( $fakturpro_shop_url = wp_http_validate_url( $fakturpro_shop_url ) ? $fakturpro_shop_url : false ) {
                $data['fakturpro_shop_url'] = $fakturpro_shop_url;
            } else {
                $this->error_fields[] = 'fakturpro_shop_url';
            }
        }

        if ( isset( $_POST['fakturpro_shop_token'] ) ) {
            $fakturpro_shop_token = sanitize_text_field( $_POST['fakturpro_shop_token'] );
            if ( $fakturpro_shop_token = preg_match('/^[0-9a-z]{32}$/i', $fakturpro_shop_token) === 1 ? $fakturpro_shop_token : false ) {
                $data['fakturpro_shop_token'] = $fakturpro_shop_token;
            } else {
                $this->error_fields[] = 'fakturpro_shop_token';
            }
        }

        if ( isset( $_POST['fakturpro_send_error_mails_to'] ) ) {
            $fakturpro_send_error_mails_to = sanitize_key( $_POST['fakturpro_send_error_mails_to'] );
            $fakturpro_send_error_mails_to = in_array( $fakturpro_send_error_mails_to, ['admin', 'user'] ) ? $fakturpro_send_error_mails_to : 'admin';
            $data['fakturpro_send_error_mails_to'] = $fakturpro_send_error_mails_to;
        }


        if (isset($_POST['fakturpro_customer_vat_id_meta_name']) ) {
            $fakturpro_customer_vat_id_meta_name = sanitize_text_field( $_POST['fakturpro_customer_vat_id_meta_name'] );
            if ( empty( $fakturpro_customer_vat_id_meta_name ) || $fakturpro_customer_vat_id_meta_name = $this->validate_allowed_chars( $fakturpro_customer_vat_id_meta_name ) ) {
                $data['fakturpro_customer_vat_id_meta_name'] = $fakturpro_customer_vat_id_meta_name;
            } else {
                $this->error_fields[] = 'fakturpro_customer_vat_id_meta_name';
            }
        }


        if ( isset( $_POST['fakturpro_create_invoices'] ) ) {
            $fakturpro_create_invoices = strval( absint( $_POST['fakturpro_create_invoices'] ) );
            $fakturpro_create_invoices = in_array( $fakturpro_create_invoices, ['0', '1']) ? $fakturpro_create_invoices : '1';
            $data['fakturpro_create_invoices'] = $fakturpro_create_invoices;
        }

        if ( isset( $_POST['fakturpro_invoice_for_states'] ) ) {
            $fakturpro_invoice_for_states = array_map(function ($status) { return sanitize_key( $status ); }, $_POST['fakturpro_invoice_for_states'] );
            $fakturpro_invoice_for_states = array_filter($fakturpro_invoice_for_states, function ($status) { return in_array($status, array_keys( $this->get_order_states() )); });
            $data['fakturpro_invoice_for_states'] = $fakturpro_invoice_for_states;
        }

        if ( isset( $_POST['fakturpro_no_invoice_for_methods'] ) ) {
            $fakturpro_no_invoice_for_methods = array_map(function ($method) { return sanitize_key( $method ); }, $_POST['fakturpro_no_invoice_for_methods'] );
            $fakturpro_no_invoice_for_methods = array_filter($fakturpro_no_invoice_for_methods, function ($method) { return in_array($method, array_keys( $this->get_payment_methods() )); });
            $data['fakturpro_no_invoice_for_methods'] = $fakturpro_no_invoice_for_methods;
        }

        if ( isset( $_POST['fakturpro_paid_for_methods'] ) ) {
            $fakturpro_paid_for_methods = array_map(function ($method) { return sanitize_key( $method ); }, $_POST['fakturpro_paid_for_methods'] );
            $fakturpro_paid_for_methods = array_filter($fakturpro_paid_for_methods, function ($method) { return in_array($method, array_keys( $this->get_payment_methods() )); });
            $data['fakturpro_paid_for_methods'] = $fakturpro_paid_for_methods;
        }

        if ( isset( $_POST['fakturpro_zero_value_invoices'] ) ) {
            $fakturpro_zero_value_invoices = strval( absint( $_POST['fakturpro_zero_value_invoices'] ) );
            $fakturpro_zero_value_invoices = in_array( $fakturpro_zero_value_invoices, ['0', '1']) ? $fakturpro_zero_value_invoices : '0';
            $data['fakturpro_zero_value_invoices'] = $fakturpro_zero_value_invoices;
        }

        if ( isset( $_POST['fakturpro_cancel_invoices'] ) ) {
            $fakturpro_cancel_invoices = strval( absint( $_POST['fakturpro_cancel_invoices'] ) );
            $fakturpro_cancel_invoices = in_array( $fakturpro_cancel_invoices, ['0', '1']) ? $fakturpro_cancel_invoices : '0';
            $data['fakturpro_cancel_invoices'] = $fakturpro_cancel_invoices;
        }

        if ( isset( $_POST['fakturpro_open_invoices'] ) ) {
            $fakturpro_open_invoices = strval( absint( $_POST['fakturpro_open_invoices'] ) );
            $fakturpro_open_invoices = in_array( $fakturpro_open_invoices, ['0', '1']) ? $fakturpro_open_invoices : '0';
            $data['fakturpro_open_invoices'] = $fakturpro_open_invoices;
        }

        if ( isset( $_POST['fakturpro_customer_link'] ) ) {
            $fakturpro_customer_link = strval( absint( $_POST['fakturpro_customer_link'] ) );
            $fakturpro_customer_link = in_array( $fakturpro_customer_link, ['0', '1']) ? $fakturpro_customer_link : '0';
            $data['fakturpro_customer_link'] = $fakturpro_customer_link;
        }

        if ( isset( $_POST['fakturpro_merge_credits'] ) ) {
            $fakturpro_merge_credits = strval( absint( $_POST['fakturpro_merge_credits'] ) );
            $fakturpro_merge_credits = in_array( $fakturpro_merge_credits, ['0', '1']) ? $fakturpro_merge_credits : '0';
            $data['fakturpro_merge_credits'] = $fakturpro_merge_credits;
        }

        if ( isset( $_POST['fakturpro_line_name'] ) ) {
            $fakturpro_line_name = sanitize_key( $_POST['fakturpro_line_name'] );
            $fakturpro_line_name = in_array( $fakturpro_line_name, array_keys( $this->get_line_names() ) ) ? $fakturpro_line_name : '';
            $data['fakturpro_line_name'] = $fakturpro_line_name;
        }

        if ( isset( $_POST['fakturpro_line_description'] ) ) {
            $fakturpro_line_description = sanitize_key( $_POST['fakturpro_line_description'] );
            $fakturpro_line_description = in_array( $fakturpro_line_description, array_keys( $this->get_line_descriptions() ) ) ? $fakturpro_line_description : '';
            $data['fakturpro_line_description'] = $fakturpro_line_description;
        }

        if ( isset( $_POST['fakturpro_price_num_decimals'] ) ) {
            $fakturpro_price_num_decimals = max( absint( $_POST['fakturpro_price_num_decimals'] ), 0);
            $data['fakturpro_price_num_decimals'] = $fakturpro_price_num_decimals;
        }

        if ( isset( $_POST['fakturpro_article_name_shipping'] ) ) {
            $fakturpro_article_name_shipping = sanitize_text_field( $_POST['fakturpro_article_name_shipping'] );
            if ( empty( $fakturpro_article_name_shipping ) || $fakturpro_article_name_shipping = $this->validate_allowed_chars( $fakturpro_article_name_shipping ) ) {
                $data['fakturpro_article_name_shipping'] = $fakturpro_article_name_shipping;
            } else {
                $this->error_fields[] = 'fakturpro_article_name_shipping';
            }
        }

        if ( isset( $_POST['fakturpro_article_number_shipping'] ) && !empty( $_POST['fakturpro_article_number_shipping'] ) ) {
            $fakturpro_article_number_shipping = sanitize_text_field( $_POST['fakturpro_article_number_shipping'] );
            if ( $fakturpro_article_number_shipping = preg_match('/^[0-9a-z _\-]*$/i', $fakturpro_article_number_shipping ) === 1 ? $fakturpro_article_number_shipping : false ) {
                $data['fakturpro_article_number_shipping'] = $fakturpro_article_number_shipping;
            } else {
                $this->error_fields[] = 'fakturpro_article_number_shipping';
            }
        }

        if ( isset( $_POST['fakturpro_order_number_prefix'] ) && !empty( $_POST['fakturpro_order_number_prefix'] ) ) {
            $fakturpro_order_number_prefix = sanitize_text_field( $_POST['fakturpro_order_number_prefix'] );
            if ( empty( $fakturpro_order_number_prefix ) || $fakturpro_order_number_prefix = $this->validate_allowed_chars( $fakturpro_order_number_prefix ) ) {
                $data['fakturpro_order_number_prefix'] = $fakturpro_order_number_prefix;
            } else {
                $this->error_fields[] = 'fakturpro_order_number_prefix';
            }
        }

        if ( isset( $_POST['fakturpro_order_number_suffix'] ) && !empty( $_POST['fakturpro_order_number_suffix'] ) ) {
            $fakturpro_order_number_suffix = sanitize_text_field( $_POST['fakturpro_order_number_suffix'] );
            if ( empty( $fakturpro_order_number_suffix ) || $fakturpro_order_number_suffix = $this->validate_allowed_chars( $fakturpro_order_number_suffix ) ) {
                $data['fakturpro_order_number_suffix'] = $fakturpro_order_number_suffix;
            } else {
                $this->error_fields[] = 'fakturpro_order_number_suffix';
            }
        }

        if ( isset( $_POST['fakturpro_email_filename'] ) ) {
            $fakturpro_email_filename = sanitize_text_field( $_POST['fakturpro_email_filename'] );
            if ( $fakturpro_email_filename = $this->validate_allowed_chars( $fakturpro_email_filename ) ) {
                $data['fakturpro_email_filename'] = $fakturpro_email_filename;
            } else {
                $this->error_fields[] = 'fakturpro_email_filename';
            }
        }

        if ( isset( $_POST['fakturpro_invoice_email'] ) ) {
            $fakturpro_invoice_email = sanitize_key( $_POST['fakturpro_invoice_email'] );
            $fakturpro_invoice_email = in_array($fakturpro_invoice_email, array_keys( $this->get_invoice_email_options() ) ) ? $fakturpro_invoice_email : 'append';
            $data['fakturpro_invoice_email'] = $fakturpro_invoice_email;
        }

        if ( isset( $_POST['fakturpro_email_to_append_to'] ) ) {
            $fakturpro_email_to_append_to = array_map(function ($type) { return sanitize_key( $type ); }, $_POST['fakturpro_email_to_append_to'] );
            $fakturpro_email_to_append_to = array_filter($fakturpro_email_to_append_to, function ($type) { return in_array($type, array_keys( $this->get_email_types() )); });
            $data['fakturpro_email_to_append_to'] = $fakturpro_email_to_append_to;
        }

        if ( isset( $_POST['fakturpro_email_for_states'] ) ) {
            $fakturpro_email_for_states = array_map(function ($type) { return sanitize_key( $type ); }, $_POST['fakturpro_email_for_states'] );
            $fakturpro_email_for_states = array_filter($fakturpro_email_for_states, function ($state) { return in_array($state, array_keys( $this->get_order_states() )); });
            $data['fakturpro_email_for_states'] = $fakturpro_email_for_states;
        }

        if ( isset( $_POST['fakturpro_no_email_for_methods'] ) ) {
            $fakturpro_no_email_for_methods = array_map(function ($method) { return sanitize_key( $method ); }, $_POST['fakturpro_no_email_for_methods'] );
            $fakturpro_no_email_for_methods = array_filter($fakturpro_no_email_for_methods, function ($method) { return in_array($method, array_keys( $this->get_payment_methods() )); });
            $data['fakturpro_no_email_for_methods'] = $fakturpro_no_email_for_methods;
        }

        if ( isset( $_POST['fakturpro_email_template'] ) ) {
            $fakturpro_email_template = sanitize_key( $_POST['fakturpro_email_template'] );
            $fakturpro_email_template = in_array($fakturpro_email_template, array_keys( $this->get_email_template_options() ) ) ? $fakturpro_email_template : 'none';
            $data['fakturpro_email_template'] = $fakturpro_email_template;
        }

        if ( isset( $_POST['fakturpro_email_subject'] ) ) {
            $fakturpro_email_subject = sanitize_text_field( $_POST['fakturpro_email_subject'] );
            if ( empty( $fakturpro_email_subject ) || $fakturpro_email_subject = $this->validate_allowed_chars( $fakturpro_email_subject ) ) {
                $data['fakturpro_email_subject'] = $fakturpro_email_subject;
            } else {
                $this->error_fields[] = 'fakturpro_email_subject';
            }
        }

        if ( isset( $_POST['fakturpro_email_to'] ) ) {
            $fakturpro_email_to = preg_split( '/[\s;,]/i', $_POST['fakturpro_email_to'] );
            $fakturpro_email_to = array_map(function ($email) { return sanitize_email( $email ); }, $fakturpro_email_to );
            $fakturpro_email_to = array_filter( $fakturpro_email_to , function ($email) { return is_email( $email ); });
            $data['fakturpro_email_to'] = implode( ', ', $fakturpro_email_to );
        }

        if ( isset( $_POST['fakturpro_email_copy'] ) ) {
            $fakturpro_email_copy = preg_split( '/[\s;,]/i', $_POST['fakturpro_email_copy'] );
            $fakturpro_email_copy = array_map(function ($email) { return sanitize_email( $email ); }, $fakturpro_email_copy );
            $fakturpro_email_copy = array_filter( $fakturpro_email_copy , function ($email) { return is_email( $email ); });
            $data['fakturpro_email_copy'] = implode( ', ', $fakturpro_email_copy );
        }

        if ( isset( $_POST['fakturpro_email_blind_copy'] ) ) {
            $fakturpro_email_blind_copy = preg_split( '/[\s;,]/i', $_POST['fakturpro_email_blind_copy'] );
            $fakturpro_email_blind_copy = array_map(function ($email) { return sanitize_email( $email ); }, $fakturpro_email_blind_copy );
            $fakturpro_email_blind_copy = array_filter( $fakturpro_email_blind_copy , function ($email) { return is_email( $email ); });
            $data['fakturpro_email_blind_copy'] = implode( ', ', $fakturpro_email_blind_copy );
        }

        if ( isset( $_POST['fakturpro_email_content_text'] ) ) {
            $fakturpro_email_content_text = sanitize_textarea_field( $_POST['fakturpro_email_content_text'] );
            $fakturpro_email_content_text = strip_tags( $fakturpro_email_content_text );
            $data['fakturpro_email_content_text'] = $fakturpro_email_content_text;
        }

        if ( isset( $_POST['fakturpro_email_content_html'] ) ) {
            $fakturpro_email_content_html = FP_Plugin::encode_html_content( $_POST['fakturpro_email_content_html'] );
            $data['fakturpro_email_content_html'] = $fakturpro_email_content_html;
        }

        if ( !empty( $this->error_fields ) ) {
            FP_Admin_Notices::add_notice(
                __( 'Validation error: Not all settings were saved because their content was invalid. Settings that were not changed were marked in red.', 'fakturpro' ),
                FP_Admin_Notices::NOTICE_TYPE_ERROR,
                true
            );
        }

        woocommerce_update_options( $this->get_settings(), $data );
    }

    /**
     * Filter for get options with html content as value.
     *
     * @param  string $value
     * @param  mixed $option
     * @return string
     */
    public function html_content_filter($value, $option)
    {
        return FP_Plugin::decode_html_content($value);
    }

    /**
     * Collect all settings fields for the WooCommerce tab.
     *
     * @return array<array<string, mixed>>
     */
    public function get_settings()
    {
        $settings = array();
        $settings = array_merge( $settings, $this->section_general() );
        $settings = array_merge( $settings, $this->section_customer() );
        $settings = array_merge( $settings, $this->section_invoice() );
        $settings = array_merge( $settings, $this->section_invoice_items() );
        $settings = array_merge( $settings, $this->section_email() );
        return $settings;
    }

    /**
     * Get order states.
     * 
     * @return array<string>
     */
    private function get_order_states()
    {
        return array_filter($this->plugin()->get_order_statuses(), function($status) {
            return !in_array($status, array('cancelled', 'refunded', 'failed'));
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get line names.
     * 
     * @return array<string, string>
     */
    private function get_line_names()
    {
        $names = array(
            'product_name' => __('Product name', 'fakturpro'),
            'product_name_and_alternate_title' => __('Product name and alternate title', 'fakturpro'),
            'alternate_title_and_product_name' => __('Alternate title and product name', 'fakturpro'),
            'alternate_title' => __('Alternate title', 'fakturpro'),
        );

        if ($this->_plugin->is_secondary_title_active()) {
            $names = array_merge(
                $names,
                array(
                    'secondary_title_format' => __('Secondary Title format (Plugin)', 'fakturpro'),
                )
            );
        }

        return $names;
    }

    /**
     * Get line descriptions.
     * 
     * @return array<string, string>
     */
    private function get_line_descriptions()
    {
        return array(
            '' => __('No description', 'fakturpro'),
            'article' => __('Product or variant description explicit', 'fakturpro'),
            'article_or_variation_inherit' => __('Product or variant description inherited', 'fakturpro'),
            'article_and_variation' => __('Product and variant description', 'fakturpro'),
            'article_strict' => __('Product description', 'fakturpro'),
            'short' => __('Short product description', 'fakturpro'),
            'variation_title' => __('Variant title', 'fakturpro'),
            'variation' => __('Variant description', 'fakturpro'),
            'meta_data' => __('Metadata', 'fakturpro'),
            'mini_desc' => __('Shopping cart short description', 'fakturpro'),
            'variation_mini_desc' => __('Variant shopping cart short description', 'fakturpro'),
            'alternate_title' => __('Alternate title', 'fakturpro'),
        );
    }

    /**
     * Get invoice email options.
     * 
     * @return array<string, string>
     */
    public function get_invoice_email_options()
    {
        return array(
            'none'      => __('Do not send invoices by email', 'fakturpro'),
            'append'    => __('Attach invoice to WooCommerce email', 'fakturpro'),
            'separate'  => __('Send invoice in separate email', 'fakturpro'),
        );
    }

    /**
     * Get email template options.
     * 
     * @return array<string, string>
     */
    private function get_email_template_options()
    {
        return array(
            'none' => __('No template', 'fakturpro'),
            'customer_deliver_invoice' => __('Deliver invoice', 'fakturpro'),
        );
    }

    /**
     * Get email recipient address.
     *
     * @param WC_Email $email
     * @return string
     */
    private function _get_email_recipient_address(WC_Email $email)
    {
        return esc_html($email->is_customer_email() ? __('Customer', 'woocommerce') : $email->get_recipient());
    }

    /**
     * Get email recipient from email.
     *
     * @param WC_Email $email
     * @return string
     */
    private function _get_email_recipient_text(WC_Email $email)
    {
        return __('Recipient(s)', 'woocommerce').': '.$this->_get_email_recipient_address($email);
    }

    /**
     * Get email recipient from emails via key.
     *
     * @param  WC_Email[] $emails
     * @param  string $key
     * @return string
     */
    private function _get_email_recipient_info( $emails, $key )
    {
        if ( !empty( $emails[$key] ) ) {
            return ' ('. $this->_get_email_recipient_text( $emails[$key] ) .')';
        }
        return '';
    }

    /**
     * Get email types.
     * 
     * @return array<string, string>
     */
    private function get_email_types()
    {
		$mailer = WC()->mailer();
		$emails = $mailer->get_emails();

        $email_types = array();
        $email_types['new_order'] = __(
            'New order',
            'woocommerce'
        ).$this->_get_email_recipient_info($emails, 'WC_Email_New_Order');

        $email_types['customer_processing_order'] = (
            $this->plugin()->is_germanized_active()
                ? __('Order Confirmation', 'woocommerce-germanized')
                : __('Processing order', 'woocommerce')
        ).$this->_get_email_recipient_info($emails, 'WC_Email_Customer_Processing_Order');

        $email_types['customer_on_hold_order'] = __(
            'Order on-hold',
            'woocommerce'
        ).$this->_get_email_recipient_info($emails, 'WC_Email_Customer_On_Hold_Order');

        // WooCommerce Germanized plugin emails
        if ($this->plugin()->is_germanized_active()) {
            $email_types['customer_paid_for_order'] = __(
                'Paid for order',
                'woocommerce-germanized'
            ).$this->_get_email_recipient_info($emails, 'WC_GZD_Email_Customer_Paid_For_Order');

            $email_types['customer_shipment'] = _x(
                'Order shipped',
                'shipments',
                'woocommerce-germanized'
            ).$this->_get_email_recipient_info($emails, 'WC_GZD_Email_Customer_Shipment');
        }

        $email_types['customer_completed_order'] = __(
            'Completed order',
            'woocommerce'
        ).$this->_get_email_recipient_info($emails, 'WC_Email_Customer_Completed_Order');

        // WooCommerce Subscriptions plugin email
        if ($this->plugin()->is_woocommerce_subscriptions_active()) {
            $email_types['new_renewal_order'] = __(
                'New Renewal Order',
                'woocommerce-subscriptions'
            ).$this->_get_email_recipient_info($emails, 'WCS_Email_New_Renewal_Order');

            $email_types['customer_renewal_invoice'] = __(
                'Customer Renewal Invoice',
                'woocommerce-subscriptions'
            ).$this->_get_email_recipient_info($emails, 'WCS_Email_Customer_Renewal_Invoice');

            $email_types['customer_on_hold_renewal_order'] = __(
                'On-hold Renewal Order',
                'woocommerce-subscriptions'
            ).$this->_get_email_recipient_info($emails, 'WCS_Email_Customer_On_Hold_Renewal_Order');

            $email_types['customer_processing_renewal_order'] = __(
                'Processing Renewal order',
                'woocommerce-subscriptions'
            ).$this->_get_email_recipient_info($emails, 'WCS_Email_Processing_Renewal_Order');

            $email_types['customer_completed_renewal_order'] = __(
                'Completed Renewal Order',
                'woocommerce-subscriptions'
            ).$this->_get_email_recipient_info($emails, 'WCS_Email_Completed_Renewal_Order');
        }

        return $email_types;
    }

    /**
     * Get payment methods.
     * 
     * @return array<string>
     */
    private function get_payment_methods()
    {
        $gateways = WC()->payment_gateways();
        $gateways = $gateways->payment_gateways();

        $result = array();

        foreach ($gateways as $id => $gateway) {
            $method_title = $gateway->get_method_title() ? $gateway->get_method_title() : $gateway->get_title();
            $custom_title = $gateway->get_title();

            $title = wp_kses_post( $method_title );
            $title .= $method_title !== $custom_title ? ' - ' . wp_kses_post( $custom_title ) : '';
            $title .= ' (' . $id . ')';

            $result[$id] = $title;
        }

        return $result;
    }

    /**
     * Section general.
     * 
     * @return array<array<string, mixed>>
     */
    private function section_general()
    {
        $result = array();
        $result[] = $this->section_general_start();
        $result[] = $this->field_shop_url();
        $result[] = $this->field_shop_token();
        $result[] = $this->field_send_error_mails_to();
        $result[] = $this->section_general_end();
        return $result;
    }

    /**
     * Section customer.
     * 
     * @return array<array<string, mixed>>
     */
    private function section_customer()
    {
        $result = array();
        $result[] = $this->section_customer_start();
        $result[] = $this->field_customer_vat_id_meta_name();
        $result[] = $this->section_customer_end();
        return $result;
    }

    /**
     * Section invoice.
     * 
     * @return array<array<string, mixed>>
     */
    private function section_invoice()
    {
        $result = array();
        $result[] = $this->section_invoice_start();
        $result[] = $this->field_create_invoices();
        $result[] = $this->field_invoices_for_states();
        $result[] = $this->field_no_invoices_for_methods();
        $result[] = $this->field_paid_for_methods();
        $result[] = $this->field_cancel_invoices();
        $result[] = $this->field_zero_value_invoices();
        $result[] = $this->field_open_invoices();
        $result[] = $this->field_customer_link();
        $result[] = $this->field_order_number_prefix();
        $result[] = $this->field_order_number_suffix();
        $result[] = $this->field_email_filename();
        $result[] = $this->section_invoice_end();
        return $result;
    }

    /**
     * Section invoice items.
     * 
     * @return array<array<string, mixed>>
     */
    private function section_invoice_items()
    {
        $result = array();
        $result[] = $this->section_invoice_items_start();
        $result[] = $this->field_merge_credits();
        $result[] = $this->field_line_name();
        $result[] = $this->field_line_description();
        $result[] = $this->field_price_num_decimals();
        $result[] = $this->field_article_name_shipping();
        $result[] = $this->field_article_number_shipping();
        $result[] = $this->section_invoice_items_end();
        return $result;
    }

    /**
     * Section email.
     * 
     * @return array<array<string, mixed>>
     */
    private function section_email()
    {
        $result = array();
        $result[] = $this->section_email_start();
        $result[] = $this->field_invoice_email();
        $result[] = $this->field_email_to_append_to();
        $result[] = $this->field_email_for_states();
        $result[] = $this->field_no_email_for_methods();
        $result[] = $this->field_email_template();
        $result[] = $this->field_email_subject();
        $result[] = $this->field_email_to();
        $result[] = $this->field_email_copy();
        $result[] = $this->field_email_blind_copy();
        $result[] = $this->field_email_content_text();
        $result[] = $this->field_email_content_html();
        $result[] = $this->field_email_content_placeholders();
        $result[] = $this->section_email_end();
        return $result;
    }

    /**
     * Section general start.
     * 
     * @return array<string, mixed>
     */
    private function section_general_start()
    {
        return array(
            'id'        => 'fakturpro_section_general',
            'title'     => __('Basic Settings', 'fakturpro'),
            'type'      => 'title',
            'desc'      => __('Please enter your store\'s URL and API token to connect to your Faktur Pro account. You can find the API token in your store\'s settings in the "API token" section. If you don\'t yet have a Faktur Pro user account, register for free at http://www.faktur.pro.', 'fakturpro')
        );
    }

    /**
     * Section general end.
     * 
     * @return array<string, mixed>
     */
    private function section_general_end()
    {
        return array(
            'id'        => 'fakturpro_section_general',
            'type'      => 'sectionend',
        );
    }

    /**
     * Field desctiption table.
     * 
     * @param  array<string> $columns
     * @param  array<array<string>> $rows
     * @return string
     */
    private function field_description_table( $columns, $rows )
    {
        $table = '<table class="fp-desc-table">';
        $table .= '<tr>';
        foreach ($columns as $column) {
            $table .= '<th class="fp-desc-th">'.esc_html( $column ).'</th>';
        }
        $table .= '</tr>';
        foreach ($rows as $row) {
            $table .= '<tr>';
            foreach ($row as $data) {
                $table .= '<td class="fp-desc-td">'.esc_html( $data ).'</td>';
            }
            $table .= '</tr>';
        }
        $table .= '</table>';
        return $table;
    }

    /**
     * Field shop url.
     * 
     * @return array<string, mixed>
     */
    private function field_shop_url()
    {
        return array(
            'id'        => 'fakturpro_shop_url',
            'title'     => 'Shop URL',
            'type'      => 'text',
            'css'       => $this->field_css('fakturpro_shop_url'),
            'default'   => get_home_url(),
            'desc_tip'  => __('The store URL should match the URL you set for your store in your account.', 'fakturpro'),
        );
    }

    /**
     * Field shop token.
     * 
     * @return array<string, mixed>
     */
    private function field_shop_token()
    {
        return array(
            'id'        => 'fakturpro_shop_token',
            'title'     => 'Shop Token',
            'type'      => 'text',
            'css'       => $this->field_css('fakturpro_shop_token'),
            'desc_tip'  => __('The shop token should match the token you received in your account for your shop.', 'fakturpro'),
        );
    }

    /**
     * Field send error mails to.
     * 
     * @return array<string, mixed>
     */
    private function field_send_error_mails_to()
    {
        return array(
            'id'        => 'fakturpro_send_error_mails_to',
            'title'     => __('Error emails to', 'fakturpro'),
            'desc'      => '',
            'class'     => 'chosen_select',
            'type'      => 'select',
            'options'   => array(
                'admin' => sprintf( "WordPress %s (%s)", __( 'Administration Email Address' ), get_option( 'admin_email' ) ),
                'user' => __('Email address of the acting user', 'fakturpro'),
            ),
            'default'   => 'admin',
        );
    }

    /**
     * Section customer start.
     * 
     * @return array<string, mixed>
     */
    private function section_customer_start()
    {
        return array(
            'id'        => 'fakturpro_section_customer',
            'title'     => __('Customer Settings', 'fakturpro'),
            'type'      => 'title',
            'desc'      => __('Please configure the details of customer data for creating invoices in your shop in this section.', 'fakturpro')
        );
    }

    /**
     * Section customer end.
     * 
     * @return array<string, mixed>
     */
    private function section_customer_end()
    {
        return array(
            'id'        => 'fakturpro_section_customer',
            'type'      => 'sectionend',
        );
    }

    /**
     * Field customer vat id meta name.
     * 
     * @return array<string, mixed>
     */
    private function field_customer_vat_id_meta_name()
    {
        return array(
            'id'        => 'fakturpro_customer_vat_id_meta_name',
            'title'     => __('Vat ID meta name', 'fakturpro'),
            'type'      => 'text',
            'css'       => $this->field_css('fakturpro_customer_vat_id_meta_name'),
            'desc'      => __('The name from the metadata of the customer or the order under which the customers VAT ID is stored.', 'fakturpro'),
            'default'   => '',
            'desc_tip'  => __('An optional meta field name to find the customers VAT ID.', 'fakturpro'),
        );
    }

    /**
     * Section invoice start.
     * 
     * @return array<string, mixed>
     */
    private function section_invoice_start()
    {
        return array(
            'id'        => 'fakturpro_section_invoice',
            'type'      => 'title',
            'title'     => __('Billing Settings', 'fakturpro'),
            'desc'      => __('Please configure the details for creating invoices in your shop in this section.', 'fakturpro'),
        );
    }

    /**
     * Section invoice end.
     * 
     * @return array<string, mixed>
     */
    private function section_invoice_end()
    {
        return array(
            'id'        => 'fakturpro_section_invoice',
            'type'      => 'sectionend',
        );
    }

    /**
     * Field create invoices.
     * 
     * @return array<string, mixed>
     */
    private function field_create_invoices()
    {
        return array(
            'id'        => 'fakturpro_create_invoices',
            'title'     => __('Automatic creation', 'fakturpro'),
            'type'      => 'checkbox',
            'desc'      => __('Automatically create invoices for orders', 'fakturpro'),
            'default'   => 'yes',
            'desc_tip'  => __('If you enable this option, invoices will automatically be created for orders when they have a specific status and payment method. You can configure the order status and payment method for which invoices should be created using the following two options.', 'fakturpro'),
        );
    }

    /**
     * Field invoices for states.
     * 
     * @return array<string, mixed>
     */
    private function field_invoices_for_states()
    {
        return array(
            'id'        => 'fakturpro_invoice_for_states',
            'title'     => __('Creation for order status', 'fakturpro'),
            'desc'      => '',
            'class'     => 'chosen_select',
            'type'      => 'multiselect',
            'options'   => $this->get_order_states(),
            'default'   => array('completed'),
            'desc_tip'  => __('Please select all order statuses for which invoices should be automatically created by Faktur Pro.', 'fakturpro'),
        );
    }

    /**
     * Field no invoices for methods.
     * 
     * @return array<string, mixed>
     */
    private function field_no_invoices_for_methods()
    {
        return array(
            'id'        => 'fakturpro_no_invoice_for_methods',
            'title'     => __('Exceptions for payment methods', 'fakturpro'),
            'desc'      => '',
            'class'     => 'chosen_select',
            'type'      => 'multiselect',
            'options'   => $this->get_payment_methods(),
            'default'   => array(),
            'desc_tip'  => __('Please select all payment methods for which Faktur Pro should NOT automatically generate invoices.', 'fakturpro'),
        );
    }

    /**
     * Field paid for methods.
     * 
     * @return array<string, mixed>
     */
    private function field_paid_for_methods()
    {
        return array(
            'id'        => 'fakturpro_paid_for_methods',
            'title'     => __('"Paid" per payment method', 'fakturpro'),
            'desc'      => '',
            'class'     => 'chosen_select',
            'type'      => 'multiselect',
            'options'   => $this->get_payment_methods(),
            'default'   => array(),
            'desc_tip'  => __('Please select all payment methods for which you would like your invoice to be marked as "paid".', 'fakturpro'),
        );
    }

    /**
     * Field zero value invoices.
     * 
     * @return array<string, mixed>
     */
    private function field_zero_value_invoices()
    {
        return array(
            'id'        => 'fakturpro_zero_value_invoices',
            'title'     => __('Creation at 0€', 'fakturpro'),
            'type'      => 'checkbox',
            'desc'      => __('Creation of invoices over €0', 'fakturpro'),
            'default'   => 'no',
            'desc_tip'  => __('If you activate this option, invoices will also be created for orders with a total value of 0 euros. By default, no invoices are created for these orders.', 'fakturpro'),
        );
    }

    /**
     * Field cancel invoices.
     * 
     * @return array<string, mixed>
     */
    private function field_cancel_invoices()
    {
        return array(
            'id'        => 'fakturpro_cancel_invoices',
            'title'     => __('Automatic cancellation', 'fakturpro'),
            'type'      => 'checkbox',
            'desc'      => __('Automatically cancel invoices', 'fakturpro'),
            'default'   => 'no',
            'desc_tip'  => __('If you activate this option, cancellation invoices will be automatically created when an order is canceled. Cancellation only occurs if you use an external billing service.', 'fakturpro'),
        );
    }

    /**
     * Field open invoices.
     * 
     * @return array<string, mixed>
     */
    private function field_open_invoices()
    {
        return array(
            'id'        => 'fakturpro_open_invoices',
            'title'     => __('Open invoice', 'fakturpro'),
            'type'      => 'checkbox',
            'desc'      => __('Open the invoice instead of downloading it directly', 'fakturpro'),
            'default'   => 'no',
            'desc_tip'  => __('If you activate this option, invoices that you generate using the invoice button in the order overview will be opened in the browser instead of starting a download of the invoice PDF.', 'fakturpro'),
        );
    }

    /**
     * Field customer link.
     * 
     * @return array<string, mixed>
     */
    private function field_customer_link()
    {
        return array(
            'id'        => 'fakturpro_customer_link',
            'title'     => __('Invoice for customer', 'fakturpro'),
            'type'      => 'checkbox',
            'desc'      => __('Make the invoice available for customers to download', 'fakturpro'),
            'default'   => 'no',
            'desc_tip'  => __('If you enable this option, a link to download their invoice will be added to the order summary in your customer\'s account. The link only appears once an invoice has been generated for your order.', 'fakturpro'),
        );
    }

    /**
     * Field article name shipping.
     * 
     * @return array<string, mixed>
     */
    private function field_article_name_shipping()
    {
        return array(
            'id'        => 'fakturpro_article_name_shipping',
            'title'     => __('Description of shipping costs', 'fakturpro'),
            'type'      => 'text',
            'css'       => $this->field_css('fakturpro_article_name_shipping'),
            'desc'      => '',
            'default'   => '',
            'desc_tip'  => __('The name that should appear on the invoice for all different shipping cost types (optional).', 'fakturpro'),
        );
    }

    /**
     * Field article number shipping.
     * 
     * @return array<string, mixed>
     */
    private function field_article_number_shipping()
    {
        return array(
            'id'        => 'fakturpro_article_number_shipping',
            'title'     => __('Item number shipping costs', 'fakturpro'),
            'type'      => 'text',
            'css'       => $this->field_css('fakturpro_article_number_shipping'),
            'desc'      => '',
            'default'   => '',
            'desc_tip'  => __('The item number that should appear on the invoice for shipping costs.', 'fakturpro'),
        );
    }

    /**
     * Field order number prefix.
     * 
     * @return array<string, mixed>
     */
    private function field_order_number_prefix()
    {
        return array(
            'id'        => 'fakturpro_order_number_prefix',
            'title'     => __('Order number prefix', 'fakturpro'),
            'type'      => 'text',
            'css'       => $this->field_css('fakturpro_order_number_prefix'),
            'desc'      => '',
            'default'   => '',
            'desc_tip'  => __('An optional prefix for the original order number.', 'fakturpro'),
        );
    }

    /**
     * Field order number suffix.
     * 
     * @return array<string, mixed>
     */
    private function field_order_number_suffix()
    {
        return array(
            'id'        => 'fakturpro_order_number_suffix',
            'title'     => __('Order number suffix', 'fakturpro'),
            'type'      => 'text',
            'css'       => $this->field_css('fakturpro_order_number_suffix'),
            'desc'      => '',
            'default'   => '',
            'desc_tip'  => __('An optional suffix for the original order number.', 'fakturpro'),
        );
    }

    /**
     * Field email filename.
     * 
     * @return array<string, mixed>
     */
    private function field_email_filename()
    {
        $variables = $this->plugin()->get_invoice_filename_variables();
        $rows = array();
        foreach ($variables as $name => $description) {
            $rows[] = array( $name, $description );
        }
        $desc = $this->field_description_table(array(__('Variable', 'fakturpro'), __('Description', 'fakturpro')), $rows);
        return array(
            'id'        => 'fakturpro_email_filename',
            'title'     => __('File name of the PDF', 'fakturpro'),
            'type'      => 'text',
            'css'       => $this->field_css('fakturpro_email_filename'),
            'desc'      => $desc,
            'default'   => '',
            'desc_tip'  => __('The name of the invoice file that is sent by email (without file extension!).', 'fakturpro'),
        );
    }

    /**
     * Section invoice items start.
     * 
     * @return array<string, mixed>
     */
    private function section_invoice_items_start()
    {
        return array(
            'id'        => 'fakturpro_section_invoice_items',
            'type'      => 'title',
            'title'     => __('Invoice Items Settings', 'fakturpro'),
            'desc'      => __('Please configure the details of invoice items for creating invoices in your shop in this section.', 'fakturpro'),
        );
    }

    /**
     * Section invoice items end.
     * 
     * @return array<string, mixed>
     */
    private function section_invoice_items_end()
    {
        return array(
            'id'        => 'fakturpro_section_invoice_items',
            'type'      => 'sectionend',
        );
    }

    /**
     * Field merge credits.
     * 
     * @return array<string, mixed>
     */
    private function field_merge_credits()
    {
        return array(
            'id'        => 'fakturpro_merge_credits',
            'title'     => __('Merge credits', 'fakturpro'),
            'type'      => 'checkbox',
            'desc'      => __('Merges credits to a single invoice line item', 'fakturpro'),
            'default'   => 'no',
            'desc_tip'  => __('Merges credits to a single invoice line item and sum the amount.', 'fakturpro'),
        );
    }

    /**
     * Field line name.
     * 
     * @return array<string, mixed>
     */
    private function field_line_name()
    {
        return array(
            'id'        => 'fakturpro_line_name',
            'title'     => __('Product name', 'fakturpro'),
            'type'      => 'select',
            'options'   => $this->get_line_names(),
            'desc_tip'  => __('Text to be transferred as a product name.', 'fakturpro'),
            'default'   => 'product_name',
        );
    }

    /**
     * Field line description.
     * 
     * @return array<string, mixed>
     */
    private function field_line_description()
    {
        return array(
            'id'        => 'fakturpro_line_description',
            'title'     => __('Product description', 'fakturpro'),
            'type'      => 'select',
            'options'   => $this->get_line_descriptions(),
            'desc_tip'  => __('Text to be transferred as a product description.', 'fakturpro'),
            'default'   => 'article',
            'desc'      => __("Explicit = Use variant description or product description (WooCommerce decides).<br />Inherited = Use variant description if available otherwise product description.", 'fakturpro'),
        );
    }

    /**
     * Field price num decimals.
     * 
     * @return array<string, mixed>
     */
    private function field_price_num_decimals()
    {
        return array(
            'id'        => 'fakturpro_price_num_decimals',
            'title'     => __('Price decimal places', 'fakturpro'),
            'desc_tip'  => __('Number of decimal places for product prices to transfer.', 'fakturpro'),
            'desc'      => __("Unless otherwise required, 2 decimal places should be used. If there are more decimal places, rounding errors may occur. These usually occur when creating net invoices because the services calculate the amounts using net prices and convert the sum to gross. If there are more than 2 decimal places, we recommend creating gross invoices.", 'fakturpro'),
            'type'      => 'number',
            'default'   => '2',
            'css'       => 'width:50px;',
            'custom_attributes' => array(
                'min'  => 0,
                'step' => 1,
            ),
        );
    }

    /**
     * Section email start.
     * 
     * @return array<string, mixed>
     */
    private function section_email_start()
    {
        return array(
            'id'         => 'fakturpro_section_email',
            'type'       => 'title',
            'title'      => __('Email Settings', 'fakturpro'),
            'desc'       => __('Please configure sending your invoices by email in this section.', 'fakturpro'),
        );
    }

    /**
     * Section email end.
     * 
     * @return array<string, mixed>
     */
    private function section_email_end()
    {
        return array(
            'id'         => 'fakturpro_section_email',
            'type'       => 'sectionend',
        );
    }

    /**
     * Field invoice email.
     * 
     * @return array<string, mixed>
     */
    private function field_invoice_email()
    {
        return array(
            'id'         => 'fakturpro_invoice_email',
            'title'      => __('Email sending', 'fakturpro'),
            'desc'       => '',
            'type'       => 'radio',
            'default'    => 'append',
            'options'    => $this->get_invoice_email_options(),
        );
    }

    /**
     * Field email to append to.
     * 
     * @return array<string, mixed>
     */
    private function field_email_to_append_to()
    {
        return array(
            'id'        => 'fakturpro_email_to_append_to',
            'title'     => __('Email for attachment', 'fakturpro'),
            'class'     => 'chosen_select',
            'type'      => 'multiselect',
            'desc_tip'  => __('Email to which the invoice should be attached.', 'fakturpro'),
            'default'   => array('customer_processing_order'),
            'options'   => $this->get_email_types(),
        );
    }

    /**
     * Field email for states.
     * 
     * @return array<string, mixed>
     */
    private function field_email_for_states()
    {
        return array(
            'id'        => 'fakturpro_email_for_states',
            'title'     => __('Email for order status', 'fakturpro'),
            'desc'      => '',
            'class'     => 'chosen_select',
            'type'      => 'multiselect',
            'options'   => $this->get_order_states(),
            'default'   => array('completed'),
            'desc_tip'  => __('The order status for which the invoice should be sent as a separate email.', 'fakturpro'),
        );
    }

    /**
     * Field no email for methods.
     * 
     * @return array<string, mixed>
     */
    private function field_no_email_for_methods()
    {
        return array(
            'id'        => 'fakturpro_no_email_for_methods',
            'title'     => __('Exceptions for payment methods', 'fakturpro'),
            'desc'      => '',
            'class'     => 'chosen_select',
            'type'      => 'multiselect',
            'options'   => $this->get_payment_methods(),
            'default'   => array(),
            'desc_tip'  => __('The payment methods for which the invoice should NOT be sent by email.', 'fakturpro'),
        );
    }

    /**
     * Field email template.
     * 
     * @return array<string, mixed>
     */
    private function field_email_template()
    {
        $href = admin_url('/admin.php?page=wc-settings&tab=email');
        return array(
            'id'         => 'fakturpro_email_template',
            'title'      => __('Email template', 'fakturpro'),
            'type'       => 'select',
            'default'    => 'none',
            'options'    => $this->get_email_template_options(),
            'desc'       => sprintf(
                /* translators: %s: tab name */
                __('The email templates can be found in the "%s" settings tab.', 'fakturpro'),
                '<a href="' . esc_url($href) . '" target="_self">' . __('Email', 'woocommerce') . '</a>'
            ),
        );
    }

    /**
     * Field email subject.
     * 
     * @return array<string, mixed>
     */
    private function field_email_subject()
    {
        return array(
            'id'        => 'fakturpro_email_subject',
            'title'     => __('Email subject', 'fakturpro'),
            'type'      => 'text',
            'css'       => $this->field_css('fakturpro_email_subject'),
            'desc'      => '',
            'default'   => '',
            'desc_tip'  => __('The subject of the invoice email.', 'fakturpro'),
        );
    }

    /**
     * Field email to.
     * 
     * @return array<string, mixed>
     */
    private function field_email_to()
    {
        return array(
            'title'     => __('Email additionally to', 'fakturpro'),
            'id'        => 'fakturpro_email_to',
            'type'      => 'text',
            'desc'      => '',
            'default'   => '',
            'desc_tip'  => __('Additional email addresses to which the email should be sent. Separate addresses with commas.', 'fakturpro'),
        );
    }

    /**
     * Field email copy.
     * 
     * @return array<string, mixed>
     */
    private function field_email_copy()
    {
        return array(
            'title'     => __('Email copy (CC)', 'fakturpro'),
            'id'        => 'fakturpro_email_copy',
            'type'      => 'text',
            'desc'      => '',
            'default'   => '',
            'desc_tip'  => __('Additional email addresses to which the email should be sent as a copy. Separate addresses with commas.', 'fakturpro'),
        );
    }

    /**
     * Field email blind copy.
     * 
     * @return array<string, mixed>
     */
    private function field_email_blind_copy()
    {
        return array(
            'title'     => __('Email blind copy (BCC)', 'fakturpro'),
            'id'        => 'fakturpro_email_blind_copy',
            'type'      => 'text',
            'desc'      => '',
            'default'   => '',
            'desc_tip'  => __('Additional email addresses to which the email should be sent as a blind copy. Separate addresses with commas.', 'fakturpro'),
        );
    }

    /**
     * Field email content text.
     * 
     * @return array<string, mixed>
     */
    private function field_email_content_text()
    {
        return array(
            'id'        => 'fakturpro_email_content_text',
            'title'     => __('Email text content', 'fakturpro'),
            'desc'      => '',
            'default'   =>  '',
            'css'       => 'height: 100px;',
            'type'      => 'textarea',
            'desc_tip'  => __('The text content of your invoice email. HTML is not allowed here.', 'fakturpro'),
        );
    }

    /**
     * Field email content html.
     * 
     * @return array<string, mixed>
     */
    private function field_email_content_html()
    {
        return array(
            'id'        => 'fakturpro_email_content_html',
            'title'     => __('Email HTML content', 'fakturpro'),
            'desc'      => '',
            'default'   =>  '',
            'css'       => 'height: 100px;',
            'type'      => 'textarea',
            'desc_tip'  => __('The HTML content of your invoice email. You can use HTML here.', 'fakturpro'),
        );
    }

    /**
     * Field email content placeholders.
     * 
     * @return array<string, mixed>
     */
    private function field_email_content_placeholders()
    {
        $variables = $this->plugin()->get_invoice_placeholder_variables();
        $rows = array();
        foreach ($variables as $name => $description) {
            $rows[] = array( $name, $description );
        }
        $desc = $this->field_description_table(array(__('Variable', 'fakturpro'), __('Description', 'fakturpro')), $rows);
        return array(
            'id'        => 'fakturpro_email_content_placeholders',
            'title'     => '',
            'desc'      => $desc,
            'type'      => 'description',
        );
    }

    /**
     * Output description.
     * 
     * @param  array<string, mixed> $value
     * @return void
     */
    public function output_description( $value )
    {
        // Description handling.
        $field_description = class_exists('WC_Admin_Settings') ? WC_Admin_Settings::get_field_description( $value ) : [];
        $description       = array_key_exists('description', $field_description) ? $field_description['description'] : '';
        $tooltip_html      = array_key_exists('description', $field_description) ? $field_description['tooltip_html'] : '';

        ?>
        <tr valign="top"<?php echo $value['row_class'] ? ' class="' . esc_attr( $value['row_class'] ) . '"' : '' ?>">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
            </th>
            <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
                <p id="<?php echo esc_attr( $value['id'] ); ?>">
                    <?php echo $description; // WPCS: XSS ok. ?>
                </p>
            </td>
        </tr>
        <?php
    }
}

endif;
