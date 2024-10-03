<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Bulk_Actions' ) ):

/**
 * Faktur Pro Bulk Actions Class
 *
 * This class provides two bulk actions that can be applied
 * to a bunch of orders in the order list. Firstly, all invoices
 * created for those orders can be fetched and exported as a
 * ZIP archive. Secondly, all invoices created can be reset.
 *
 * @version  1.0.0
 * @package  FakturPro\Admin
 * @author   Zweischneider
 */
final class FP_Bulk_Actions extends FP_Abstract_Module
{
    /**
     * The alternative text body of invoice emails.
     *
     * @var string|null
     */
    private $_mail_alt_body = null;

    /**
     * The errors that occurred during processing.
     *
     * @var array<array<string, mixed>>
     */
    private $errors = array();

    /**
     * Initialize the hooks of this module.
     *
     * @return void
     */
    public function init_hooks()
    {
        if ( is_admin() ) {
            $this->add_action('phpmailer_init', 'configure_phpmailer', 8, 1);
            add_action('admin_init', function () {
                $shop_order_screen_id = ( 'shop_order' === self::get_order_screen_id() ? 'edit-shop_order' : self::get_order_screen_id() );
                add_filter('bulk_actions-' . $shop_order_screen_id, array($this, 'add_bulk_actions'), 20, 1 );
                add_filter('handle_bulk_actions-' . $shop_order_screen_id, array($this, 'handle_bulk_action'), 10, 3 );
            });
        }
    }

    /**
     * Get the screen id for shop order.
     *
     * @return string
     */
	public static function get_order_screen_id() {
		return function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
	}

    /**
     * Configure the PHPMailer instance.
     *
     * Add the alternative plain text body to the mailer.
     *
     * @param  mixed $mailer
     * @return void
     */
    public function configure_phpmailer( $mailer )
    {
        if (
            class_exists( 'PHPMailer\PHPMailer\PHPMailer' )
            && $mailer instanceof \PHPMailer\PHPMailer\PHPMailer
        ) {
            if ( !empty( $this->_mail_alt_body ) ) {
                $body = $this->_mail_alt_body;
                $mailer->AltBody = $body;
                $this->_mail_alt_body = null;
            }
        }
    }

    /**
     * The callback function to add the bulk actions to the dropdown.
     *
     * @param  array<string, string>|null $actions
     * @return array<string, string>
     */
    public function add_bulk_actions( $actions )
    {
        $actions = is_array( $actions ) ? $actions : array();
        $actions['export_invoices'] = __('Export invoices', 'fakturpro');
        $actions['reset_invoices'] = __('Reset invoices', 'fakturpro');
        return $actions;
    }

    /**
     * The callback function for the dropdown options.
     *
     * @param  string $redirect_to
     * @param  string $action
     * @param  array<mixed>  $post_ids
     * @return string
     */
    public function handle_bulk_action( $redirect_to, $action, $post_ids )
    {
        $this->errors = array();
        switch( $action )
        {
            case 'reset_invoices':
                $this->reset_invoices( $post_ids );
                return $redirect_to;

            case 'export_invoices':
                $this->export_invoices( $post_ids );
                return $redirect_to;

            default:
                return $redirect_to;
        }
    }

    /**
     * Reset all invoice ids for the selected orders.
     *
     * @param  array<mixed> $post_ids
     * @return void
     */
    public function reset_invoices( $post_ids )
    {
        foreach ( $post_ids as $order_id ) {
            $order = new FP_Order_Adapter( $order_id );
            $order->reset_invoice();
        }
    }

    /**
     * Export all selected invoices using a ZIP archive.
     *
     * @param  array<mixed> $post_ids
     * @return void
     */
    private function export_invoices( $post_ids )
    {
        $callback = array($this, 'retrieve_invoice');
        $invoices = array_map( $callback, $post_ids );
        $invoices = array_filter( $invoices );
        $this->create_archive( $invoices );
    }

