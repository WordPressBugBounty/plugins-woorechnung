<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Email_Handler' ) ):

/**
 * Faktur Pro Email Handler Module.
 *
 * @class    FP_Email_Handler
 * @version  1.0.0
 * @package  FakturPro
 * @author   Zweischneider
 */
final class FP_Email_Handler extends FP_Abstract_Module
{
    /**
     * The alternative text body of invoice emails.
     *
     * @var string|null
     */
    private $_mail_alt_body = null;

    /**
     * Initialize the hooks of this module.
     *
     * @return void
     */
    public function init_hooks()
    {
        $this->add_action('phpmailer_init', 'configure_phpmailer', 8, 1);
        $this->add_action('fakturpro_invoice_created', 'process_mailing', 10, 1);
        $this->add_action('woocommerce_order_status_changed', 'process_mailing_delayed', 11, 3);
        /*if ($this->plugin()->is_woocommerce_subscriptions_active()) {
            $this->add_action('woocommerce_subscription_status_changed', 'process_mailing_delayed', 11, 3);
        }*/
        $this->add_action('woocommerce_checkout_order_processed', 'process_mailing_delayed_on_checkout', 11, 3);
        $this->add_filter('woocommerce_email_attachments', 'process_appending', 10, 3); // keep 3 parameters to remain backwards compatible.

        // Custom WooCommerce emails
        $this->add_action('woocommerce_email_classes', 'register_email_classes', 90, 1);
    }

