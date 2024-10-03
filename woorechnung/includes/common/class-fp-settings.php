<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'FP_Settings' ) ):

/**
 * Faktur Pro Settings Component.
 *
 * This class provides an interface to all user settings that this
 * plugin requires. The settings are stored and loaded using the WordPress
 * options API.
 *
 * @class    FP_Settings
 * @version  1.0.0
 * @package  FakturPro\Common
 * @author   Zweischneider
 */
final class FP_Settings extends FP_Abstract_Settings
{
    /**
     * The (unprefixed) keys of all settings.
     *
     * @var array<string>
     */
    protected $_keys = array(
        'shop_url',
        'shop_token',
        'send_error_mails_to',
        'customer_vat_id_meta_name',
        'create_invoices',
        'cancel_invoices',
        'invoice_for_states',
        'no_invoice_for_methods',
        'paid_for_methods',
        'zero_value_invoices',
        'open_invoices',
        'customer_link',
        'line_name',
        'line_description',
        'merge_credits',
        'article_name_shipping',
        'article_number_shipping',
        'price_num_decimals',
        'order_number_prefix',
        'order_number_suffix',
        'invoice_email',
        'email_to_append_to',
        'email_for_states',
        'no_email_for_methods',
        'email_template',
        'email_filename',
        'email_to',
        'email_copy',
        'email_blind_copy',
        'email_subject',
        'email_content_text',
        'email_content_html',
    );

    /**
     * Decide if the shop url setting has been set.
     *
     * @return bool
     */
    public function has_shop_url()
    {
        return ! $this->is_empty( 'shop_url' );
    }

    /**
     * Set the shop URL setting.
     *
     * @param  string $value
     * @return void
     */
    public function set_shop_url ( $value )
    {
        $this->set( 'shop_url' , $value );
    }

    /**
     * Get the shop url to use for authentication.
     *
     * @return string|null
     */
    public function get_shop_url()
    {
        return trim( $this->get( 'shop_url', get_home_url() ) );
    }

    /**
     * Decide if the shop token setting has been set.
     *
     * @return bool
     */
    public function has_shop_token()
    {
        return ! $this->is_empty( 'shop_token' );
    }

    /**
     * Set the shop token setting.
     *
     * @param  string $value
     * @return void
     */
    public function set_shop_token ( $value )
    {
        $this->set( 'shop_token' , $value );
    }

    /**
     * Get the shop token to use for authentication.
     *
     * @return string
     */
    public function get_shop_token()
    {
        return trim( $this->get( 'shop_token', '' ) );
    }

    /**
     * Set the email address to send error emails to.
     *
     * @param  string $value
     * @return void
     */
    public function set_send_error_mails_to( $value )
    {
        $this->set( 'send_error_mails_to', $value );
    }

    /**
     * Get the email address to send error emails to.
     *
     * @return string
     */
    public function get_send_error_mails_to()
    {
        $send_to = $this->get( 'send_error_mails_to', get_option( 'admin_email' ));
        switch ( $send_to ) {
            case 'admin':
                return get_option( 'admin_email' );
            case 'user':
                $current_user  = wp_get_current_user();
                return $current_user->user_email;
            default:
                return $send_to;
        }
    }

    /**
     * Set the customer vat id meta name.
     *
     * @param  string $value
     * @return void
     */
    public function set_customer_vat_id_meta_name( $value )
    {
        $this->set( 'customer_vat_id_meta_name', $value );
    }

    /**
     * Get the customer vat id meta name.
     *
     * @return string
     */
    public function get_customer_vat_id_meta_name()
    {
        return $this->get( 'customer_vat_id_meta_name', '' );
    }

    /**
     * Decide if both the shop url and the shop token have been set.
     *
     * @return bool
     */
    public function has_shop_credentials()
    {
        return $this->has_shop_url() && $this->has_shop_token();
    }