    /**
     * Retrieve an invoice for a given order.
     *
     * Invoices are either created just in time if not before
     * or simply downloaded if a key already exists.
     *
     * @param  mixed $order_id
     * @return array<string, mixed>|null
     */
    private function retrieve_invoice( $order_id )
    {
        // Wrap up the order using the adapter
        $order = new FP_Order_Adapter( $order_id );

        // Order already has an invoice key, fetch data
        // Return invoice key and data

        if ( $order->has_invoice_key() ) {
            $invoice['order_id'] = $order_id;
            $invoice['invoice_key'] = $order->get_invoice_key();
            $invoice['invoice_number'] = $order->get_invoice_number();
            $invoice['invoice_data'] = $this->fetch_invoice( $order );
            return ! empty ( $invoice['invoice_data'] ) ? $invoice : null;
        }

        // Invoice does not exist yet, create it
        // Fetch invoice afterwards and return key and data

        else {
            $order->unset_invoice_error_message();
            $invoice['order_id'] = $order_id;
            $invoice['invoice_key'] = $this->create_invoice( $order );
            $invoice['invoice_data'] = !empty($order->get_invoice_key()) ? $this->fetch_invoice( $order ) : null;
            $invoice['invoice_number'] = $order->get_invoice_number();
            return ! empty ( $invoice['invoice_data'] ) ? $invoice : null;
        }
    }

    /**
     * Fetch an existing invoice for a given order.
     *
     * @param  FP_Order_Adapter $order
     * @return string|null
     */
    private function fetch_invoice( FP_Order_Adapter $order )
    {
        // Try to create an invoice by using the API
        // On success, the invoice data is returned

        try {
            $key = $order->get_invoice_key();
            $result = $this->client()->get_invoice( $key );
            $this->logger()->fetch_invoice_success();
            return $result['data'];
        }

        // Catch any exception that might happen during the process
        // Log the exception and return null instead

        catch (Exception $exception) {
            $this->logger()->fetch_invoice_failed();
            $this->logger()->capture($exception);
            $this->errors[] = array(
                'order' => $order,
                'title' => __('Invoice not found', 'fakturpro'),
                'message' => __('The invoice could not be retrieved.', 'fakturpro'),
            );
            return null;
        }
    }

    /**
     * Create a new invoice for a given order.
     *
     * @param  FP_Order_Adapter|null $order
     * @return string|null
     */
    private function create_invoice( FP_Order_Adapter $order = null )
    {
        // Try to create an invoice for a given order
        // Store the invoice UUID on success an add an order note
        // Trigger the action that an invoice has been created

        // Check for waiting time has passed
        if ( !empty( $order ) && !$order->is_create_invoice_request_waiting_time_passed() ) {
            $this->logger()->verbose( 'IF !$order->is_create_invoice_request_waiting_time_passed() IN' );
            return null;
        }

        try {
            $order->set_create_invoice_requested_at();
            $model = $this->factory()->create_invoice( $order );
            $result = $this->client()->create_invoice( $model );
            $order->set_invoice_uuid( $result['uuid'] );
            $order->set_invoice_number( $result['number'] );
            $order->set_invoice_date( $result['invoice_date'] );
            $order->add_note_invoice_created();
            $this->logger()->create_invoice_success();
            return $result['uuid'];
        }

        // Catch any exception that might happen during the process
        // Log the exception and return null instead of the key

        catch ( \Exception $exception ) {
            $message = 'failed to create an invoice.';
            $this->logger()->create_invoice_failed();
            $this->logger()->capture( $exception );
            if (is_callable(array($exception, 'render_error'))) {
                $error = $exception->render_error();
                $message = $error['message'];
                $this->errors[] = array_merge(
                    array('order' => $order),
                    $error
                );
            } else {
                $this->errors[] = array(
                    'order' => $order,
                    'title' => __('Cause unclear', 'fakturpro'),
                    'message' => __(
                        'Es ist zu einem unbekannten Fehler gekommen. Bitte kontaktiere den Support, ' .
                        'wenn das Problem weiterhin besteht.',
                        'fakturpro'
                    ),
                );
            }
            if ( !empty( $order ) ) {
                $order->set_invoice_error_message(
                    (!empty($message) ? $message . "\n\n" : '')
                    . '[Code: '.$exception->getCode().', message: '.$exception->getMessage().']'
                );
            }
            return null;
        }
    }

