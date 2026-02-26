<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Order_Action' ) ):

/**
 * Faktur Pro Order Action Module.
 *
 * This class implements the button to be added to the WooCommerce
 * list of orders in the admin panel. When the button is clicked,
 * either a new invoice is created or an existing invoice is downloaded
 * and shown to the user.
 *
 * @version  1.0.0
 * @package  FakturPro\Admin
 * @author   Zweischneider
 */
final class FP_Order_Action extends FP_Abstract_Module
{
    /**
     * Initialize the hooks of this module.
     *
     * @return void
     */
    public function init_hooks()
    {
        if ( is_admin() ) {
            // General
            $this->add_action( 'admin_init', 'handle_actions' );
            $this->add_action( 'wp_ajax_fakturpro_invoice', 'handle_invoice_button_ajax' );
            $this->add_action( 'wp_ajax_fakturpro_delivery_note', 'handle_delivery_note_button_ajax' );
            $this->add_action( 'wp_ajax_fakturpro_cancellation_invoice', 'handle_cancellation_invoice_button_ajax' );

            // Orders list
            $this->add_action( 'woocommerce_admin_order_actions_end', 'add_orders_list_actions_buttons' );

            // Order details
            $this->add_filter( 'woocommerce_order_actions', 'add_invoice_actions', 30, 1 );
            $this->add_action( 'woocommerce_order_actions_start', 'add_invoice_actions_buttons', 30, 1 );
            $this->add_action( 'woocommerce_order_action_fp_create_invoice', 'handle_create_invoice', 10, 1 );
            $this->add_action( 'woocommerce_order_action_fp_reset_invoice', 'handle_reset_invoice', 10, 1 );
            $this->add_action( 'woocommerce_order_action_fp_cancel_invoice', 'handle_cancel_invoice', 10, 1 );
        }
    }

    /**
     * Add invoice actions to order details actions dropdown.
     *
     * @param  array<string, string> $actions
     * @return array<string, string>
     */
    public function add_invoice_actions( $actions )
    {
        global $theorder;

        if ( ! is_a( $theorder, WC_Order::class ) || $theorder->get_status() == 'auto-draft' || empty( $theorder->get_id() ) ) {
            return $actions;
        }

        $adapter = new FP_Order_Adapter( $theorder );
        if ( ! $adapter->has_invoice_key() ) {
            $actions['fp_create_invoice'] = __( 'Create invoice', 'fakturpro' );
        } else {
            $actions['fp_reset_invoice'] = __( 'Reset invoice', 'fakturpro' );
            if ( ! $adapter->has_invoice_canceled() ) {
                $actions['fp_cancel_invoice'] = __( 'Cancel invoice', 'fakturpro' );
            }
        }
        return $actions;
    }

    /**
     * Show order details button.
     *
     * @param string $text
     * @param string $url
     * @param string $icon
     * @param string $target
     * @param string $confirmation_text
     * @return void
     */
    private function order_details_button( string $text, string $url, string $icon = '', string $target = '_self', string $confirmation_text = '' )
    {
        $css = 'button button-secondary';
        $css .= !empty( $confirmation_text ) ? ' fakturpro-confirm' : '';
        echo '<a href="' . $url . '" target="' . $target . '" class="' . $css . '"';
        if ( !empty( $confirmation_text ) ) {
            echo ' data-fakturpro-question="' . $confirmation_text . '"';
        }
        echo '>';
        if ( !empty( $icon ) ) {
            echo '<span class="' . $icon . '"></span> ';
        }
        echo $text;
        echo '</a>';
    }

    /**
     * Show order details action button.
     *
     * @param string $text
     * @param string $action
     * @param FP_Order_Adapter $order
     * @param string $icon
     * @param string $confirmation_text
     * @return void
     */
    private function order_details_action_button( string $text, string $action, FP_Order_Adapter $order, $icon = '', string $confirmation_text = '' )
    {
        $params = array( 'page' => 'wc-orders', 'action' => 'edit', 'id' => $order->get_id(), 'fp_action' => $action );
        $url = 'admin.php?' . http_build_query( $params );
        $url = wp_nonce_url( admin_url( $url ), $action, '_fp_nonce' );
        $this->order_details_button( $text, $url, $icon, '_self', $confirmation_text );
    }

