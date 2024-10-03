<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Server_Error' ) ):

/**
 * Faktur Pro Server Error Class
 *
 * @version  1.0.0
 * @package  FakturPro\Errors
 * @author   Zweischneider
 */
final class FP_Server_Error extends FP_Abstract_Error
{
    /**
     * The default error message for this exception type.
     *
     * @var string ERROR_MESSAGE
     */
    const ERROR_MESSAGE = 'Faktur Pro server-side error';

    /**
     * The trace id the client generates for a request.
     *
     * @var string $_trace_id
     */
    private $_trace_id;

    /**
     * Create a new instance of this error.
     *
     * @param  string $body
     * @param  int    $code
     */
    public function __construct( $body, $code )
    {
        parent::__construct( $body, $code );
    }

    /**
     * Set the trace id for this error instance.
     *
     * @param  string $_trace_id
     * @return void
     */
    public function set_trace_id($_trace_id)
    {
        $this->_trace_id = $_trace_id;
    }

    /**
     * Get the trace id for this error instance.
     *
     * @return string
     */
    public function get_trace_id()
    {
        return $this->_trace_id;
    }

    /**
     * Get the data of the error.
     *
     * @return array<string, mixed>
     */
    private function get_data()
    {
        $message = $this->getMessage();
        $data = @json_decode($message, true);
        return !empty($data) ? $data : ['message' => $message];
    }

