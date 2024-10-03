<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'FP_Order_Adapter' ) ):

/**
 * Faktur Pro Order Adapter Class
 *
 * This adapter is used to conveniently operate on the
 * WooCommerce WC_Order class within the plugin.
 *
 * @class    FP_Order_Adapter
 * @version  1.0.0
 * @package  FakturPro\Support
 * @author   Zweischneider
 */
final class FP_Order_Adapter
{
    /**
     * The meta data key for invoice identifiers.
     *
     * @var string INVOICE_META_KEY
     */
    const INVOICE_META_KEY = 'fakturpro_uuid';

    /**
     * The old meta key for invoice identifiers.
     *
     * @var string INVOICE_OLD_KEY
     */
    const INVOICE_OLD_KEY = 'woo_invoices_invoice_id';

    /**
     * The meta key for invoice numbers.
     *
     * @var string INVOICE_NO_KEY
     */
    const INVOICE_NO_KEY = 'fakturpro_number';

    /**
     * The meta key for invoice numbers.
     *
     * @var string INVOICE_DATE
     */
    const INVOICE_DATE = 'fakturpro_invoice_date';

    /**
     * The meta key for invoice was canceled
     * 
     * @var string INVOICE_CANCELED
     */
    const INVOICE_CANCELED = 'fakturpro_invoice_canceled';

    /**
     * The meta key for invoice emails.
     *
     * @var string INVOICE_EMAIL_KEY
     */
    const INVOICE_EMAIL_KEY = 'fakturpro_email';

    /**
     * The meta key for invoice appended to email.
     *
     * @var string INVOICE_APPENDED_TO_EMAIL
     */
    const INVOICE_APPENDED_TO_EMAIL = 'fakturpro_invoice_appended_to_email';

    /**
     * The meta key for invoice error message.
     *
     * @var string INVOICE_ERROR_MESSAGE
     */
    const INVOICE_ERROR_MESSAGE = 'fakturpro_invoice_error_message';

    /**
     * The mapping of billing and shipping title to strings.
     *
     * This is used for the WooCommerce Germanized plugin that adds
     * billing and shipping title values.
     *
     * @var array<int, string>
     */
    private static $map_titles = array( 1 => 'Herr', 2 => 'Frau' );

    /**
     * The order instance to operate on.
     *
     * @var WC_Order|null
     */
    private $order;

    /**
     * Create a new instance of the order adapter.
     *
     * @param  mixed $order
     * @return void
     */
    public function __construct( $order )
    {
        if ( is_a( $order, 'WC_Order' ) ) {
            $this->order = $order;
            return;
        }
        $this->load_order( $order );
    }

    /**
     * Make all order methods directly accessible.
     *
     * @param  string $method
     * @param  mixed  $args
     * @return mixed
     */
    public function __call( $method, $args )
    {
        if (empty($this->order)) {
            return null;
        }

        if (empty($args)) {
            /*return is_callable( array( $this->order, $method ) )
                ? $this->order->{$method}()
                : null;*/
            return call_user_func( array( $this->order, $method ) );
        }

        /* return is_callable( array($this->order, $method ) )
            ? $this->order->{$method}($args)
            : null; */
        return call_user_func_array( array( $this->order, $method ), $args );
    }

    /**
     * Load the actual order object using the ID.
     *
     * @param  mixed $order_id
     * @return void
     */
    private function load_order( $order_id )
    {
        $this->order = function_exists('wc_get_order') ? wc_get_order( $order_id ) : null;
    }

    /**
     * Return the order object this class operates on.
     *
     * @return WC_Order|null
     */
    public function get_order()
    {
        return $this->order;
    }

    /**
     * Decide if the order has an invoice meta key attached to.
     *
     * @return bool
     */
    public function has_invoice_key()
    {
        return $this->has_invoice_uuid() || $this->has_invoice_id();
    }

    /**
     * Get the meta key attached to this order.
     *
     * @return string|null
     */
    public function get_invoice_key()
    {
        return $this->has_invoice_uuid() ? $this->get_invoice_uuid() : $this->get_invoice_id();
    }

