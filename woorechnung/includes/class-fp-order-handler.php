<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'FP_Order_Handler' ) ):

/**
 * Faktur Pro Order Handler Module.
 *
 * This class automatically processes order on status
 * update and performs the subsequent action if the preconditions
 * fir the user settings.
 *
 * @version  1.0.0
 * @package  FakturPro
 * @author   Zweischneider
 */
final class FP_Order_Handler extends FP_Abstract_Module
{
    /**
     * Initialize the hooks of this module.
     *
     * @return void
     */
    public function init_hooks()
    {
        // Action for processing order after order fully created
        $this->add_action('woocommerce_gzd_checkout_order_before_confirmation', 'process_order_object', 9, 1);

        // Action for WooCommerce Subscriptions renewal order created
        $this->add_Filter('wcs_renewal_order_created', 'process_order_object_filter', 9, 1);

        // Actions for processing order on status update
        $statuses = $this->plugin()->get_order_statuses();
        foreach ( $statuses as $status => $name ) {
            $this->add_action("woocommerce_order_status_{$status}", 'process_order_on_status', 9, 1);
        }
        // $this->add_action('woocommerce_order_status_pending', 'process_order_on_status', 9, 1);
        $this->add_action('woocommerce_order_status_changed', 'process_order_on_status_changed', 9, 4);
        $this->add_action('woocommerce_checkout_order_processed', 'process_order_on_checkout', 9, 3);

        // Actions for processing subscription order on status update
        /*if ($this->plugin()->is_woocommerce_subscriptions_active()) {
            foreach ( $statuses as $status => $name ) {
                $this->add_action("woocommerce_subscription_status_{$status}", 'process_order_object', 9, 1);
            }
        }*/

        // Don't carry fakturpro meta data to subscription
        $this->add_filter('wcs_subscription_meta_query', 'remove_subscription_meta_query', 10);
        $this->add_filter('wcs_subscription_meta', 'exclude_core_renewal_order_meta_properties');

		// Don't carry fakturpro meta data to renewal orders
		$this->add_filter('wcs_renewal_order_meta_query', 'remove_renewal_order_meta_query', 10);
        $this->add_filter('wcs_renewal_order_meta', 'exclude_core_renewal_order_meta_properties');
    }

    /**
	 * Do not carry over fakturpro related meta data to subscriptions.
     *
     * @param  string $order_meta_query
     * @return string
	 */
	public static function remove_subscription_meta_query( $order_meta_query )
    {
        return self::remove_renewal_order_meta_query( $order_meta_query );
    }

    /**
	 * Do not carry over fakturpro related meta data to renewal orders.
     *
     * @param  string $order_meta_query
     * @return string
	 */
	public static function remove_renewal_order_meta_query( $order_meta_query )
    {
        $fields_to_ignore = array(
            'woo_invoices_invoice_id', // old id key
            'fakturpro_uuid',
            'fakturpro_number',
            'fakturpro_email',
            'fakturpro_invoice_date',
            'fakturpro_invoice_appended_to_email',
        );

        $ignore = implode("', '", $fields_to_ignore);

		$order_meta_query .= " AND `meta_key` NOT IN ('{$ignore}')";

		return $order_meta_query;
	}

	/**
	 * Excludes core order meta properties from the meta copied from the subscription.
	 *
	 * @param  array<array<string, mixed>> $order_meta The meta keys and values to copy from the subscription to the early renewal order.
	 * @return array<array<string, mixed>> The subscription meta to copy to the early renewal order.
	 */
	public function exclude_core_renewal_order_meta_properties( $order_meta )
    {
		$excluded_meta_keys = array(
            'woo_invoices_invoice_id', // old id key
            'fakturpro_uuid',
            'fakturpro_number',
            'fakturpro_email',
            'fakturpro_invoice_date',
            'fakturpro_invoice_appended_to_email',
		);

		foreach ( $order_meta as $index => $meta ) {
			if ( !empty( $meta[ 'meta_key' ] ) && in_array( $meta['meta_key'], $excluded_meta_keys, true ) ) {
				unset( $order_meta[ $index ] );
			}
		}

		return $order_meta;
	}