    /**
     * Show order details retrieve button.
     *
     * @param string $text
     * @param string $action
     * @param FP_Order_Adapter $order
     * @param string $icon
     * @return void
     */
    private function order_details_retrieve_button( string $text, string $action, FP_Order_Adapter $order, string $icon = '' )
    {
        $params = array( 'action' => $action, 'order_id' => $order->get_id() );
        $url = 'admin-ajax.php?' . http_build_query( $params );
        $url = wp_nonce_url( admin_url( $url ), $action, '_fp_nonce' );
        $this->order_details_button( $text, $url, $icon, '_blank' );
    }

    /**
     * Add invoice action buttons to order details actions block.
     *
     * @param  int $order_id
     * @return void
     */
    public function add_invoice_actions_buttons( $order_id )
    {
        $adapter = new FP_Order_Adapter( $order_id );

        if ( empty( $adapter->get_order() ) || $adapter->get_status() == 'auto-draft' || empty( $order_id ) ) {
            return;
        }

        echo '<li class="wide">';
        echo '<div class="fp-order-details-actions-buttons-container">';

        if ( ! $adapter->has_invoice_key() ) {
            $text = __( 'Create invoice', 'fakturpro' );
            $this->order_details_action_button( $text, 'create_invoice', $adapter, 'icon-pdf-add' );
        } else {
            $text = __( 'Retrieve invoice', 'fakturpro' );
            $this->order_details_retrieve_button( $text, 'fakturpro_invoice', $adapter, 'icon-pdf' );

            if ( $adapter->has_delivery_note_number() ) {
                $text = __( 'Retrieve delivery note', 'fakturpro' );
                $this->order_details_retrieve_button( $text, 'fakturpro_delivery_note', $adapter, 'icon-pdf' );
            }

            if ( ! $adapter->has_invoice_canceled() ) {
                $text = __( 'Create cancellation invoice', 'fakturpro' );
                $cancellation_question = __( 'Do you really want to cancel this invoice?', 'fakturpro' );
                $this->order_details_action_button( $text, 'cancel_invoice', $adapter, 'icon-pdf-add', $cancellation_question );
            } else {
                $text = __( 'Retrieve cancellation invoice', 'fakturpro' );
                $this->order_details_retrieve_button( $text, 'fakturpro_cancellation_invoice', $adapter, 'icon-pdf' );
            }

            $text = __( 'Send invoice', 'fakturpro' );
            if ( $adapter->has_sent_invoice_email() || $adapter->has_invoice_appended_to_email() ) {
                $text = __( 'Resend invoice', 'fakturpro' );
            }
            $this->order_details_action_button( $text, 'send_invoice', $adapter, 'dashicons dashicons-email-alt fp-button-dashicon' );
        }

        echo '</div>';
        echo '</li>';
    }

    /**
     * Handle action create invoice.
     *
     * @param FP_Order_Adapter $adapter
     * @return void
     */
    private function handle_action_create_invoice( FP_Order_Adapter $adapter )
    {
        $adapter->unset_invoice_error_message();
        if ( $this->create_invoice( $adapter ) ) {
            FP_Admin_Notices::add_notice(
                __( 'Invoice created', 'fakturpro' ),
                FP_Admin_Notices::NOTICE_TYPE_SUCCESS,
                true
            );
        } else {
            FP_Admin_Notices::add_notice(
                __( 'Invoice was not created', 'fakturpro' ),
                FP_Admin_Notices::NOTICE_TYPE_ERROR,
                true
            );
        }
    }

    /**
     * Handle action send invoice.
     *
     * @param FP_Order_Adapter $adapter
     * @return void
     */
    private function handle_action_send_invoice( FP_Order_Adapter $adapter )
    {
        if ( $this->send_invoice( $adapter, false ) ) {
            FP_Admin_Notices::add_notice(
                __( 'Invoice sent', 'fakturpro' ),
                FP_Admin_Notices::NOTICE_TYPE_SUCCESS,
                true
            );
        } else {
            FP_Admin_Notices::add_notice(
                __( 'Invoice was not sent', 'fakturpro' ),
                FP_Admin_Notices::NOTICE_TYPE_ERROR,
                true
            );
        }
    }