    /**
     * Set the create invoices setting.
     *
     * @param  bool $value
     * @return void
     */
    public function set_create_invoices ( $value )
    {
        $this->set( 'create_invoices' ,  $value ? 'yes' : 'no' );
    }

    /**
     * Decide if the creation of invoices is active.
     *
     * @return bool
     */
    public function get_create_invoices()
    {
        return $this->get( 'create_invoices', 'no') === 'yes';
    }

    /**
     * Set the create invoices setting.
     *
     * @param  array<string>|null $value
     * @return void
     */
    public function set_invoice_for_states ( $value )
    {
        $this->set( 'invoice_for_states', is_array( $value ) ? $value : array() );
    }

    /**
     * Get the order states for which invoices are to be created automatically.
     *
     * @return array<string>
     */
    public function get_invoice_for_states()
    {
        return $this->get( 'invoice_for_states', array() );
    }

    /**
     * Decide if an invoice is to be created for a particular order state.
     *
     * @param  string $state
     * @return bool
     */
    public function create_invoice_for_state( $state )
    {
        return in_array( $state, $this->get_invoice_for_states() );
    }

    /**
     * Set the create invoices for methods setting.
     *
     * @param  array<string>|null $value
     * @return void
     */
    public function set_no_invoice_for_methods ( $value )
    {
        $this->set( 'no_invoice_for_methods' , is_array( $value ) ? $value : array() );
    }

    /**
     * Get the payment methods for which invoices are to be created automatically.
     *
     * @return array<string>
     */
    public function get_no_invoice_for_methods()
    {
        return $this->get( 'no_invoice_for_methods' , array() );
    }

    /**
     * Set the invoices paid for payment methods setting.
     *
     * @param  array<string>|null $value
     * @return void
     */
    public function set_paid_for_methods( $value )
    {
        $this->set( 'paid_for_methods' , is_array( $value ) ? $value : array() );
    }

    /**
     * Get the invoices paid for payment methods setting.
     *
     * @return array<string>
     */
    public function get_paid_for_methods()
    {
        return $this->get( 'paid_for_methods', array() );
    }

    /**
     * Decide if to mark an invoice as paid for a given method.
     *
     * @param  string $method
     * @return bool
     */
    public function mark_invoice_as_paid( $method )
    {
        return in_array( $method, $this->get_paid_for_methods() );
    }

    /**
     * Decide if an invoice is to be created for a particular payment method.
     *
     * @param  string $method
     * @return bool
     */
    public function create_invoice_for_method( $method )
    {
        return ! in_array( $method, $this->get_no_invoice_for_methods() );
    }

    /**
     * Set the cancel invoices setting.
     *
     * @param  bool $value
     * @return void
     */
    public function set_cancel_invoices( $value )
    {
        $this->set( 'cancel_invoices', $value ? 'yes' : 'no' );
    }

    /**
     * Decide if the cancellation of invoices is active.
     *
     * @return bool
     */
    public function get_cancel_invoices()
    {
        return $this->get( 'cancel_invoices', 'no' ) === 'yes';
    }

    /**
     * Set the create zero value invoices setting.
     *
     * @param  bool $value
     * @return void
     */
    public function set_zero_value_invoices( $value )
    {
        $this->set( 'zero_value_invoices' , $value ? 'yes' : 'no' );
    }

    /**
     * Decide if invoices are to be created if they are of zero value.
     *
     * @return bool
     */
    public function get_zero_value_invoices()
    {
        return $this->get( 'zero_value_invoices', 'no' ) === 'yes';
    }

    /**
     * Decide if invoices are to be created for a given value.
     *
     * @param  mixed $value
     * @return bool
     */
    public function create_invoice_for_value( $value )
    {
        return (floatval($value) != 0.0) || $this->get_zero_value_invoices();
    }