    /**
     * Automatically process the order without status update (e.g. on order created) for filter hooks.
     *
     * @param  WC_Order $order
     * @return WC_Order
     */
    public function process_order_object_filter( $order )
    {
        $this->process_order_object( $order );
        return $order;
    }

    /**
     * Automatically process the order without status update (e.g. on order created).
     *
     * @param  WC_Order|null $order
     * @return void
     */
    public function process_order_object( $order )
    {
        $order_id = !empty( $order ) ? $order->get_id() : '';
        $this->logger()->verbose( 'CALL', [ 'order_id' => $order_id, 'wp_filter' => current_filter() ] );
        $this->process_order( new FP_Order_Adapter( $order_id ) );
    }

    /**
     * Automatically process new orders on checkout.
     *
     * @param  int $order_id
     * @param  array<array<string, mixed>> $post_data
     * @param  WC_Order $order
     * @return void
     */
    public function process_order_on_checkout( $order_id, $post_data, $order )
    {
        $this->logger()->verbose( 'CALL', [
            'order_id' => $order_id,
            'status' => $order->get_status(),
            'wp_filter' => current_filter()
        ] );
        $this->process_order( new FP_Order_Adapter($order_id) );
    }

    /**
     * Automatically process the order on a status changed.
     *
     * @param  int $order_id
     * @param  string $from
     * @param  string $to
     * @param  WC_Order $order
     * @return void
     */
    public function process_order_on_status_changed( $order_id, $from, $to, $order )
    {
        $this->logger()->verbose( 'CALL', [
            'order_id' => $order_id,
            'from' => $from,
            'to' => $to,
            'wp_filter' => current_filter()
        ] );
        $this->process_order( new FP_Order_Adapter( $order_id ) );
    }

    /**
     * Automatically process the order on a status update.
     *
     * @param  int $order_id
     * @return bool
     */
    public function process_order_on_status( $order_id )
    {
        $this->logger()->verbose( 'CALL', [ 'order_id' => $order_id, 'wp_filter' => current_filter() ] );
        return $this->process_order( new FP_Order_Adapter( $order_id ) );
    }

    /**
     * Automatically process the order on a status update.
     *
     * @param  FP_Order_Adapter $order
     * @return bool
     */
    public function process_order( $order )
    {
        if (!$order->is_order_type()) {
            $this->logger()->verbose( 'IF !$order->is_order_type()' );
            return true;
        }

        if ( $order->is_outstanding() ) {
            $this->logger()->verbose( 'IF $order->is_outstanding()' );
            return $this->maybe_create_invoice( $order );
        }

        if ( $order->is_cancelled() ) {
            $this->logger()->verbose( 'IF $order->is_cancelled()' );
            return $this->maybe_cancel_invoice( $order );
        }

        return true;
    }

    /**
     * Maybe create an invoice if all prerequisites are fullfilled.
     *
     * @param  FP_Order_Adapter|null $order
     * @return bool
     */
    private function maybe_create_invoice( $order )
    {
        $order_id = !empty( $order ) ? $order->get_id() : '';
        $this->logger()->verbose( 'CALL', [ 'order_id' => $order_id ] );

        if ( $order->has_invoice_key() || $order->has_invoice_error_message() ) {
            return false;
        }

        $settings = $this->settings();
        if ( ! $settings->get_create_invoices() ) {
            $this->logger()->verbose( 'IF !$settings->get_create_invoices() IN' );
            return false;
        }

        $status = $order->get_status();
        if ( ! $settings->create_invoice_for_state( $status ) ) {
            $this->logger()->verbose( 'IF !$settings->create_invoice_for_state("'.$status.'") IN', [
                'settings' => [ 'invoice_for_states' => $settings->get_invoice_for_states() ]
            ] );
            return false;
        }

        $method = $order->get_payment_method();
        if ( ! $settings->create_invoice_for_method( $method ) ) {
            $this->logger()->verbose( 'IF !$settings->create_invoice_for_method("'.$method.'") IN' );
            return false;
        }

        $value = $order->get_total();
        if ( ! $settings->create_invoice_for_value( $value ) ) {
            $this->logger()->verbose( 'IF !$settings->create_invoice_for_value('.$value.') IN' );
            return false;
        }

        $this->logger()->verbose( 'SUCCESS', [
            'order_id' => $order_id,
            'order_status' => $status,
            'order_payment_method' => $method,
            'order_total' => $value
        ] );

        $this->create_invoice( $order );
        return true;
    }