    /**
     * Get the error message.
     *
     * @return string
     */
    private function get_error_message()
    {
        $data = $this->get_data();
        $error_code = !empty($data['error_code']) ? $data['error_code'] : '';
        $message = !empty($data['message']) ? $data['message'] : '';
        if (!empty($message)) {
            $message = !empty($error_code) ? "{$message} (Code: {$error_code})" : $message;
        } else {
            $message = $error_code;
        }
        switch ($error_code) {
            case 'missing_secrets':
                $message = __('Missing setup. Please check if you have fully configured your billing provider.',
                    'fakturpro'
                );
                break;
            case 'missing_settings':
                $message = __('Missing settings. Please check if you have fully configured your billing provider.',
                    'fakturpro'
                );
                break;
            case 'pause_services':
                $message = __('The processing services of your account have been paused. Please check if there are any outstanding payments to Faktur Pro or payments failed and update your payment details if necessary.',
                    'fakturpro'
                );
                break;
            case 'service_not_active':
                $message = __('The connection to the service provider has been deactivated and must be reactivated.',
                    'fakturpro'
                );
                break;
            case 'service_not_ready':
                $message = __('The connection to the service provider is not ready. Please check if you have configured your billing provider correctly.',
                    'fakturpro'
                );
                break;
            default:
                $message = __('Ein Fehler ist aufgetreten.' .
                    (!empty($message) ? " Error: {$message}" : ''),
                    'fakturpro'
                );
        }
        return $message;
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
            case 400: return $this->error_bad_request();
            case 401: return $this->error_authentication_failed();
            case 402: return $this->error_upgrade_required();
            case 403: return $this->error_access_forbidden();
            case 404: return $this->error_resource_not_found();
            case 409: return $this->error_conflict();
            case 415: return $this->error_unsupported_media_type();
            case 422: return $this->error_unprocessable_entity();
            case 423: return $this->error_resource_locked();
            case 429: return $this->error_too_many_requests();
            case 500: return $this->error_server_internal();
            case 501: return $this->error_method_not_implemented();
            case 503: return $this->error_server_unavailable();
            default:  return $this->error_unknown_reasons();
        }
    }

    /**
     * Render an error concerning missing configuration.
     *
     * @return array<string, string>
     */
    private function error_bad_request()
    {
        $title = __('Missing configuration (Error 400)', 'fakturpro');
        $message = __('Please check that you have fully configured your billing provider.', 'fakturpro');
        return array('title' => $title, 'message' => $message);
    }

    /**
     * Render an error concerning the API authentication.
     *
     * @return array<string, string>
     */
    private function error_authentication_failed()
    {
        $title = __('Authentication failed (Error 401)', 'fakturpro');
        $message = __('Please check if the store key set in the settings is correct.', 'fakturpro');
        return array('title' => $title, 'message' => $message);
    }

    /**
     * Render an error concerning the invoice limit.
     *
     * @return array<string, string>
     */
    private function error_upgrade_required()
    {
        $title = __('Upgrade required (Error 402)', 'fakturpro');
        $message = __('You cannot create any more invoices this month because your limit has been reached.', 'fakturpro');
        return array('title' => $title, 'message' => $message);
    }

    /**
     * Render an error concerning the authorization of a resource.
     *
     * @return array<string, string>
     */
    private function error_access_forbidden()
    {
        $title = __('Authorization failed (Error 403)', 'fakturpro');
        $message = __('You do not have permission to access this invoice', 'fakturpro');
        return array('title' => $title, 'message' => $message);
    }

    /**
     * Render an error concerning the processing of a resource.
     *
     * Possible reasons for this errors are:
     * - the validation of the request data fails on the server side
     * - the connection to the invoice provider cannot be established
     * - the request to the invoice provider fails
     *
     * @return array<string, string>
     */
    private function error_unprocessable_entity()
    {
        $title = __('Processing failed (Error 422)', 'fakturpro');
        $message = __('<p>The request processing failed. Please check the following possible causes of error:</p>'
            . '<ul>'
            . '<li>Does the order have all the necessary data for invoicing?</li>'
            . '<li>Have you configured the taxes correctly in your online shop?</li>'
            . '<li>Have you successfully established the connection to your billing provider?</li>'
            . '<li>Can your billing provider process all the information in your order?</li>'
            . '</ul>'
            . '<p>If the cause of the problem is unclear to you, please contact us please contact our support.</p>', 'fakturpro');
        return array('title' => $title, 'message' => $message);
    }

    /**
     * Render a too many requests error to the user.
     *
     * @return array<string, string>
     */
    private function error_too_many_requests()
    {
        $title = __('Too many requests (Error 429)', 'fakturpro');
        $message = __('You sent too many inquiries to Invoice Pro in too short a time. Please wait a moment and then try again.', 'fakturpro');
        return array('title' => $title, 'message' => $message);
    }

    /**
     * Render an internal server error to the user.
     *
     * @return array<string, string>
     */
    private function error_server_internal()
    {
        $title = __('Server application error (Error 500)', 'fakturpro');
        $message = __('An unexpected server application error occurred. Please contact support.', 'fakturpro');
        return array('title' => $title, 'message' => $message);
    }

    /**
     * Render an internal server error to the user.
     *
     * @return array<string, string>
     */
    private function error_server_unavailable()
    {
        $title = __('Can not reach server (Error 503)', 'fakturpro');
        $message = __('The server application is temporarily unavailable. Please try again later.', 'fakturpro');
        return array('title' => $title, 'message' => $message);
    }

    /**
     * Render an error concerning a missing implementation.
     *
     * @return array<string, string>
     */
    private function error_method_not_implemented()
    {
        $title = __('Not yet available (Error 501)', 'fakturpro');
        $message = __('This function is not currently available, but will be implemented in the future.', 'fakturpro');
        return array('title' => $title, 'message' => $message);
    }

    /**
     * Render the error that a resource could not be found.
     *
     * @return array<string, string>
     */
    private function error_resource_not_found()
    {
        $title = __('Invoice not found (Error 404)', 'fakturpro');
        $message = __('The invoice was not found. Are you sure this bill exists?', 'fakturpro');
        return array('title' => $title, 'message' => $message);
    }

    /**
     * Render the error that a resource is locked.
     *
     * @return array<string, string>
     */
    private function error_resource_locked()
    {
        $title = __('Services have been blocked (Error 423)', 'fakturpro');
        return array('title' => $title, 'message' => $this->get_error_message());
    }

    /**
     * Render the error that there is a conflict.
     *
     * @return array<string, string>
     */
    private function error_conflict()
    {
        $title = __('Conflict occurred (Error 409)', 'fakturpro');
        return array('title' => $title, 'message' => $this->get_error_message());
    }

    /**
     * Render the error that the requested media type is not supported.
     *
     * @return array<string, string>
     */
    private function error_unsupported_media_type()
    {
        $title = __('Media type not supported (Error 415)', 'fakturpro');
        $message = __('The requested media type is not supported by Invoice Pro.', 'fakturpro');
        return array('title' => $title, 'message' => $message);
    }

    /**
     * Render anything else that might potentially happen but should not.
     *
     * @return array<string, string>
     */
    private function error_unknown_reasons()
    {
        $title = __('Cause unclear', 'fakturpro');
        $message = __('An error with an unknown cause has occurred. Please contact support.', 'fakturpro');
        return array('title' => $title, 'message' => $message);
    }
}

endif;
