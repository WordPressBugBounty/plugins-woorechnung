<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'FP_Viewer' ) ):

/**
 * Faktur Pro PDF Viewer Component
 *
 * @version  1.0.0
 * @package  FakturPro\Common
 * @author   Zweischneider
 */
final class FP_Viewer
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
     * Validate pdf data.
     *
     * @param  mixed $data
     * @return bool
     */
    private function validate_pdf( $data )
    {
        return is_string( $data ) && is_string( base64_decode( $data ) );
    }

    /**
     * View a PDF file, given its key and base64 data.
     *
     * @param string $key
     * @param string $data
     * @return void
     */
    public function view_pdf( $key, $data )
    {
        if ( $this->validate_pdf( $data ) ) {

            if ( $this->_plugin->get_settings()->get_open_invoices() ) {
                header( 'Content-type: application/pdf' );
                header( 'Content-Disposition: inline; filename="' . esc_attr( $key ) . '.pdf"' );
                header( 'Content-Transfer-Encoding: binary' );
                header( 'Accept-Ranges: bytes' );

                // At this point, the content of a PDF file from our server is outputed as an echo.
                // Unfortunately, we cannot sanatize PDF content.
                echo base64_decode( $data );
            } else {
                header( 'Content-Description: File Transfer' );
                header( 'Content-Disposition: attachment; filename="' . esc_attr( $key ) . '.pdf"' );
                header( 'Content-Type: application/octet-stream' );
                header( 'Content-Transfer-Encoding: binary' );
                header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
                header( 'Connection: keep-alive' );
                header( 'Expires: 0' );
                header( 'Pragma: public' );

                // At this point, the content of a PDF file from our server is outputed as an echo.
                // Unfortunately, we cannot sanatize PDF content.
                echo base64_decode( $data );
            }

        } else {
            throw new FP_Server_Error( __( 'PDF data is not valid.', 'fakturpro' ), 0 );
        }
    }
}

endif;