    /**
     * Unset the invoice key (UUID or ID) for the order and save it.
     *
     * @return void
     */
    public function unset_invoice_key()
    {
        $this->order->delete_meta_data( self::INVOICE_META_KEY );
        $this->order->meta_exists( self::INVOICE_OLD_KEY);
        $this->order->save();
    }

    /**
     * Return the old invoice ID attached to the order.
     *
     * @return string|null
     */
    public function get_invoice_id()
    {
        return trim( $this->order->get_meta( self::INVOICE_OLD_KEY ) );
    }

    /**
     * Decide if this order has an old invoice ID attached to.
     *
     * @return bool
     */
    public function has_invoice_id()
    {
        return $this->order->meta_exists( self::INVOICE_OLD_KEY );
    }

    /**
     * Unset the invoice UUID for the order and save it.
     *
     * @return void
     */
    public function unset_invoice_id()
    {
        $this->order->delete_meta_data( self::INVOICE_OLD_KEY );
        $this->order->save();
    }

    /**
     * Decide if the order has an invoice UUID attached to.
     *
     * @return bool
     */
    public function has_invoice_uuid()
    {
        return $this->order->meta_exists( self::INVOICE_META_KEY );
    }

    /**
     * Return the invoice UUID attached to the order.
     *
     * @return string|null
     */
    public function get_invoice_uuid()
    {
        return trim( $this->order->get_meta( self::INVOICE_META_KEY, true ) );
    }

    /**
     * Set the invoice UUID for the order and save it.
     *
     * @param  string $uuid
     * @return void
     */
    public function set_invoice_uuid( $uuid )
    {
        $this->order->update_meta_data( self::INVOICE_META_KEY, $uuid );
        $this->order->save();
    }

    /**
     * Unset the invoice UUID for the order and save it.
     *
     * @return void
     */
    public function unset_invoice_uuid()
    {
        $this->order->delete_meta_data( self::INVOICE_META_KEY );
        $this->order->save();
    }

    /**
     * Decide if this order has an invoice number attached to.
     *
     * @return bool
     */
    public function has_invoice_number()
    {
        return $this->order->meta_exists( self::INVOICE_NO_KEY );
    }

    /**
     * Return the invoice number attached to the order.
     *
     * @return string|null
     */
    public function get_invoice_number()
    {
        return trim( $this->order->get_meta( self::INVOICE_NO_KEY, true ) );
    }

    /**
     * Set the invoice UUID for the order and save it.
     *
     * @param  string $number
     * @return void
     */
    public function set_invoice_number( $number )
    {
        $this->order->update_meta_data( self::INVOICE_NO_KEY, $number );
        $this->order->save();
    }

    /**
     * Unset the invoice UUID for the order and save it.
     *
     * @return void
     */
    public function unset_invoice_number()
    {
        $this->order->delete_meta_data( self::INVOICE_NO_KEY );
        $this->order->save();
    }

    /**
     * Return the invoice date attached to the order.
     *
     * @return string|int|null
     */
    public function get_invoice_date()
    {
        return trim( $this->order->get_meta( self::INVOICE_DATE, true ) );
    }

    /**
     * Set the invoice date for the order and save it.
     *
     * @param  string $date
     * @return void
     */
    public function set_invoice_date( $date )
    {
        $this->order->update_meta_data( self::INVOICE_DATE, $date );
        $this->order->save();
    }

    /**
     * Unset the invoice date for the order and save it.
     *
     * @return void
     */
    public function unset_invoice_date()
    {
        $this->order->delete_meta_data( self::INVOICE_DATE );
        $this->order->save();
    }

    /**
     * Retrieve the value for the invoice was canceled.
     *
     * @return string
     */
    public function get_invoice_canceled()
    {
        $value = $this->order->get_meta( self::INVOICE_CANCELED, true, 'edit' );
        return !empty( $value ) ? $value : 'no';
    }

    /**
     * Set the invoice was canceled for this order.
     *
     * @param  string $value
     * @return void
     */
    public function set_invoice_canceled( $value = "yes" )
    {
        $this->order->update_meta_data( self::INVOICE_CANCELED, $value );
        $this->order->save();
    }

    /**
     * Decide if the invoice was canceled.
     *
     * @return bool
     */
    public function has_invoice_canceled()
    {
        return $this->get_invoice_canceled() === "yes";
    }

