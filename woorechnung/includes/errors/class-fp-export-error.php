<?php

if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Export_Error' ) ):

/**
 * Faktur Pro Export Error Class
 *
 * @class    FP_Export_Error
 * @version  1.0.0
 * @package  FakturPro\Errors
 * @author   Zweischneider
 */
final class FP_Export_Error extends FP_Abstract_Error
{
    /**
     * The default error message for this exception type.
     *
     * @var string ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'Faktur Pro bulk-export error';

    /**
     * Create a new instance of this error.
     *
     * @param  int $status
     */
    public function __construct($status = 0)
    {
        parent::__construct( self::ERROR_MESSAGE, $status );
    }

    /**
     * Render this error to a printable title and message.
     *
     * @return array<string, string>
     */
    public function render_error()
    {
        $status = $this->getCode();
        $title = __( "Export fehlgeschlagen (Fehler {$status})", 'fakturpro' );
        $message = __( 'The invoices could not be exported.', 'fakturpro' );
        return array( 'title' => $title, 'message' => $message );
    }
}

endif;