    /**
     * Decide if invoices are to be created for given parameters.
     *
     * @param  string $status
     * @param  string $method
     * @param  mixed $value
     * @return bool
     */
    public function create_invoice_for( $status, $method, $value )
    {
        return $this->create_invoice_for_state( $status )
            && $this->create_invoice_for_method( $method )
            && $this->create_invoice_for_value( $value );
    }

    /**
     * Set the open invoices setting.
     *
     * @param  string $value
     * @return void
     */
    public function set_open_invoices( $value )
    {
        $this->set( 'open_invoices', $value ? 'yes' : 'no' );
    }

    /**
     * Decide if invoices are to be opened instead of downloaded.
     *
     * @return bool
     */
    public function get_open_invoices()
    {
        return $this->get( 'open_invoices', 'no' ) === 'yes';
    }

    /**
     * Set the customer link setting.
     *
     * @param  bool $value
     * @return void
     */
    public function set_customer_link( $value )
    {
        $this->set( 'customer_link' , $value ? 'yes' : 'no' );
    }

    /**
     * Decide if the invoice is to be downloadable for the customer.
     *
     * @return bool
     */
    public function get_customer_link()
    {
        return $this->get( 'customer_link', 'no' ) === 'yes';
    }

    /**
     * Set the merge credits setting.
     *
     * @param  bool $value
     * @return void
     */
    public function set_merge_credits( $value )
    {
        $this->set( 'merge_credits' , $value ? 'yes' : 'no' );
    }

    /**
     * Decide if the credits should be merged for the invoice.
     *
     * @return bool
     */
    public function get_merge_credits()
    {
        return $this->get( 'merge_credits', 'no' ) === 'yes';
    }

    /**
     * Get the line name setting .
     *
     * @return string
     */
    public function get_line_name()
    {
        return $this->get( 'line_name', 'product_name' );
    }

    /**
     * Set the line name setting .
     *
     * @param  string $value
     * @return void
     */
    public function set_line_name( $value )
    {
        $this->set( 'line_name', $value );
    }

    /**
     * Get the line description setting .
     *
     * @return string
     */
    public function get_line_description()
    {
        return $this->get( 'line_description' );
    }

    /**
     * Set the line description setting .
     *
     * @param  string $value
     * @return void
     */
    public function set_line_description( $value )
    {
        $this->set( 'line_description', $value );
    }

    /**
     * Set article name for shipping setting .
     *
     * @param  string $value
     * @return void
     */
    public function set_article_name_shipping( $value )
    {
        $this->set( 'article_name_shipping' , $value );
    }

    /**
     * Get the article name for shipping items
     *
     * @return string|null
     */
    public function get_article_name_shipping()
    {
        return $this->get( 'article_name_shipping' );
    }

    /**
     * Set article number for shipping setting .
     *
     * @param  string $value
     * @return void
     */
    public function set_article_number_shipping( $value )
    {
        $this->set( 'article_number_shipping' , $value );
    }

    /**
     * Get the article number for shipping items
     *
     * @return string|null
     */
    public function get_article_number_shipping()
    {
        return $this->get( 'article_number_shipping' );
    }

    /**
     * Set price number decimals.
     * 
     * @param  int $value
     * @return void
     */
    public function set_price_num_decimals( $value )
    {
        $this->set( 'price_num_decimals', $value );
    }

    /**
     * Get the price number decimals.
     *
     * @return int
     */
    public function get_price_num_decimals()
    {
        return $this->get( 'price_num_decimals', 2 );
    }

    /**
     * Set the order number prefix setting .
     *
     * @param  string $value
     * @return void
     */
    public function set_order_number_prefix( $value )
    {
        $this->set( 'order_number_prefix' , $value );
    }

    /**
     * Get the order number prefix for order invoices.
     *
     * @return string
     */
    public function get_order_number_prefix()
    {
        return $this->get( 'order_number_prefix', '' );
    }

    /**
     * Set the order number suffix setting .
     *
     * @param  string $value
     * @return void
     */
    public function set_order_number_suffix( $value )
    {
        $this->set( 'order_number_suffix' , $value );
    }

