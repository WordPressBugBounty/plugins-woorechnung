<?php

// Exit of accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Customer_Link' ) ):

/**
 * Faktur Pro Invoice Customer Link Module.
 *
 * @version  1.0.0
 * @package  FakturPro
 * @author   Zweischneider
 */
final class FP_Customer_Link extends FP_Abstract_Module
{

    /**
     * The title of the error message to display to the customer.
     *
     * @var string
     */
    const ERROR_TITLE = 'Rechnungsdownload fehlgeschlagen';

    /**
     * The error message to display to the user.
     *
     * @var string
     */
    const ERROR_MESSAGE = 'Bitte kontaktiere den Betreiber des Onlineshops.';

    /**
     * Initialize the hooks for this module.
     *
     * @return void
     */
    public function init_hooks()
    {
        if ( $this->settings()->get_customer_link() ) {
            $this->add_filter('woocommerce_my_account_my_orders_actions', 'add_customer_invoice_link', 30, 2);
            $this->add_action('wp_ajax_fakturpro_customer_invoice', 'handle_customer_invoice_ajax');
        }
    }

    /**
     * The the invoice  download link to the customers order.
     *
     * @param  array<string, array<string, mixed>>|null $actions
     * @param  WC_Order $order
     * @return array<string, array<string, mixed>>|null
     */
    public function add_customer_invoice_link( $actions, $order )
    {
        $order = new FP_Order_Adapter( $order->get_id() );

        if ( $order->has_invoice_key() ) {
            $action = array();
            $action['url'] = $this->prepare_target( $order );
            $action['name'] = __( 'Invoice', 'fakturpro' );
            $actions = is_array( $actions ) ? $actions : array();
            $actions['invoice'] = $action;
        }

        return $actions;
    }

    /**
     * Prepare the target URI to download the invoice.
     *
     * @param WC_Order|FP_Order_Adapter $order
     * @return string
     */
    private function prepare_target( $order )
    {
        $params = array();
        $params['action'] = 'fakturpro_customer_invoice';
        $params['order'] = $order->get_id();
        $params['my-account'] = true;

        $target = 'admin-ajax.php?';
        $target = $target . http_build_query( $params );
        $target = wp_nonce_url( admin_url( $target ), 'fakturpro_customer_invoice', '_fp_nonce' );

        return $target;
    }

    /**
     * Handle the customer invoice download AJAX action.
     *
     * @return void
     */
    public function handle_customer_invoice_ajax()
    {
		if ( ! isset( $_GET['_fp_nonce'], $_GET['action'] ) ) {
			wp_send_json_error( 'missing_fields' );
			// wp_die(); // NOTE: wp_send_json_error already let php die
		}

		if ( ! wp_verify_nonce( wp_unslash( $_GET['_fp_nonce'] ), 'fakturpro_customer_invoice' ) ) {
			wp_send_json_error( 'bad_nonce' );
			// wp_die(); // NOTE: wp_send_json_error already let php die
		}

        // Fetch the invoice by UUID calling the API
        // Log the download isplay the invoice as PDF

        try {
            $order = new FP_Order_Adapter( absint( wp_unslash( $_GET['order'] ) ) );
            $key = $order->get_invoice_key();
            $result = $this->client()->get_invoice( $key );
            $this->logger()->customer_download_success();
            $this->viewer()->view_pdf( $key, $result['data'] );
        }

        // Catch any exception that happens during the process
        // Log the error for the admin and display a message

        catch ( \Exception $exception ) {
            $this->logger()->customer_download_failed();
            $this->handler()->exit_error( self::ERROR_TITLE, self::ERROR_MESSAGE, false );
        }
    }
}

endif;