    /**
     * Actually create an invoice for a given order.
     *
     * @param  FP_Order_Adapter|null $order
     * @return void
     */
    private function create_invoice( $order )
    {
        $this->logger()->verbose('CALL', ['order_id' => !empty( $order ) ? $order->get_id() : '']);

        // Try to create an invoice for a given order
        // Store the invoice UUID on success an add an order note
        // Trigger the action that an invoice has been created

        // Check for waiting time has passed
        if ( !empty( $order ) && ! $order->is_create_invoice_request_waiting_time_passed() ) {
            $this->logger()->verbose('IF !$order->is_create_invoice_request_waiting_time_passed() IN');
            return;
        }

        try {
            $order->set_create_invoice_requested_at();
            $model = $this->factory()->create_invoice( $order );
            $result = $this->client()->create_invoice( $model );
            $order->set_invoice_uuid( $result['uuid'] );
            $order->set_invoice_number( $result['number'] );
            $order->set_invoice_date( $result['invoice_date'] );
            $order->add_note_invoice_created();
            $order->do_action_invoice_created();
            $this->logger()->create_invoice_success();
        }

        // Catch any exception that might happen during the process
        // Log the exception

        catch (Exception $exception) {
            $message = 'failed to create an invoice.';
            if ( is_callable( array( $exception, 'render_error' ) ) ) {
                $error = $exception->render_error();
                $message = $error['message'];
                FP_Admin_Notices::add_notice(
                    $error['title'].': '.$error['message'],
                    FP_Admin_Notices::NOTICE_TYPE_ERROR,
                    true
                );
            }
            if ( !empty( $order ) ) {
                $order->set_invoice_error_message(
                    (!empty($message) ? $message . "\n\n" : '')
                    . '[Code: '.$exception->getCode().', message: '.$exception->getMessage().']'
                );
            }
            $this->logger()->create_invoice_failed();
            $this->logger()->capture( $exception );
        }
    }

    /**
     * Maybe cancel an invoice if all prerequisites are fullfilled.
     *
     * @param  FP_Order_Adapter $order
     * @return bool
     */
    private function maybe_cancel_invoice( $order )
    {
        $this->logger()->verbose( 'CALL', [ 'order_id' => $order->get_id() ] );

        if ( ! $order->has_invoice_key() ) {
            return false;
        }

        $settings = $this->settings();
        if ( ! $settings->get_cancel_invoices() ) {
            return false;
        }

        if ( $order->has_invoice_canceled() ) {
            $this->logger()->verbose( 'IF $order->has_invoice_canceled() IN' );
            return false;
        }

        $this->cancel_invoice( $order );
        return true;
    }

    /**
     * Actually cancel an invoice for a given order.
     *
     * @param  FP_Order_Adapter $order
     * @return void
     */
    private function cancel_invoice( $order )
    {
        $this->logger()->verbose( 'CALL', [ 'order_id' => $order->get_id() ] );

        // Check for waiting time has passed
        if ( ! $order->is_cancel_invoice_request_waiting_time_passed() ) {
            $this->logger()->verbose( 'IF !$order->is_cancel_invoice_request_waiting_time_passed() IN' );
            return;
        }

        // Cancel the invoice by calling the API method
        // Add an order note that the invoice was cancelled
        try {
            $order->set_cancel_invoice_requested_at();
            $key = $order->get_invoice_key();
            $model = $this->factory()->create_invoice( $order );
            $this->client()->cancel_invoice( $key, $model );
            $order->add_note_invoice_cancelled();
            $order->do_action_invoice_cancelled();
            $order->set_invoice_canceled();
            $this->logger()->cancel_invoice_success();
        }

        // Catch any exception that happens during cancellation
        // Log the exception

        catch (Exception $exception) {
            if ( is_callable( array( $exception, 'render_error' ) ) ) {
                $error = $exception->render_error();
                FP_Admin_Notices::add_notice(
                    $error['title'].': '.$error['message'],
                    FP_Admin_Notices::NOTICE_TYPE_ERROR,
                    true
                );
            }
            $this->logger()->cancel_invoice_failed();
            $this->logger()->capture( $exception );
        }
    }
}

endif;