    /**
     * Get the order number suffix for order invoices.
     *
     * @return string
     */
    public function get_order_number_suffix()
    {
        return $this->get( 'order_number_suffix', '' );
    }

    /**
     * Add prefix and suffix to a given order number.
     *
     * @param  string|int $number
     * @return string
     */
    public function get_order_number( $number )
    {
        $prefix = $this->get_order_number_prefix();
        $suffix = $this->get_order_number_suffix();
        $result = $prefix . $number . $suffix;
        return $result;
    }

    /**
     * Set the invoice_email setting.
     *
     * @param  string $value
     * @return void
     */
    public function set_invoice_email( $value )
    {
        $this->set( 'invoice_email' , $value );
    }

    /**
     * Get the invoice_email setting.
     *
     * @return string
     */
    public function get_invoice_email()
    {
        return $this->get( 'invoice_email', '' );
    }

    /**
     * Decide if invoices are to be created on order status 'completed'.
     *
     * @return bool
     */
    public function append_invoice_to_email()
    {
        return $this->get( 'invoice_email', 'append' ) === 'append';
    }

    /**
     * Decide if invoices are to be created on order status 'completed'.
     *
     * @return bool
     */
    public function send_invoice_as_email()
    {
        return $this->get( 'invoice_email', 'append' ) === 'separate';
    }

    /**
     * Set the setting to which email append invoices to.
     *
     * @param  string $value
     * @return void
     */
    public function set_email_to_append_to( $value )
    {
        $this->set( 'email_to_append_to' , $value );
    }

    /**
     * Get the setting to which email append invoices to.
     *
     * @return array<string>
     */
    public function get_email_to_append_to()
    {
        $value = $this->get( 'email_to_append_to', array( 'customer_processing_order' ) );
        if ( !is_array( $value ) ) {
            $value = array( $value );
        }
        return $value;
    }

    /**
     * Decide whether to append invoice to certain email type.
     *
     * @param  string $value
     * @return bool
     */
    public function append_invoice_to_email_type( $value )
    {
        return in_array( $value, $this->get_email_to_append_to() );
    }

    /**
     * Set the email for states setting.
     *
     * @param  array<string>|string $value
     * @return void
     */
    public function set_email_for_states( $value )
    {
        $this->set( 'email_for_states' , $value );
    }

    /**
     * Get the status for which invoices are to be sent as an email.
     *
     * @return array<string>|string
     */
    public function get_email_for_states()
    {
        return $this->get( 'email_for_states', array() );
    }

    /**
     * Decide if to send an email for a particular order status.
     *
     * @param  string $state
     * @return bool
     */
    public function send_email_for_state( $state )
    {
        return in_array( $state, $this->get_email_for_states() );
    }

    /**
     * Set the email for methods setting.
     *
     * @param  array<string> $value
     * @return void
     */
    public function set_no_email_for_methods( $value )
    {
        $this->set( 'no_email_for_methods', $value );
    }

    /**
     * Get the payment methods for which invoices are to be sent as an email.
     *
     * @return array<string>
     */
    public function get_no_email_for_methods()
    {
        return $this->get( 'no_email_for_methods', array() );
    }

    /**
     * Decide if to send an email for a particular payment method.
     *
     * @param  string $method
     * @return bool
     */
    public function send_email_for_method( $method )
    {
        return ! in_array( $method, $this->get_no_email_for_methods() );
    }

    /**
     * Decide if to send an email for a given parameters.
     *
     * @param  string $state
     * @param  string $method
     * @return bool
     */
    public function send_email_for( $state, $method )
    {
        return $this->send_email_for_state( $state )
            && $this->send_email_for_method( $method );
    }

    /**
     * Set the email template setting.
     *
     * @param  string $value
     * @return void
     */
    public function set_email_template( $value )
    {
        $this->set( 'email_template', $value );
    }

