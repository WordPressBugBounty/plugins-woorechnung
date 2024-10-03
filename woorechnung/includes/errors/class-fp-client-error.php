<?php

if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Client_Error' ) ):

/**
 * Faktur Pro Client Error Class
 *
 * This exception is thrown if the HTTP client is not capable to send
 * requests to the server application. This type of error is not to be
 * mistaken with the request error, which signals an erroneous HTTP
 * response returned from the server application.
 *
 * @class    FP_Client_Error
 * @version  1.0.0
 * @package  FakturPro\Errors
 * @author   Zweischneider
 */
final class FP_Client_Error extends FP_Abstract_Error
{
    /**
     * The default error message for this exception type.
     *
     * @var string ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'Faktur Pro client-side error';

    /**
     * Create a new instance of this error.
     *
     * @param  int    $code
     */
    public function __construct( $code = 0 )
    {
        parent::__construct( self::ERROR_MESSAGE, $code );
    }

    /**
     * Render this error to a printable title and message.
     *
     * @return array<string, string>
     */
    public function render_error()
    {
        switch ($this->getCode())
        {
            case CURLE_COULDNT_CONNECT: return $this->error_connection();
            case CURLE_SSL_CONNECT_ERROR: return $this->error_ssl_connection();
            case CURLE_OPERATION_TIMEDOUT: return $this->error_timeout();
            default: return $this->error_unknown_reasons();
        }
    }

    /**
     * Render an error concerning a connection problem.
     *
     * @return array<string, string>
     */
    public function error_connection()
    {
        $title = __( 'Connection error', 'fakturpro ');
        $message = __( 'Could not connect to the server application. Please check your internet connection.', 'fakturpro' );
        return array( 'title' => $title, 'message' => $message );
    }

    /**
     * Render an error concerning an ssl connection problem.
     *
     * @return array<string, string>
     */
    public function error_ssl_connection()
    {
        $title = __( 'SSL-Error', 'fakturpro' );
        $message = __( 'A secure connection over SSL could not be established. Please check your server configuration.', 'fakturpro' );
        return array( 'title' => $title, 'message' => $message );
    }

    /**
     * Render an error concerning a a request timeout.
     *
     * @return array<string, string>
     */
    public function error_timeout()
    {
        $title = __( 'Request timeout', 'fakturpro' );
        $message = __( 'The request to the server took too long. Please try again or increase the timeout limit.', 'fakturpro' );
        return array( 'title' => $title, 'message' => $message );
    }

    /**
     * Render an error with an unknown reason.
     *
     * @return array<string, string>
     */
    public function error_unknown_reasons()
    {
        $title = __( 'Cause unclear', 'fakturpro' );
        $message = __( 'An unknown error has occurred. Please contact support if the problem persists.', 'fakturpro' );
        return array( 'title' => $title, 'message' => $message );
    }
}

endif;