    /**
     * Handle action cancel invoice.
     *
     * @param FP_Order_Adapter $adapter
     * @return void
     */
    private function handle_action_cancel_invoice( FP_Order_Adapter $adapter )
    {
        $adapter->unset_invoice_error_message();
        if ( $this->cancel_invoice( $adapter ) ) {
            FP_Admin_Notices::add_notice(
                __( 'Cancellation invoice created', 'fakturpro' ),
                FP_Admin_Notices::NOTICE_TYPE_SUCCESS,
                true
            );
        } else {
            FP_Admin_Notices::add_notice(
                __( 'Cancellation invoice was not created', 'fakturpro' ),
                FP_Admin_Notices::NOTICE_TYPE_ERROR,
                true
            );
        }
    }

    /**
     * Handle actions of sent forms, buttons and links.
     *
     * @return void
     */
    public function handle_actions()
    {
		if ( isset( $_GET['fp_action'], $_GET['_fp_nonce'] ) ) {
            $action = wc_clean( wp_unslash( $_GET['fp_action'] ) );
            $nonce = wp_unslash( $_GET['_fp_nonce'] );

            if (
                in_array( $action, ['create_invoice', 'send_invoice', 'cancel_invoice'] )
                && wp_verify_nonce( $nonce, $action )
            ) {
                $order_id = FP_Order_Adapter::get_request_id( $_GET );
                if ( !empty( $order_id ) ) {
                    $adapter = new FP_Order_Adapter( $order_id );

                    switch ( $action ) {
                        case 'create_invoice':
                            $this->handle_action_create_invoice( $adapter );
                            break;
                        case 'send_invoice':
                            $this->handle_action_send_invoice( $adapter );
                            break;
                        case 'cancel_invoice':
                            $this->handle_action_cancel_invoice( $adapter );
                            break;
                    }

                    $params = array( 'page' => 'wc-orders' );
                    if ( isset( $_GET['action'] ) && wp_unslash( $_GET['action'] ) == 'edit' ) {
                        $params['action'] = 'edit';
                        $params['id'] = $order_id;
                    }
                    wp_safe_redirect( admin_url( 'admin.php?' . http_build_query( $params ) ) );
                    exit;
                }

                FP_Admin_Notices::add_notice(
                    /* translators: %s: parameter name */
                    sprintf( __( 'Missing parameter %s', 'fakturpro' ), 'id' ),
                    FP_Admin_Notices::NOTICE_TYPE_ERROR,
                    true
                );

                $params = array( 'page' => 'wc-orders' );
                wp_safe_redirect( admin_url( 'admin.php?' . http_build_query( $params ) ) );
                exit;
            }
        }
    }

    /**
     * Create invoice.
     *
     * @param WC_Order $order
     * @return void
     */
    public function handle_create_invoice( $order )
    {
        $adapter = new FP_Order_Adapter( $order );
        if ( ! $adapter->has_invoice_key() ) {
            $adapter->unset_invoice_error_message();
            $this->create_invoice( $adapter );
        }
    }

    /**
     * Reset invoice.
     *
     * @param WC_Order $order
     * @return void
     */
    public function handle_reset_invoice( $order )
    {
        $order = new FP_Order_Adapter( $order );
        $order->reset_invoice();
    }

    /**
     * Cancel invoice.
     *
     * @param WC_Order $order
     * @return void
     */
    public function handle_cancel_invoice( $order )
    {
        $adapter = new FP_Order_Adapter( $order );
        if ( $adapter->has_invoice_key() && ! $adapter->has_invoice_canceled() ) {
            $adapter->unset_invoice_error_message();
            $this->cancel_invoice( $adapter );
        }
    }

    /**
     * Callback to add the invoice buttons to the orders list table.
     *
     * @param  WC_Order $order
     * @return void
     */
    public function add_orders_list_actions_buttons( $order )
    {
        $adapter = new FP_Order_Adapter( $order );
        $params = $this->orders_list_invoice_button_prepare_params( $adapter );
        $this->orders_list_button( $params );
    }