    /**
     * Unset the invoice canceled value for the order and save it.
     *
     * @return void
     */
    public function unset_invoice_canceled()
    {
        $this->order->delete_meta_data( self::INVOICE_CANCELED );
        $this->order->save();
    }

    /**
     * Retrieve the value for the invoice email status.
     *
     * @return string
     */
    public function get_invoice_email()
    {
        $value = $this->order->get_meta( self::INVOICE_EMAIL_KEY, true, 'edit' );
        return !empty( $value ) ? $value : 'no';
    }

    /**
     * Set the invoice email value for the order and save it.
     *
     * @param  string $value
     * @return void
     */
    public function set_invoice_email( $value )
    {
        $this->order->update_meta_data( self::INVOICE_EMAIL_KEY, $value);
        $this->order->save();
    }

    /**
     * Decide if the invoice email has been sent to the customer.
     *
     * @return bool
     */
    public function has_sent_invoice_email()
    {
        return $this->get_invoice_email() === "yes";
    }

    /**
     * Set the invoice email to be sent for this order.
     *
     * @return void
     */
    public function set_sent_invoice_email()
    {
        $this->set_invoice_email("yes");
    }

    /**
     * Unset the invoice email value for the order and save it.
     *
     * @return void
     */
    public function unset_invoice_email()
    {
        $this->order->delete_meta_data( self::INVOICE_EMAIL_KEY );
        $this->order->save();
    }

    /**
     * Retrieve the value for the invoice appended to email status.
     *
     * @return string
     */
    public function get_invoice_appended_to_email()
    {
        $value = $this->order->get_meta( self::INVOICE_APPENDED_TO_EMAIL, true, 'edit' );
        return !empty( $value ) ? $value : 'no';
    }

    /**
     * Set the invoice appended to email value for the order and save it.
     *
     * @param  string $value
     * @return void
     */
    public function set_invoice_appended_to_email( $value = 'yes' )
    {
        $this->order->update_meta_data( self::INVOICE_APPENDED_TO_EMAIL, $value);
        $this->order->save();
    }

    /**
     * Decide if the invoice appended to email has been sent to the customer.
     *
     * @return bool
     */
    public function has_invoice_appended_to_email()
    {
        return $this->get_invoice_appended_to_email() === "yes";
    }

    /**
     * Unset the invoice appended to email value for the order and save it.
     *
     * @return void
     */
    public function unset_invoice_appended_to_email()
    {
        $this->order->delete_meta_data( self::INVOICE_APPENDED_TO_EMAIL );
        $this->order->save();
    }

    /**
     * Retrieve the value for the invoice appended to email status.
     *
     * @return string
     */
    public function get_invoice_error_message()
    {
        $value = $this->order->get_meta( self::INVOICE_ERROR_MESSAGE, true, 'edit' );
        return !empty( $value ) ? $value : '';
    }

    /**
     * Set the invoice error message value for the order and save it.
     *
     * @param  string $value
     * @return void
     */
    public function set_invoice_error_message( $value )
    {
        $value = str_replace(array('<br>', '<br/>', '<br\/>', '<br />', '<br \/>'), "\n", $value);
        $value = str_replace(array('<ul>', '</ul>'), array('', "\n"), $value);
        $value = str_replace(array('<li>', '</li>'), array('- ', "\n"), $value);
        $value = str_replace(array('<p>', '</p>'), array('', "\n\n"), $value);
        $value = strip_tags($value);
        $this->order->update_meta_data( self::INVOICE_ERROR_MESSAGE, trim($value) );
        $this->order->save();
    }

    /**
     * Decide if the invoice error message has been set to the order.
     *
     * @return bool
     */
    public function has_invoice_error_message()
    {
        return !empty( $this->get_invoice_error_message() );
    }

    /**
     * Unset the invoice error message value for the order and save it.
     *
     * @return void
     */
    public function unset_invoice_error_message()
    {
        $this->order->delete_meta_data( self::INVOICE_ERROR_MESSAGE );
        $this->order->save();
    }

    /**
     * Add a note to the order history.
     *
     * @param  string $message
     * @return void
     */
    public function add_note( $message )
    {
        $this->order->add_order_note( $message );
    }