    /**
     * Get the template to build the email that is to be sent.
     *
     * @return string
     */
    public function get_email_template()
    {
        return $this->get( 'email_template', 'none' );
    }

    /**
     * Set the email filename setting.
     *
     * @param  string $value
     * @return void
     */
    public function set_email_filename( $value )
    {
        $this->set( 'email_filename', $value );
    }

    /**
     * Get the name of the file that is to be sent via email.
     *
     * @return string
     */
    public function get_email_filename()
    {
        return $this->get( 'email_filename', 'Rechnung' );
    }

    /**
     * Set the email to setting.
     *
     * @param  string $value
     * @return void
     */
    public function set_email_to( $value )
    {
        $this->set( 'email_to', $value );
    }

    /**
     * Get the to of the email to be sent to the customer.
     *
     * @return string
     */
    public function get_email_to()
    {
        return $this->get( 'email_to', '' );
    }

    /**
     * Return an array of recipients from the email section.
     *
     * @return array<string>
     */
    public function send_email_to()
    {
        $emails = preg_split( '/[\s;,]/i', trim( $this->get_email_to() ) );
        $emails = array_filter( $emails , function ( $email ) { return is_email( $email ); });
        return $emails;
    }

    /**
     * Set the email copy setting.
     *
     * @param  string $value
     * @return void
     */
    public function set_email_copy( $value )
    {
        $this->set( 'email_copy', $value );
    }

    /**
     * Get the carbon copy of the email to be sent to the customer.
     *
     * @return string
     */
    public function get_email_copy()
    {
        return $this->get( 'email_copy', '' );
    }

    /**
     * Return an array of recipients from the email copy section.
     *
     * @return array<string>
     */
    public function send_email_copies_to()
    {
        $emails = preg_split( '/[\s;,]/i', trim( $this->get_email_copy() ) );
        $emails = array_filter( $emails , function ( $email ) { return is_email( $email ); });
        return $emails;
    }

    /**
     * Set the email blind copy setting.
     *
     * @param  string $value
     * @return void
     */
    public function set_email_blind_copy( $value )
    {
        $this->set( 'email_blind_copy', $value );
    }

    /**
     * Get the blind carbon copy of the email to be sent to the customer.
     *
     * @return string
     */
    public function get_email_blind_copy()
    {
        return $this->get( 'email_blind_copy', '' );
    }

    /**
     * Return an array of recipients from the email blind copy section.
     *
     * @return array<string>
     */
    public function send_email_blind_copies_to()
    {
        $emails = preg_split( '/[\s;,]/i', trim( $this->get_email_blind_copy() ) );
        $emails = array_filter( $emails , function ($email) { return is_email( $email ); });
        return $emails;
    }

    /**
     * Set the email filename setting.
     *
     * @param  string $value
     * @return void
     */
    public function set_email_subject( $value )
    {
        $this->set( 'email_subject', $value );
    }

    /**
     * Get the subject of the email to be sent to the customer.
     *
     * @return string|null
     */
    public function get_email_subject()
    {
        return $this->get( 'email_subject' );
    }

    /**
     * Set the email content text setting.
     *
     * @param  string $value
     * @return void
     */
    public function set_email_content_text( $value )
    {
        $this->set( 'email_content_text', $value );
    }

    /**
     * Get the text content of the email to be sent to the customer.
     *
     * @return string|null
     */
    public function get_email_content_text()
    {
        return $this->get( 'email_content_text', '' );
    }

    /**
     * Set the email filename setting.
     *
     * @param  string $value
     * @return void
     */
    public function set_email_content_html( $value )
    {
        $this->set( 'email_content_html', FP_Plugin::encode_html_content( $value ) );
    }

    /**
     * Get the html content of the email to be sent to the customer.
     *
     * @return string|null
     */
    public function get_email_content_html()
    {
        return FP_Plugin::decode_html_content( $this->get( 'email_content_html', '' ) );
    }
}

endif;
