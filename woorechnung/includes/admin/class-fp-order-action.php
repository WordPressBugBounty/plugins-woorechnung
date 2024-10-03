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
            // Orders list
            $this->add_action('woocommerce_admin_order_actions_end', 'add_invoice_button');
            $this->add_action('wp_ajax_fakturpro_invoice', 'handle_invoice_button_ajax');

            // Order details
            $this->add_filter('woocommerce_order_actions', 'add_invoice_actions', 30, 1);
            $this->add_action('woocommerce_order_actions_start', 'add_invoice_actions_buttons', 30, 1);
            $this->add_action('woocommerce_order_action_fp_create_invoice', 'handle_create_invoice', 10, 1);
            $this->add_action('woocommerce_order_action_fp_reset_invoice', 'handle_reset_invoice', 10, 1);
            $this->add_action('admin_init', 'handle_actions');
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

        if ( ! is_a( $theorder, WC_Order::class ) || $theorder->get_status() == 'auto-draft' ) {
            return $actions;
        }

        $adapter = new FP_Order_Adapter( $theorder );
        if ( ! $adapter->has_invoice_key() ) {
            $actions['fp_create_invoice'] = __('Create invoice', 'fakturpro');
        } else {
            $actions['fp_reset_invoice'] = __('Reset invoice', 'fakturpro');
        }
        return $actions;
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

        if ( $adapter->get_status() == 'auto-draft' ) {
            return;
        }

        echo '<li class="wide">';
        if ( ! $adapter->has_invoice_key() ) {
            $params = array('page' => 'wc-orders', 'action' => 'edit', 'id' => $order_id, 'fp_action' => 'create_invoice');
            $target = 'admin.php?' . http_build_query( $params );
            $target = wp_nonce_url( admin_url( $target ), 'create_invoice', '_fp_nonce' );

            echo '<a href="' . $target . '" class="button button-secondary">';
            echo '<span class="icon-pdf-add"></span> ';
            echo __('Create invoice', 'fakturpro');
            echo '</a>';
        } else {
            $params = array('action' => 'fakturpro_invoice', 'order_id' => $order_id);
            $target = 'admin-ajax.php?' . http_build_query( $params );
            $target = wp_nonce_url( admin_url( $target ), 'fakturpro_invoice', '_fp_nonce' );

            echo '<a href="' . $target . '" target="_blank" class="button button-secondary">';
            echo '<span class="icon-pdf"></span> ';
            echo __('Retrieve invoice', 'fakturpro');
            echo '</a>';
        }
        echo '</li>';
    }

    /**
     * Handle actions of sent forms, buttons and links.
     *
     * @return void
     */
    public function handle_actions()
    {
		if ( isset( $_GET['fp_action'], $_GET['_fp_nonce'] ) ) {
            if ( 'create_invoice' === wp_unslash( $_GET['fp_action'] ) && wp_verify_nonce( wp_unslash( $_GET['_fp_nonce'] ), 'create_invoice' ) ) {

                if ( isset( $_GET['id'] ) ) {
                    $order_id = $_GET['id'];

                    $adapter = new FP_Order_Adapter( $order_id );
                    $adapter->unset_invoice_error_message();
                    if ( $this->create_invoice( $adapter ) ) {
                        FP_Admin_Notices::add_notice(
                            __('Invoice created', 'fakturpro'),
                            FP_Admin_Notices::NOTICE_TYPE_SUCCESS,
                            true
                        );
                    } else {
                        FP_Admin_Notices::add_notice(
                            __('Invocie was not created', 'fakturpro'),
                            FP_Admin_Notices::NOTICE_TYPE_ERROR,
                            true
                        );
                    }

                    $params = array('page' => 'wc-orders');
                    if ( isset($_GET['action'] ) && wp_unslash( $_GET['action'] ) == 'edit' ) {
                        $params['action'] = 'edit';
                        $params['id'] = $order_id;
                    }

                    wp_safe_redirect( admin_url( 'admin.php?' . http_build_query($params) ) );
                    exit;
                }

                FP_Admin_Notices::add_notice(
                    /* translators: %s: parameter name */
                    sprintf( __('Missing parameter %s', 'fakturpro'), 'id' ),
                    FP_Admin_Notices::NOTICE_TYPE_ERROR,
                    true
                );

                $params = array( 'page' => 'wc-orders' );
                wp_safe_redirect( admin_url( 'admin.php?' . http_build_query($params) ) );
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
     * Callback to add the invoice button to the order.
     *
     * @param  WC_Order $order
     * @return void
     */
    public function add_invoice_button( $order )
    {
        $adapter = new FP_Order_Adapter( $order->get_id() );
        $params = $this->prepare_params( $adapter );
        $this->button( $params );
    }

    /**
     * Prepare the parameters for the invoice button.
     *
     * The button requires a URI target with the action to trigger on click,
     * the CSS class for the icon and the textthat matches the action to
     * trigger (create or download the invoice).
     *
     * @param  FP_Order_Adapter $order
     * @return array<string, mixed>
     */
    private function prepare_params( FP_Order_Adapter $order )
    {
        // Prepare the target URL for the action button

        $invoice = $order->get_invoice_key();
        $params = array('action' => 'fakturpro_invoice', 'order_id' => $order->get_id());
        $target = 'admin-ajax.php?' . http_build_query($params);
        $target = wp_nonce_url( admin_url($target), 'fakturpro_invoice', '_fp_nonce');

        $icon_create = 'button icon-pdf-add';
        $icon_fetch = 'button icon-pdf';
        $text_create = __('Create invoice', 'fakturpro');
        $text_fetch = __('Retrieve invoice', 'fakturpro');

        if ( $order->has_invoice_error_message() ) {
            $icon_create .= ' error';
            $text_create .= ' (' . __('Error on last try', 'fakturpro') . ')';
        }

        // Return the parameters
        $result = array();
        $result['target'] = $target;
        $result['class'] = empty($invoice) ? $icon_create : $icon_fetch;
        $result['text'] = empty($invoice) ? $text_create : $text_fetch;
        return $result;
    }

    /**
     * Show the button.
     *
     * @param  array<string, mixed> $params
     * @param  string $target
     * @return void
     */
    private function button( $params, $target = '_blank' )
    {
        echo '<a '
            . 'class="'.esc_attr( $params['class'] ).'" '
            . 'href="'.esc_url( $params['target'] ).'" '
            . 'alt="'.esc_attr( $params['text'] ).'" '
            . 'title="'.esc_attr( $params['text'] ).'" '
            . 'aria-label="'.esc_attr( $params['text'] ).'" '
            . 'data-tip="'.esc_attr( $params['text'] ).'" '
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
        $order_id = absint( wp_unslash( $_GET['order_id'] ) );
        $order = new FP_Order_Adapter($order_id);

        // Create invoice if necessary
        if (!$order->has_invoice_key()) {
            $order->unset_invoice_error_message();
            $this->create_invoice( $order );
        }

        // Show the invoice for the order
        $this->show_invoice( $order );
    }

    /**
     * Create a new invoice by sending a request to the API.
     *
     * @param  FP_Order_Adapter|null $order
     * @return bool
     */
    private function create_invoice( FP_Order_Adapter $order = null )
    {
        // Try to create an invoice by using the API
        // On Success the UUID is stored and a note added

        // Check for waiting time has passed
        if ( !empty( $order ) && !$order->is_create_invoice_request_waiting_time_passed() ) {
            $this->logger()->verbose('IF !$order->is_create_invoice_request_waiting_time_passed() IN');
            return false;
        }

        try {
            $order->set_create_invoice_requested_at();
            $model = $this->factory()->create_invoice( $order );
            $result = $this->client()->create_invoice( $model );
            $order->set_invoice_uuid($result['uuid']);
            $order->set_invoice_number($result['number']);
            $order->set_invoice_date( $result['invoice_date'] );
            $order->add_note_invoice_created();
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
            $result = $this->client()->get_invoice( $key );
            $this->viewer()->view_pdf($key, $result['data']);
            $this->logger()->fetch_invoice_success();
        }

        // Catch any exception that might happen during the process
        // Log the exception and let the handler exit properly

        catch (Exception $exception) {
            $this->logger()->fetch_invoice_failed();
            $this->logger()->capture($exception);
            $this->handler()->handle($exception);
        }
    }

}

endif;