    /**
     * Prepare the parameters for the orders list invoice button.
     *
     * The button requires a URI target with the action to trigger on click,
     * the CSS class for the icon and the textthat matches the action to
     * trigger (create or download the invoice).
     *
     * @param  FP_Order_Adapter $order
     * @return array<string, mixed>
     */
    private function orders_list_invoice_button_prepare_params( FP_Order_Adapter $order )
    {
        // Prepare the target URL for the action button

        $invoice = $order->get_invoice_key();
        $params = array( 'action' => 'fakturpro_invoice', 'order_id' => $order->get_id() );
        $target = 'admin-ajax.php?' . http_build_query( $params );
        $target = wp_nonce_url( admin_url( $target ), 'fakturpro_invoice', '_fp_nonce');

        $icon_create = 'button icon-pdf-add';
        $icon_fetch = 'button icon-pdf';
        $text_create = __( 'Create invoice', 'fakturpro' );
        $text_fetch = __( 'Retrieve invoice', 'fakturpro' );

        if ( $order->has_invoice_error_message() ) {
            $icon_create .= ' error';
            $text_create .= ' (' . __( 'Error on last try', 'fakturpro' ) . ')';
        }

        // Return the parameters
        $result = array();
        $result['target'] = $target;
        $result['class'] = empty( $invoice ) ? $icon_create : $icon_fetch;
        $result['text'] = empty( $invoice ) ? $text_create : $text_fetch;
        return $result;
    }

    /**
     * Show the orders list button.
     *
     * @param  array<string, mixed> $params
     * @param  string $target
     * @return void
     */
    private function orders_list_button( $params, $target = '_blank' )
    {
        echo '<a '
            . 'class="' . esc_attr( $params['class'] ) . '" '
            . 'href="' . esc_url( $params['target'] ) . '" '
            . 'alt="' . esc_attr( $params['text'] ) . '" '
            . 'title="' . esc_attr( $params['text'] ) . '" '
            . 'aria-label="' . esc_attr( $params['text'] ) . '" '
            . 'data-tip="' . esc_attr( $params['text'] ) . '" '
            . 'target="' . $target . '"'
            . '></a>';
    }

    /**
     * Handle the ajax action when the button is clicked.
     *
     * If there is no invoice key attached to the order th button was
     * triggered for, a new invoice is created first. Afterwards, the
     * invoice is fetched and displayed as PDF.
     *
     * @return void
     */
    public function handle_invoice_button_ajax()
    {
		if ( ! isset( $_GET['_fp_nonce'], $_GET['action'] ) ) {
			wp_send_json_error( 'missing_fields' );
			// wp_die(); // NOTE: wp_send_json_error already let php die
		}

		if ( ! wp_verify_nonce( wp_unslash( $_GET['_fp_nonce'] ), 'fakturpro_invoice' ) ) {
			wp_send_json_error( 'bad_nonce' );
			// wp_die(); // NOTE: wp_send_json_error already let php die
		}

        // Fetch the order id parameter
        $order_id = FP_Order_Adapter::get_request_id( $_GET );
        $order = new FP_Order_Adapter( $order_id );

        // Create invoice if necessary
        if ( ! $order->has_invoice_key() ) {
            $order->unset_invoice_error_message();
            $this->create_invoice( $order, true );
        }

        // Show the invoice for the order
        $this->show_invoice( $order );
    }

    /**
     * Handle the ajax action when the button is clicked.
     *
     * If there is no invoice key attached to the order th button was
     * triggered for, a new invoice is created first. Afterwards, the
     * invoice is fetched and displayed as PDF.
     *
     * @return void
     */
    public function handle_cancellation_invoice_button_ajax()
    {
		if ( ! isset( $_GET['_fp_nonce'], $_GET['action'] ) ) {
			wp_send_json_error( 'missing_fields' );
			// wp_die(); // NOTE: wp_send_json_error already let php die
		}

		if ( ! wp_verify_nonce( wp_unslash( $_GET['_fp_nonce'] ), 'fakturpro_cancellation_invoice' ) ) {
			wp_send_json_error( 'bad_nonce' );
			// wp_die(); // NOTE: wp_send_json_error already let php die
		}

        // Fetch the order id parameter
        $order_id = FP_Order_Adapter::get_request_id( $_GET );
        $order = new FP_Order_Adapter( $order_id );

        // Create invoice if necessary
        if ( ! $order->has_invoice_key() || ! $order->has_invoice_canceled() ) {
            $order->unset_invoice_error_message();
            $this->cancel_invoice( $order );
        }

        // Show the invoice for the order
        $this->show_cancellation_invoice( $order );
    }