    /**
     * Create the ZIP archive for all invoice files.
     *
     * @param  array<array<string, mixed>> $invoices
     * @return string|null
     */
    private function create_archive( $invoices )
    {
        // Prepare export filepath
        $export_file = md5( strval( time() ) ) . '.zip';
        $export_dir = $this->plugin()->get_exports_path();
        $export_path = $export_dir . $export_file;

        if (!is_dir( $export_dir )) {
            mkdir( $export_dir, 0777, true );
        }

        // Create the zip archive
        $archive = new ZipArchive();
        $mode = ZipArchive::CREATE | ZipArchive::EXCL;
        $result = $archive->open( $export_path, $mode );

        // Check if an error occurred
        if ( $result !== true ) {
            $exception = new FP_Export_Error( $result );
            $this->handler()->handle( $exception );
        }

        // Add invoices to archive
        foreach ( $invoices as $invoice ) {
            $order_id = $invoice['order_id'];
            $invoice_key = $invoice['invoice_key'];
            $invoice_number = $invoice['invoice_number'];
            $invoice_data = $invoice['invoice_data'];
            $filedata = base64_decode($invoice['invoice_data']);
            $filename = "Bestellung_{$invoice['order_id']}.pdf";

            if ( ! empty($invoice_number) ) {
                $filename = "Rechnung_{$invoice_number}.pdf";
                $filename = str_replace(" ", "_", $filename);
                $filename = str_replace("/", "_", $filename);
                $filename = str_replace("\\", "_", $filename);
            }

            $archive->addFromString($filename, $filedata);
        }

        // Close the zip archive
        $result = $archive->close();

        // Check if an error occurred
        if ( $result !== true ) {
            $exception = new FP_Export_Error();
            $this->handler()->handle( $exception );
        }

        $filename = 'Rechnungen_' . date( 'd_m_Y' ) . '.zip';
        $filesize = filesize( $export_path );

        if (!empty($this->errors)) {
            $settings = $this->settings();
            $mailer = $this->mailer();
            $recipient = $settings->get_send_error_mails_to();

            $subject = sprintf(
                /* translators: %s: action name */
                __('Error with multiple action "%s" on orders', 'fakturpro'),
                esc_html( __('Export invoices', 'fakturpro') )
            );

            $lines = array(
                sprintf(
                    /* translators: %1$s: action name, %2$s: date, %3$s: time */
                    __(
                        'Errors occurred when running the multiple action "%1$s" on %2$s at %3$s o\'clock on the following orders:',
                        'fakturpro'
                    ),
                    esc_html( __('Export invoices', 'fakturpro') ),
                    date('d.m.Y'),
                    date('H:i')
                ),
            );

            $content_text = implode("\n", $lines)."\n\n";
            $content_html = '<p>'.implode('<br/>', $lines).'</p>';

            foreach ($this->errors as $error) {
                $order = $error['order'];
                $lines = array(
                    /* translators: %s: order id */
                    sprintf( __('Order #%s', 'fakturpro'), $order->get_id()).': '.$error['title'],
                    $error['message'],
                );
                $content_text .= implode("\n", $lines)."\n\n";
                $content_html .= '<p>'.implode('<br/>', $lines).'</p>';
            }

            $this->_mail_alt_body = $content_text;

            $mailer->add_recipient( $recipient );
            $mailer->set_subject( $subject );
            $mailer->set_contents( $content_html );
            $mailer->send_email();
        }

        // Send content
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . $filesize);
        @readfile( $export_path );

        // Unlink and exit
        @unlink( $export_path );
        exit();
    }


}

endif;