    /**
     * Add a note that an invoice has been created.
     *
     * @return void
     */
    public function add_note_invoice_created()
    {
        $this->add_note( __( 'Invoice created', 'fakturpro' ) );
    }

    /**
     * Add a note that an invoice has been cancelled.
     *
     * @return void
     */
    public function add_note_invoice_cancelled()
    {
        $this->add_note( __( 'Invoice canceled', 'fakturpro' ) );
    }

    /**
     * Add a note that an invoice has been completed.
     *
     * @return void
     */
    public function add_note_invoice_completed()
    {
        $this->add_note( __( 'Invoice completed', 'fakturpro' ) );
    }

    /**
     * Add a note that an invoice has been appended to the WooCommerce email.
     *
     * @return void
     */
    public function add_note_invoice_appended()
    {
        $this->add_note( __( 'Invoice sent with WooCommerce email.', 'fakturpro' ) );
    }

    /**
     * Add a note that an invoice been sent as email.
     *
     * @return void
     */
    public function add_note_invoice_emailed()
    {
        $this->add_note( __( 'Invoice sent as email.', 'fakturpro' ) );
    }

    /**
     * Return the billing vat id for this order.
     *
     * This method returns the billing vat id added to the order object
     * by extensions like WooCommerce Germanized or other.
     *
     * @param  array<string> $meta_fields
     * @return string|null
     */
    public function get_billing_vat_id( $meta_fields = null )
    {
        $meta_fields = !empty($meta_fields) ? $meta_fields : array(
            '_billing_eu_vat_id',
            'billing_eu_vat_id',
            '_billing_vat',
            'billing_vat',              // WP Plugin: German Market
            '_billing_vat_id',          // WP Plugin: Germanized
            'billing_vat_id',
            '_billing_eu_vat_number',   // WP Plugin: EU VAT for WooCommerce
            'billing_eu_vat_number',
            '_vat_number',              // WP Plugin: EU VAT Number
            'vat_number',               // WP Plugin: EU VAT Assistant
            'VAT Number',               // WP Plugin: WooCommerce EU/UK VAT Compliance
            'vat number',
            '_eu_vat_id',
            'eu_vat_id',
            '_vat_id',
            'vat_id',
            '_eu_vat_number',
            'eu_vat_number',
            '_vat_number',
            'vat_number',
            'vatno',
            'wwp_wholesaler_tax_id',    // WP Plugin: Wholesale for WooCommerce
        );

        // Search for vat id in order meta
        foreach ($meta_fields as $meta_field) {
            $billing_vat_id = $this->order->get_meta( $meta_field, true );
            if (!empty($billing_vat_id)) {
                return $billing_vat_id;
            }
        }

        // Search for vat id in customer meta if no vat id was found in order meta
        $user = $this->order->get_user();
        if (!empty($user)) {
            foreach ($meta_fields as $meta_field) {
                try {
                    $billing_vat_id = $user->$meta_field;
                    if (!empty($billing_vat_id)) {
                        return $billing_vat_id;
                    }
                } catch (\Exception $ex) { // @phpstan-ignore-line
                    // ...
                }
            }
        }
        return null;
    }

    /**
     * Return the shipping vat id for this order.
     *
     * This method returns the shipping vat id the WooCommerce
     * Germanized extension adds to the order object.
     *
     * @return string|null
     */
    public function get_shipping_vat_id()
    {
        $meta_fields = array(
            '_shipping_eu_vat_id',
            'shipping_eu_vat_id',
            '_shipping_vat_id',         // WP Plugin: Germanized
            'shipping_vat_id',
            '_shipping_eu_vat_number',  // WP Plugin: EU VAT for WooCommerce
            'shipping_eu_vat_number',
            '_shipping_vat_number',
            'shipping_vat_number',
        );

        // Search for vat id in order meta
        foreach ($meta_fields as $meta_field) {
            $billing_vat_id = $this->order->get_meta( $meta_field, true );
            if (!empty($billing_vat_id)) {
                return $billing_vat_id;
            }
        }
        return null;
    }