    /**
     * Handle the ajax action when the button is clicked.
     *
     * If there is no invoice key attached to the order th button was
     * triggered for, a new invoice is created first. Afterwards, the
     * invoice is fetched and displayed as PDF.
     *
     * @return void
     */
    public function handle_delivery_note_button_ajax()
    {
		if ( ! isset( $_GET['_fp_nonce'], $_GET['action'] ) ) {
			wp_send_json_error( 'missing_fields' );
			// wp_die(); // NOTE: wp_send_json_error already let php die
		}

		if ( ! wp_verify_nonce( wp_unslash( $_GET['_fp_nonce'] ), 'fakturpro_delivery_note' ) ) {
			wp_send_json_error( 'bad_nonce' );
			// wp_die(); // NOTE: wp_send_json_error already let php die
		}

        // Fetch the order id parameter
        $order_id = FP_Order_Adapter::get_request_id( $_GET );
        $order = new FP_Order_Adapter( $order_id );

        // Abort if no delivery note exist
        if ( ! $order->has_delivery_note_number() ) {
            $order->unset_invoice_error_message();
            return;
        }

        // Show the invoice for the order
        $this->show_delivery_note( $order );
    }

    /**
     * Create a new invoice by sending a request to the API.
     *
     * @param  FP_Order_Adapter|null $order
     * @param  bool $throw_warnings
     * @return bool
     */
    private function create_invoice( ?FP_Order_Adapter $order = null, bool $throw_warnings = false )
    {
        // Try to create an invoice by using the API
        // On Success the UUID is stored and a note added

        // Check for waiting time has passed
        if ( !empty( $order ) && !$order->is_create_invoice_request_waiting_time_passed() ) {
            $this->logger()->verbose( 'IF !$order->is_create_invoice_request_waiting_time_passed() IN' );
            if ( $throw_warnings ) {
                $this->handler()->handle( new FP_Server_Error( 'Too many attempts', 460 ) );
            }
            FP_Admin_Notices::add_notice(
                __( 'Too many attempts to create the invoice in a short period of time. Please wait a few minutes before trying again.', 'fakturpro' ),
                FP_Admin_Notices::NOTICE_TYPE_WARNING,
                true
            );
            return false;
        }

        try {
            $order->set_create_invoice_requested_at();
            $model = $this->factory()->create_invoice( $order );
            $result = $this->client()->create_invoice( $model );
            $order->handle_invoice_created_result( $result );
            $this->logger()->create_invoice_success();
            return true;
        }

        // Catch any exception that might happen during the process
        // Log the exception and let the handler exit properly

        catch ( \Exception $exception ) {
            $message = 'failed to create an invoice.';
            if ( is_callable( array( $exception, 'render_error' ) ) ) {
                $error = $exception->render_error();
                $message = $error['message'];
            }
            if ( !empty( $order ) ) {
                $order->set_invoice_error_message(
                    ( !empty( $message ) ? $message . "\n\n" : '' )
                    . '[Code: '.$exception->getCode().', message: '.$exception->getMessage().']'
                );
            }
            $this->logger()->create_invoice_failed();
            $this->logger()->capture( $exception );
            $this->handler()->handle( $exception );
        }

        return false;
    }

