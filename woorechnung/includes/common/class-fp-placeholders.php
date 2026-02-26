<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'FP_Placeholders' ) ):

/**
 * Faktur Pro PDF Placeholders Component
 *
 * @version  1.0.0
 * @package  FakturPro\Common
 * @author   Zweischneider
 */
final class FP_Placeholders
{
    /**
     * The plugin instance via dependency injection.
     *
     * @var FP_Plugin $_plugin
     */
    private $_plugin;

    /**
     * Create a new instance of this viewer class.
     *
     * @param  FP_Plugin $plugin
     */
    public function __construct(FP_Plugin $plugin)
    {
        $this->_plugin = $plugin;
    }

    /**
     * Creates placeholders array.
     *
     * @param  FP_Order_Adapter $order
     * @param  bool $filename
     * @param  bool $brackets
     * @return array<string, mixed>
     */
    public function create( $order, $filename = false, $brackets = true )
    {
        $settings = $this->_plugin->get_settings();

        $company = $order->get_billing_company();
        $last_name = $order->get_billing_last_name();

        $replaces = array(
            // Order
            'order_id' => $order->get_id(),
            'order_no' => $settings->get_order_number( $order->get_order_number() ),

            // Company
            'company' => $order->get_billing_company(),
            'company_or_name' => empty( $company ) ? $last_name : $company,

            // Customer
            'first_name' => $order->get_billing_first_name(),
            'last_name' => $order->get_billing_last_name(),

            // Cancellation invoice
            'cancellation_invoice_no' => $order->get_cancellation_invoice_number(),
            'cancellation_invoice_date' => '',
            'cancellation_invoice_date_de' => '',
            'cancellation_invoice_date_day' => '',
            'cancellation_invoice_date_month' => '',
            'cancellation_invoice_date_year' => '',

            // Delivery note
            'delivery_note_no' => $order->get_delivery_note_number(),
            'delivery_note_date' => '',
            'delivery_note_date_de' => '',
            'delivery_note_date_day' => '',
            'delivery_note_date_month' => '',
            'delivery_note_date_year' => '',

            // Invoice
            'invoice_no' => $order->get_invoice_number(),
            'invoice_key' => $order->get_invoice_key(),
            'invoice_uuid' => $order->get_invoice_key(),
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

        // Cancellation invoice date
        $cancellation_invoice_date_raw = $order->get_cancellation_invoice_date();
        if ( !empty( $cancellation_invoice_date_raw ) ) {
            try {
                $cancellation_invoice_date_time = is_int( $cancellation_invoice_date_raw )
                    ? (new \DateTime())->setTimestamp( $cancellation_invoice_date_raw )
                    : new \DateTime( is_numeric( $cancellation_invoice_date_raw ) ? '@' . $cancellation_invoice_date_raw : $cancellation_invoice_date_raw );
                $replaces = array_merge(
                    $replaces,
                    array(
                        'cancellation_invoice_date' => $cancellation_invoice_date_time->format('Y-m-d'),
                        'cancellation_invoice_date_de' => $cancellation_invoice_date_time->format('d.m.Y'),
                        'cancellation_invoice_date_day' => $cancellation_invoice_date_time->format('d'),
                        'cancellation_invoice_date_month' => $cancellation_invoice_date_time->format('m'),
                        'cancellation_invoice_date_year' => $cancellation_invoice_date_time->format('Y'),
                    )
                );
            } catch ( \Exception $exception ) {
                // Ignore any exceptions
            }
        }

        // Delivery note date
        $delivery_note_date_raw = $order->get_invoice_date();
        if ( !empty( $delivery_note_date_raw ) ) {
            try {
                $delivery_note_date_time = is_int( $delivery_note_date_raw )
                    ? (new \DateTime())->setTimestamp( $delivery_note_date_raw )
                    : new \DateTime( is_numeric( $delivery_note_date_raw ) ? '@' . $delivery_note_date_raw : $delivery_note_date_raw );
                $replaces = array_merge(
                    $replaces,
                    array(
                        'delivery_note_date' => $delivery_note_date_time->format('Y-m-d'),
                        'delivery_note_date_de' => $delivery_note_date_time->format('d.m.Y'),
                        'delivery_note_date_day' => $delivery_note_date_time->format('d'),
                        'delivery_note_date_month' => $delivery_note_date_time->format('m'),
                        'delivery_note_date_year' => $delivery_note_date_time->format('Y'),
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

        $replaces = $this->array_map( $replaces, $brackets );

        return $replaces;
    }

    /**
     * Replaces placeholder variables.
     *
     * @param  string $text
     * @param  array<string, mixed>|null $placeholders
     * @return string
     */
    public function replace( $text, $placeholders = null )
    {
        return str_replace( array_keys( $placeholders ), array_values( $placeholders ), $text );
    }

    /**
     * Array map placeholders.
     *
     * @param  array<string, mixed> $placeholders
     * @param  bool $brackets
     * @return array<string, mixed>
     */
    public function array_map( $placeholders, $brackets = true )
    {
        return $this->_plugin->array_map_assoc(
            function ( $key, $value ) use ( $brackets ) {
                return [ $brackets ? '{' . $key . '}' : '%' . $key . '%', $value ];
            },
            $placeholders
        );
    }

    /**
     * Return the variable names and descriptions for the invoice filename.
     *
     * @param  bool $brackets
     * @return array<string, string>
     */
    public function get_invoice_filename_variables( $brackets = true )
    {
        return $this->array_map(
            array(
                // Cancellation invoice
                'cancellation_invoice_no' => __('Cancellation invoice number', 'fakturpro'),
                'cancellation_invoice_date' => __('Cancellation invoice date US', 'fakturpro'),
                'cancellation_invoice_date_de' => __('Cancellation invoice date DE', 'fakturpro'),
                'cancellation_invoice_date_day' => __('Day of cancellation invoice date', 'fakturpro'),
                'cancellation_invoice_date_month' => __('Month of cancellation invoice date', 'fakturpro'),
                'cancellation_invoice_date_year' => __('Year of cancellation invoice date', 'fakturpro'),

                // Company
                'company_or_name' => __('Customer company or last name', 'fakturpro'),
                'company' => __('Customer company name', 'fakturpro'),

                // Delivery note
                'delivery_note_no' => __('Delivery note number', 'fakturpro'),
                'delivery_note_date' => __('Delivery note date US', 'fakturpro'),
                'delivery_note_date_de' => __('Delivery note date DE', 'fakturpro'),
                'delivery_note_date_day' => __('Day of delivery note date', 'fakturpro'),
                'delivery_note_date_month' => __('Month of delivery note date', 'fakturpro'),
                'delivery_note_date_year' => __('Year of delivery note date', 'fakturpro'),

                // Customer
                'first_name' => __('Customer first name', 'fakturpro'),
                'last_name' => __('Customer last name', 'fakturpro'),

                // Invoice
                'invoice_uuid' => __('Invoice uuid', 'fakturpro'),
                'invoice_no' => __('Invoice number', 'fakturpro'),
                'invoice_date' => __('Invoice date US', 'fakturpro'),
                'invoice_date_de' => __('Invoice date DE', 'fakturpro'),
                'invoice_date_day' => __('Day of invoice date', 'fakturpro'),
                'invoice_date_month' => __('Month of invoice date', 'fakturpro'),
                'invoice_date_year' => __('Year of invoice date', 'fakturpro'),

                // Order
                'order_id' => __('Order ID', 'fakturpro'),
                'order_no' => __('Order number', 'fakturpro'),
            ),
            $brackets
        );
    }

    /**
     * Return the variable names and descriptions for replacements in texts.
     *
     * @param  bool $brackets
     * @return array<string, string>
     */
    public function get_invoice_variables( $brackets = true )
    {
        return array_merge(
            $this->get_invoice_filename_variables( $brackets ),
            $this->array_map(
                array(
                    'order_date' => __('Order date US', 'fakturpro'),
                    'order_date_de' => __('Order date DE', 'fakturpro'),
                    'order_date_day' => __('Day of order date', 'fakturpro'),
                    'order_date_month' => __('Month of order date', 'fakturpro'),
                    'order_date_year' => __('Year of order date', 'fakturpro'),
                    'page_title' => __('Page title', 'fakturpro'),
                ),
                $brackets
            )
        );
    }

    /**
     * Retrieve the invoice storage path.
     *
     * @param  FP_Order_Adapter $order
     * @return string
     */
    public function get_invoice_storage_path( $order )
    {
        $replaces = $this->create( $order, true, true );
        $path = $this->_plugin->get_temp_path( 'invoices/' . $replaces['{invoice_key}'] );
        return $path;
    }

    /**
     * Retrieve the invoice filename.
     *
     * @param  FP_Order_Adapter $order
     * @return string
     */
    public function get_invoice_filename( $order )
    {
        $settings = $this->_plugin->get_settings();
        $replaces = $this->create( $order, true, true );

        // Construct filename
        $filename = $this->_plugin->get_settings()->get_email_filename();
        $filename = trim( $filename );

        // Replace filename placeholders with new format (e.g. {order_id})
        $filename = $this->replace( $filename, $replaces );

        // Replace filename placeholders with old format (e.g. %order_id%)
        $replaces = $this->create( $order, true, false );
        $filename = $this->replace( $filename, $replaces );

        // Use default filename if settings is not set
        $filename = empty( $filename )
            ? 'Rechnung zur Besellung ' . $settings->get_order_number( $order->get_order_number() )
            : $filename;
        $filename = "{$filename}.pdf";

        // Remove characters that might interfere with the filepath
        $filename = str_replace( " ", "_", $filename );
        $filename = str_replace( "/", "_", $filename );
        $filename = str_replace( "\\", "_", $filename );

        return $filename;
    }

    /**
     * Retrieve the cancellation invoice filename.
     *
     * @param  FP_Order_Adapter $order
     * @return string
     */
    public function get_cancellation_invoice_filename( $order )
    {
        $settings = $this->_plugin->get_settings();
        $replaces = $this->create( $order, true, true );

        // Construct filename
        $filename = $this->_plugin->get_settings()->get_cancellation_invoice_filename();
        $filename = trim( $filename );

        // Replace filename placeholders with new format (e.g. {order_id})
        $filename = $this->replace( $filename, $replaces );

        // Replace filename placeholders with old format (e.g. %order_id%)
        $replaces = $this->create( $order, true, false );
        $filename = $this->replace( $filename, $replaces );

        // Use default filename if settings is not set
        $filename = empty( $filename )
            ? 'Stornorechnung zur Bestellung ' . $settings->get_order_number( $order->get_order_number() )
            : $filename;
        $filename = "{$filename}.pdf";

        // Remove characters that might interfere with the filepath
        $filename = str_replace( " ", "_", $filename );
        $filename = str_replace( "/", "_", $filename );
        $filename = str_replace( "\\", "_", $filename );

        return $filename;
    }

    /**
     * Retrieve the delivery note filename.
     *
     * @param  FP_Order_Adapter $order
     * @return string
     */
    public function get_delivery_note_filename( $order )
    {
        $settings = $this->_plugin->get_settings();
        $replaces = $this->create( $order, true, true );

        // Construct filename
        $filename = $this->_plugin->get_settings()->get_delivery_note_filename();
        $filename = trim( $filename );

        // Replace filename placeholders with new format (e.g. {order_id})
        $filename = $this->replace( $filename, $replaces );

        // Replace filename placeholders with old format (e.g. %order_id%)
        $replaces = $this->create( $order, true, false );
        $filename = $this->replace( $filename, $replaces );

        // Use default filename if settings is not set
        $filename = empty( $filename )
            ? 'Lieferschein zur Bestellung ' . $settings->get_order_number( $order->get_order_number() )
            : $filename;
        $filename = "{$filename}.pdf";

        // Remove characters that might interfere with the filepath
        $filename = str_replace( " ", "_", $filename );
        $filename = str_replace( "/", "_", $filename );
        $filename = str_replace( "\\", "_", $filename );

        return $filename;
    }
}

endif;