    /**
     * Return used credits array with name and amount.
     *
     * @return array<array<string, mixed>>
     */
    public function get_credits_used()
    {
        $credits = array();

        // WP Plugin: Smart Coupons for WooCommerce
        $wt_store_credit_used = $this->order->get_meta( 'wt_store_credit_used', true, 'edit' );
        if (!empty($wt_store_credit_used)) {
            foreach ($wt_store_credit_used as $key => $value) {
                $credits[] = [
                    'name' => $key,
                    'amount' => $value,
                ];
            }
        }

        return $credits;
    }

    /**
     * Return the tax id for an order item.
     *
     * @return int
     */
    public function get_item_tax_id(WC_Order_Item $item)
    {
        if ( is_callable( array( $item, 'get_taxes' ) ) ) {
            $item_taxes = $item->get_taxes();
            $taxes = !empty($item_taxes['subtotal']) ? $item_taxes['subtotal'] : $item_taxes['total'];

            // Find first tax rate id with tax amount
            foreach ($taxes as $tax_rate_id => $tax_amount) {
                if (!empty($tax_amount)) {
                    return $tax_rate_id;
                }
            }

            // If all tax rate ids are with tax amount of 0 return latest tax rate id
            if ( !empty( $tax_rate_id ) ) {
                return $tax_rate_id;
            }
        }
        return 0;
    }

    /**
     * Check if a key represents a valid billing or shipping title key.
     *
     * @param  int|null $key
     * @return bool
     */
    private static function has_title( $key )
    {
        return array_key_exists( intval( $key ) , self::$map_titles );
    }

    /**
     * Map a billing or shipping title key to the correct string representation.
     *
     * @param  int|null $key
     * @return string|null
     */
    private static function map_title( $key )
    {
        return self::has_title( $key ) ? self::$map_titles[$key] : null;
    }

    /**
     * Return the billing title value for this order.
     *
     * @return string|null
     */
    public function get_billing_title()
    {
        $key = $this->order->get_meta( '_billing_title', true, 'edit' );
        return self::map_title( $key );
    }

    /**
     * Return the shipping title value for this order.
     *
     * @return string|null
     */
    public function get_shipping_title()
    {
        $key = $this->order->get_meta( '_shipping_title', true, 'edit' );
        return self::map_title( $key );
    }

    /**
     * Decide if the order is a normal order or something other like a subscription order
     *
     * @return bool
     */
    public function is_order_type()
    {
        return $this->order->get_type() === 'shop_order';
    }

    /**
     * Decide if the order is a subscription
     *
     * @return bool
     */
    public function is_subscription_type()
    {
        return $this->order->get_type() === 'shop_subscription';
    }

    /**
     * Decide if this order has an active state.
     *
     * Active states are 'pending', 'processing', 'on-hold'
     * and 'completed'.
     *
     * @return bool
     */
    public function is_outstanding()
    {
        return !$this->is_cancelled();
    }

    /**
     * Decide if this order has been cancelled.
     *
     * @return bool
     */
    public function is_cancelled()
    {
        return $this->order->has_status( array('cancelled', 'refunded') );
    }

    /**
     * Decide if this order is vat exempt.
     *
     * @return bool
     */
    public function is_vat_exempt()
    {
        return 'yes' === $this->order->get_meta( 'is_vat_exempt', true, 'edit' );
    }

    /**
     * Get customer of order.
     *
     * @return WC_Customer
     */
    public function get_customer()
    {
        return new WC_Customer( $this->order->get_customer_id() );
    }

    /**
     * Reset invoice.
     *
     * @return void
     */
    public function reset_invoice()
    {
        $this->unset_invoice_key();
        $this->unset_invoice_number();
        $this->unset_invoice_date();
        $this->unset_invoice_canceled();
        $this->unset_invoice_error_message();

        $this->unset_create_invoice_requested_at();
        $this->unset_complete_invoice_requested_at();
        $this->unset_cancel_invoice_requested_at();
        $this->unset_refund_invoice_requested_at();
    }

    /**
     * Set the create invoice requested at time.
     *
     * @return void
     */
    public function set_create_invoice_requested_at()
    {
        $now = new \DateTime('now');
        $this->update_meta_data( '_fakturpro_create_invoice_requested_at', $now->format('c') );
    }