    /**
     * Create a new cancel invoice by sending a request to the API.
     *
     * @param  FP_Order_Adapter|null $order
     * @param  bool $throw_warnings
     * @return bool
     */
    public function cancel_invoice( ?FP_Order_Adapter $order = null, bool $throw_warnings = false )
    {
        // Try to cancel an invoice by using the API
        // On Success the cancellation is stored and a note added

        // Check for waiting time has passed
        if ( ! $order->is_cancel_invoice_request_waiting_time_passed() ) {
            $this->logger()->verbose( 'IF !$order->is_cancel_invoice_request_waiting_time_passed() IN' );
            if ( $throw_warnings ) {
                $this->handler()->handle( new FP_Server_Error( 'Too many attempts', 460 ) );
            }
            FP_Admin_Notices::add_notice(
                __( 'Too many attempts to cancel the invoice in a short period of time. Please wait a few minutes before trying again.', 'fakturpro' ),
                FP_Admin_Notices::NOTICE_TYPE_WARNING,
                true
            );
            return false;
        }

        // Cancel the invoice by calling the API method
        // Add an order note that the invoice was cancelled
        try {
            $order->set_cancel_invoice_requested_at();
            $key = $order->get_invoice_key();
            $model = $this->factory()->create_invoice( $order );
            $result = $this->client()->cancel_invoice( $key, $model );
            $order->handle_invoice_cancelled_result( $result );
            $this->logger()->cancel_invoice_success();
            return true;
        }

        // Catch any exception that might happen during the process
        // Log the exception and let the handler exit properly

        catch (Exception $exception) {
            $message = 'failed to cancel an invoice.';
            if ( is_callable( array( $exception, 'render_error' ) ) ) {
                $error = $exception->render_error();
                $message = $error['message'];
            }
            if ( !empty( $order ) ) {
                $order->set_invoice_error_message(
                    ( !empty( $message ) ? $message . "\n\n" : '' )
                    . '[Code: '.$exception->getCode().', message: '.$exception->getMessage().']'
                );
            }
            $this->logger()->cancel_invoice_failed();
            $this->logger()->capture( $exception );
            $this->handler()->handle( $exception );
        }

        return false;
    }

    /**
     * Send invoice mail.
     *
     * @param FP_Order_Adapter $order
     * @param bool $throw_exceptions
     * @return bool
     */
    private function send_invoice( FP_Order_Adapter $order, bool $throw_exceptions = false )
    {
        try {
            $order->do_action_send_invoice(true, true);
            return true;
        } catch (\Exception $exception) {
            if ( $throw_exceptions ) {
                $this->handler()->handle( $exception );
            }
        }
        return false;
    }

    /**
     * Fetch and display the invoice PDF.
     *
     * @param  FP_Order_Adapter $order
     * @return void
     */
    private function show_invoice( FP_Order_Adapter $order )
    {
        // Try to create an invoice by using the API
        // On success, the invoice data is displayed or downloaded

        try {
            $key = $order->get_invoice_key();
            $filename = $this->placeholders()->get_invoice_filename( $order );
            $result = $this->client()->get_invoice( $key );
            $this->viewer()->view_pdf( $filename, $result['data'] );
            $this->logger()->fetch_invoice_success();
        }

        // Catch any exception that might happen during the process
        // Log the exception and let the handler exit properly

        catch ( \Exception $exception ) {
            $this->logger()->fetch_invoice_failed();
            $this->logger()->capture( $exception );
            $this->handler()->handle( $exception );
        }
    }

    /**
     * Fetch and display the cancellation invoice PDF.
     *
     * @param  FP_Order_Adapter $order
     * @return void
     */
    private function show_cancellation_invoice( FP_Order_Adapter $order )
    {
        // Try to get the cancellation invoice by using the API
        // On success, the cancellation invoice data is displayed or downloaded

        try {
            $key = $order->get_invoice_key();
            $filename = $this->placeholders()->get_cancellation_invoice_filename( $order );
            $result = $this->client()->get_cancellation_invoice( $key );
            $this->viewer()->view_pdf( $filename, $result['data'] );
            $this->logger()->fetch_cancellation_invoice_success();
        }

        // Catch any exception that might happen during the process
        // Log the exception and let the handler exit properly

        catch ( \Exception $exception ) {
            $this->logger()->fetch_cancellation_invoice_failed();
            $this->logger()->capture( $exception );
            $this->handler()->handle( $exception );
        }
    }

    /**
     * Fetch and display the delivery note PDF.
     *
     * @param  FP_Order_Adapter $order
     * @return void
     */
    private function show_delivery_note( FP_Order_Adapter $order )
    {
        // Try to get the delivery note by using the API
        // On success, the delivery note data is displayed or downloaded

        try {
            $key = $order->get_invoice_key();
            $filename = $this->placeholders()->get_delivery_note_filename( $order );
            $result = $this->client()->get_delivery_note( $key );
            $this->viewer()->view_pdf( $filename, $result['data'] );
            $this->logger()->fetch_delivery_note_success();
        }

        // Catch any exception that might happen during the process
        // Log the exception and let the handler exit properly

        catch ( \Exception $exception ) {
            $this->logger()->fetch_delivery_note_failed();
            $this->logger()->capture( $exception );
            $this->handler()->handle( $exception );
        }
    }

}

endif;