    /**
     * Registers the plugin email classes to work with WooCommerce.
     *
     * @param  array<string, mixed> $emails
     * @return array<string, mixed>
     */
    public function register_email_classes( $emails )
    {
        require_once ( $this->plugin()->get_path( 'includes/emails/class-fp-email-customer-deliver-invoice.php' ) );
        $plugin_emails = array(
            'FP_Email_Customer_Deliver_Invoice' => new FP_Email_Customer_Deliver_Invoice( $this->plugin(), $this->settings(), $this ),
        );
        return array_merge( $emails, $plugin_emails );
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
     * Process the mailing of an invoice.
     *
     * @param  FP_Order_Adapter $order
     * @return bool
     */
    public function process_mailing( FP_Order_Adapter $order )
    {
        // Abort if invoice is not to be sent as email
        $settings = $this->settings();
        if ( ! $settings->send_invoice_as_email() ) {
            return false;
        }

        // Abort if email is not to be sent for this state
        $status = $order->get_status();
        if ( ! $settings->send_email_for_state( $status ) ) {
            return false;
        }

        // Abort if invoice is not to be sent for its payment method
        $method = $order->get_payment_method();
        if ( ! $settings->send_email_for_method( $method ) ) {
            return false;
        }

        // Send the invoice as an own email
        $this->send_invoice_as_email( $order );
        return true;
    }

    /**
     * Actually send the invoice as an email.
     *
     * @param  FP_Order_Adapter $order
     * @return void
     */
    private function send_invoice_as_email( FP_Order_Adapter $order )
    {
        // Try to download and send the invoice via email
        // Add an order note to signal the successful sent

        try {
            $key = $order->get_invoice_key();
            $result = $this->client()->get_invoice( $key );
            $filepath = $this->store_invoice( $order, $result['data'] );
            $this->send_invoice( $order, $filepath );
            $order->add_note_invoice_emailed();
            $order->set_sent_invoice_email();
            $this->logger()->send_invoice_as_email_success();
        }

        // Catch any exception that happens during the process
        // Log error and add error notice for user

        catch ( \Exception $exception ) {
            $this->logger()->send_invoice_as_email_failed();
            $this->logger()->capture( $exception );
        }
    }

    /**
     * Creates placeholders array.
     *
     * @param  FP_Order_Adapter $order
     * @param  bool $filename
     * @param  bool $brackets
     * @return array<string, mixed>
     */
    public function create_placeholders( $order, $filename = false, $brackets = true )
    {
        $settings = $this->settings();

        $company = $order->get_billing_company();
        $last_name = $order->get_billing_last_name();

        $replaces = array(
            'order_id' => $order->get_id(),
            'order_no' => $settings->get_order_number( $order->get_order_number() ),
            'invoice_no' => $order->get_invoice_number(),
            'invoice_key' => $order->get_invoice_key(),
            'company' => $order->get_billing_company(),
            'company_or_name' => empty( $company ) ? $last_name : $company,
            'first_name' => $order->get_billing_first_name(),
            'last_name' => $order->get_billing_last_name(),
            'invoice_date' => '',
            'invoice_date_de' => '',
            'invoice_date_day' => '',
            'invoice_date_month' => '',
            'invoice_date_year' => '',
        );

        // Invoice date
        $invoice_date_raw = $order->get_invoice_date();
        if ( !empty( $invoice_date_raw ) ) {
            try {
                $invoice_date_time = is_int( $invoice_date_raw )
                    ? (new \DateTime())->setTimestamp( $invoice_date_raw )
                    : new \DateTime( is_numeric( $invoice_date_raw ) ? '@' . $invoice_date_raw : $invoice_date_raw );
                $replaces = array_merge(
                    $replaces,
                    array(
                        'invoice_date' => $invoice_date_time->format('Y-m-d'),
                        'invoice_date_de' => $invoice_date_time->format('d.m.Y'),
                        'invoice_date_day' => $invoice_date_time->format('d'),
                        'invoice_date_month' => $invoice_date_time->format('m'),
                        'invoice_date_year' => $invoice_date_time->format('Y'),
                    )
                );
            } catch ( \Exception $exception ) {
                // Ignore any exceptions
            }
        }

        if ($filename == false) {
            $replaces['page_title'] = wp_get_document_title();
            $replaces['order_date'] = '';
            $replaces['order_date_de'] = '';
            $replaces['order_date_day'] = '';
            $replaces['order_date_month'] = '';
            $replaces['order_date_year'] = '';

            // Order date
            $order_date_raw = $order->get_date_created();
            if (!empty($order_date_raw)) {
                try {
                    $order_date_time = is_int($order_date_raw)
                        ? (new \DateTime())->setTimestamp($order_date_raw)
                        : new \DateTime(is_numeric($order_date_raw) ? '@' . $order_date_raw : $order_date_raw);
                    $replaces = array_merge(
                        $replaces,
                        array(
                            'order_date' => $order_date_time->format('Y-m-d'),
                            'order_date_de' => $order_date_time->format('d.m.Y'),
                            'order_date_day' => $order_date_time->format('d'),
                            'order_date_month' => $order_date_time->format('m'),
                            'order_date_year' => $order_date_time->format('Y'),
                        )
                    );
                } catch (\Exception $exception) {
                    // Ignore any exceptions
                }
            }
        }

        $replaces = $this->plugin()->array_map_placeholders( $replaces, $brackets );

        return $replaces;
    }

    /**
     * Replaces placeholder variables.
     *
     * @param  string $text
     * @param  array<string, mixed>|null $placeholders
     * @return string
     */
    public function replace_placeholders( $text, $placeholders = null )
    {
        return str_replace( array_keys( $placeholders ), array_values( $placeholders ), $text );
    }

    /**
     * Store invoice in temp folder to send it as email.
     *
     * @param  FP_Order_Adapter $order
     * @param  string   $data
     * @return string
     */
    private function store_invoice($order, $data)
    {
        // Construct filename and filepath
        $replaces = $this->create_placeholders($order, true, true);

        // Create directory to store the invoice in
        $directory = $this->plugin()->get_temp_path('invoices/' . $replaces['{invoice_key}']);
        $file_name = $this->settings()->get_email_filename();
        $file_name = trim($file_name);

        // Replace filename placeholders with new format (e.g. {order_id})
        $file_name = $this->replace_placeholders($file_name, $replaces);

        // Replace filename placeholders with old format (e.g. %order_id%)
        $replaces = $this->create_placeholders($order, true, false);
        $file_name = $this->replace_placeholders($file_name, $replaces);

        // Use default filename if settings is not set
        $file_name = empty($file_name) ? 'Rechnung' : $file_name;
        $file_name = "{$file_name}.pdf";

        // Remove characters that might interfere with the filepath
        $file_name = str_replace("/", "_", $file_name);
        $file_name = str_replace("\\", "_", $file_name);

        // Finally determine filepath and filedata
        $file_path = "{$directory}/{$file_name}";
        $file_data = base64_decode($data);

        // Make directory and put contents
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($file_path, $file_data);

        return $file_path;
    }

    /**
     * Send the invoice as email, using the mailing component.
     *
     * @param  FP_Order_Adapter $order
     * @param  string $filepath
     * @return void
     */
    private function send_invoice($order, $filepath)
    {
        $settings = $this->settings();
        $template = $settings->get_email_template();

        if ( $template == 'none' ) {
            $customer = $order->get_billing_email();
            $to = $settings->send_email_to();
            $copies = $settings->send_email_copies_to();
            $blind_copies = $settings->send_email_blind_copies_to();
            $subject = $settings->get_email_subject();
            $content_html = $settings->get_email_content_html();
            $content_text = $settings->get_email_content_text();
    
            // replace placeholders with new format (e.g. {order_id})
            $replaces = $this->create_placeholders($order, false, true);
            $subject = $this->replace_placeholders($subject, $replaces);
            $content_text = $this->replace_placeholders($content_text, $replaces);
            $content_html = $this->replace_placeholders($content_html, $replaces);
    
            // replace placeholders with old format (e.g. %order_id%)
            $replaces = $this->create_placeholders($order, false, false);
            $subject = $this->replace_placeholders($subject, $replaces);
            $content_text = $this->replace_placeholders($content_text, $replaces);
            $content_html = $this->replace_placeholders($content_html, $replaces);
    
            $content_html = empty($content_html) ? $content_text : $content_html;
    
            $this->_mail_alt_body = $content_text;
    
            $customer = apply_filters('fakturpro_filter_send_invoice_email_recipient', $customer, $order); // ->get_user());
    
            // Send email with own mailer
            $mailer = $this->mailer();
            $mailer->add_recipient( $customer );
            $mailer->add_recipients( $to );
            $mailer->add_copy_recipients( $copies );
            $mailer->add_blind_copy_recipients( $blind_copies );
            $mailer->set_subject( $subject );
            $mailer->set_contents( $content_html );
            $mailer->add_attachment( $filepath );
            $mailer->send_email();
        } else {
            // Send invoice email with woocommerce email template and mailer
            $attachments = array( $filepath );
            do_action( 'fakturpro_email_' . $template, $order->get_id(), $order, $attachments );
        }
    }

    /**
     * Get id of order or post object
     *
     * @param  object  $post
     * @return int|null
     */
    public function get_order_id( $post )
    {
        $order_id = null;

        if ( is_a( $post, 'YITH_YWGC_Gift_Card' ) ) {
            if ( property_exists( $post, 'order_id' ) ) {
                $order_id = $post->order_id; // @phpstan-ignore-line
            }
        }

        if ( is_null( $order_id ) && is_callable( array( $post, 'get_id' ) ) ) {
            $order_id = $post->get_id();
        }

        return $order_id;
    }

    /**
     * Process the trigger to append the invoice to the WooCommerce email.
     *
     * @param  array<string>  $attachments
     * @param  string $type
     * @param  WC_Order|null $post
     * @param  WC_Email|null $email
     * @return array<string>
     */
    public function process_appending( $attachments, $type, $post, $email = null )
    {
        // Avoid interference with other plugins
        if ( empty( $post ) || ! is_object( $post ) ) {
            return $attachments;
        }

        // Get and check order id.
        $order_id = $this->get_order_id( $post );
        if ( empty( $order_id ) ) {
            $this->logger()->verbose( 'IF empty($order_id) IN', [
                'order_id' => $order_id
            ] );
            return $attachments;
        }

        $this->logger()->verbose( 'CALL', [ 'type' => $type, 'order_id' => $order_id ] );

        // Load order using the adapter
        $order = new FP_Order_Adapter( $order_id );

        // Check if order was loaded.
        // WooCommerce uses Wordpress posts to handle products and orders.
        // We doesn't know if we processing an email for a order or a product.
        // If it can't load the post as an order then we can ignore the mail.
        if ( empty( $order->get_order() ) ) {
            $this->logger()->verbose( 'IF empty($order->get_order()) IN', [
                'post_id' => $order_id,
                'class of object' => get_class( $post )
            ] );
            return $attachments;
        }

        // Load the user settings
        $settings = $this->settings();

        // Invoice already sent
        if (
            $order->has_invoice_appended_to_email()
            && $this->plugin()->is_email_to_customer( $type )
            && $type != 'customer_invoice'
        ) {
            $this->logger()->verbose(
                'IF $order->has_invoice_appended_to_email() '.
                '&& $this->plugin()->is_email_to_customer("' . $type . '") '.
                '&& "' . $type . '" != "customer_invoice" IN'
            );
            return $attachments;
        }

        // Abort if invoice is not to be appended to email
        if (!$settings->append_invoice_to_email() ) {
            $this->logger()->verbose('IF !$settings->append_invoice_to_email() IN');
            return $attachments;
        }

        // Abort if invoice is not to be sent for its payment method
        $method = $order->get_payment_method();
        if ( !$settings->send_email_for_method( $method ) ) {
            $this->logger()->verbose('IF !$settings->send_email_for_method("' . $method . '") IN');
            return $attachments;
        }

        # Abort email type has not been selected in settings
        if (
            !$settings->append_invoice_to_email_type( $type )
            && $type != 'customer_invoice'
        ) {
            $this->logger()->verbose(
                'IF !$settings->append_invoice_to_email_type("' . $type . '") '.
                '&& "' . $type . '" != "customer_invoice" IN'
            );
            return $attachments;
        }

        $this->logger()->verbose( 'SUCCESS', [
            'order_id' => $order_id,
            'order_payment_method' => $method,
            'email_type' => $type
        ] );

        // Proceed attaching invoice to email
        return $this->append_invoice_to_email( $attachments, $order, $type );
    }

    /**
     * Download the invoice and append it to the WooCommerce email
     *
     * @param  array<string> $attachments
     * @param  FP_Order_Adapter $order
     * @param  string $email_type
     * @return array<string>
     */
    public function append_invoice_to_email( $attachments, FP_Order_Adapter $order, $email_type )
    {
        $this->logger()->verbose( 'CALL', [ 'order_id' => $order->get_id() ] );

        // Try to download the invoice as PDF and attach it
        // Add a notice to the order and return attachments

        try {
            $key = $order->get_invoice_key();
            if ( empty( $key ) ) {
                throw new \Exception( 'Invoice key not set.' );
            }
            $result = $this->client()->get_invoice( $key );
            $filepath = $this->store_invoice( $order, $result['data'] );
            $result = array_merge( $attachments, array( $filepath ) );
            $this->logger()->append_invoice_to_email_success();
            if ( $this->plugin()->is_email_to_customer( $email_type ) ) {
                $order->add_note_invoice_emailed();
                $order->set_invoice_appended_to_email();
            }
            return $result;
        }

        // Catch any exception that happens during the process
        // Log error and add error notice for user

        catch ( \Exception $exception ) {
            $this->logger()->append_invoice_to_email_failed();
            $this->logger()->capture( $exception );
            return $attachments;
        }
    }

    /**
     * Automatically process new orders on checkout.
     *
     * @param  int $order_id
     * @param  array<array<string, mixed>> $post_data
     * @param  WC_Order $order
     * @return bool
     */
    public function process_mailing_delayed_on_checkout( $order_id, $post_data, $order )
    {
        $order = new FP_Order_Adapter( $order_id );

        if ( $order->has_sent_invoice_email() ) {
            return false;
        }

        $this->process_mailing( $order );
        return true;
    }

    /**
     * Automatically process the order on a status changed.
     *
     * @param  int $order_id
     * @param  string $from
     * @param  string $to
     * @return bool
     */
    public function process_mailing_delayed( $order_id, $from, $to )
    {
        $order = new FP_Order_Adapter( $order_id );

        if ( $order->has_sent_invoice_email() ) {
            return false;
        }

        $this->process_mailing( $order );
        return true;
    }
}

endif;