    /**
     * Delete the create invoice requested at time.
     *
     * @return void
     */
    public function unset_create_invoice_requested_at()
    {
        $this->delete_meta_data( '_fakturpro_create_invoice_requested_at' );
    }

    /**
     * Decide if the wating time for create invoice request is passed.
     *
     * @return bool
     */
    public function is_create_invoice_request_waiting_time_passed()
    {
        $requested_at = $this->get_meta( '_fakturpro_create_invoice_requested_at', true, 'edit' );
        if ( empty($requested_at) ) {
            return true;
        }
        $requested_at = new \DateTime($requested_at);
        $check_time = new \DateTime('now');
        $check_time->sub(new \DateInterval('PT2M'));
        return $requested_at < $check_time;
    }

    /**
     * Set the complete invoice requested at time.
     *
     * @return void
     */
    public function set_complete_invoice_requested_at()
    {
        $now = new \DateTime('now');
        $this->update_meta_data( '_fakturpro_complete_invoice_requested_at', $now->format('c') );
    }

    /**
     * Delete the complete invoice requested at time.
     *
     * @return void
     */
    public function unset_complete_invoice_requested_at()
    {
        $this->delete_meta_data( '_fakturpro_complete_invoice_requested_at' );
    }

    /**
     * Decide if the wating time for complete invoice request is passed.
     *
     * @return bool
     */
    public function is_complete_invoice_request_waiting_time_passed()
    {
        $requested_at = $this->get_meta( '_fakturpro_complete_invoice_requested_at', true, 'edit' );
        if ( empty($requested_at) ) {
            return true;
        }
        $requested_at = new \DateTime($requested_at);
        $check_time = new \DateTime('now');
        $check_time->sub(new \DateInterval('PT2M'));
        return $requested_at < $check_time;
    }

    /**
     * Set the cancel invoice requested at time.
     *
     * @return void
     */
    public function set_cancel_invoice_requested_at()
    {
        $now = new \DateTime('now');
        $this->update_meta_data( '_fakturpro_cancel_invoice_requested_at', $now->format('c') );
    }

    /**
     * Delete the cancel invoice requested at time.
     *
     * @return void
     */
    public function unset_cancel_invoice_requested_at()
    {
        $this->delete_meta_data( '_fakturpro_cancel_invoice_requested_at' );
    }

    /**
     * Decide if the wating time for cancel invoice request is passed.
     *
     * @return bool
     */
    public function is_cancel_invoice_request_waiting_time_passed()
    {
        $requested_at = $this->get_meta( '_fakturpro_cancel_invoice_requested_at', true, 'edit' );
        if ( empty($requested_at) ) {
            return true;
        }
        $requested_at = new \DateTime($requested_at);
        $check_time = new \DateTime('now');
        $check_time->sub(new \DateInterval('PT2M'));
        return $requested_at < $check_time;
    }

    /**
     * Set the refund invoice requested at time.
     *
     * @return void
     */
    public function set_refund_invoice_requested_at()
    {
        $now = new \DateTime('now');
        $this->update_meta_data( '_fakturpro_refund_invoice_requested_at', $now->format('c') );
    }

    /**
     * Delete the refund invoice requested at time.
     *
     * @return void
     */
    public function unset_refund_invoice_requested_at()
    {
        $this->delete_meta_data( '_fakturpro_refund_invoice_requested_at' );
    }

    /**
     * Decide if the wating time for refund invoice request is passed.
     *
     * @return bool
     */
    public function is_refund_invoice_request_waiting_time_passed()
    {
        $requested_at = $this->get_meta( '_fakturpro_refund_invoice_requested_at', true, 'edit' );
        if ( empty($requested_at) ) {
            return true;
        }
        $requested_at = new \DateTime($requested_at);
        $check_time = new \DateTime('now');
        $check_time->sub(new \DateInterval('PT2M'));
        return $requested_at < $check_time;
    }

    /**
     * Trigger invoice created action for this order.
     *
     * @return void
     */
    public function do_action_invoice_created()
    {
        do_action( 'fakturpro_invoice_created' , $this );
    }

    /**
     * Trigger invoice cancelled action for this order.
     *
     * @return void
     */
    public function do_action_invoice_cancelled()
    {
        do_action( 'fakturpro_invoice_cancelled' , $this );
    }
}

endif;
