<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Abstract_Client' ) ):

/**
 * Faktur Pro Abstract HTTP Client Class
 *
 * @class    FP_Abstract_Client
 * @version  1.0.0
 * @package  FakturPro\Abstract
 * @author   Zweischneider
 */
abstract class FP_Abstract_Client
{
    /**
     * The GET HTTP method.
     *
     * @var string
     */
    const HTTP_METHOD_GET = 'GET';

    /**
     * The POST HTTP method.
     *
     * @var string
     */
    const HTTP_METHOD_POST = 'POST';

    /**
     * The PUT HTTP method.
     *
     * @var string
     */
    const HTTP_METHOD_PUT = 'PUT';

    /**
     * The DELETE HTTP method.
     *
     * @var string
     */
    const HTTP_METHOD_DELETE = 'DELETE';


    /**
     * The HTTP user agent header.
     *
     * @var string
     */
    const HTTP_HEADER_AGENT = 'User-Agent';

    /**
     * The HTTP authorization header.
     *
     * @var string
     */
    const HTTP_HEADER_AUTH = 'Authorization';

    /**
     * The HTTP content type header.
     *
     * @var string
     */
    const HTTP_HEADER_CONTENT = 'Content-Type';

    /**
     * The HTTP accept header.
     *
     * @var string
     */
    const HTTP_HEADER_ACCEPT = 'Accept';

    /**
     * The HTTP accept language header.
     *
     * @var string
     */
    const HTTP_HEADER_ACCEPT_LANG = 'Accept-Language';

    /**
     * The custom HTTP header to send to identify the base URL of this shop.
     *
     * @var string
     */
    const HTTP_HEADER_SHOP_URL = 'X-Shop-URL';

    /**
     * The custom HTTP header to identify the system of this shop.
     *
     * @var string
     */
    const HTTP_HEADER_SHOP_SYSTEM = 'X-Shop-System';

    /**
     * The custom HTTP header to identify this request and trace back errors.
     *
     * @var string
     */
    const HTTP_HEADER_TRACE_ID = 'X-Trace-Id';

    /**
     * The MIME type for JSON contents.
     *
     * @var string
     */
    const MIME_TYPE_JSON = 'application/json';

    /**
     * The shop system to be used for identification.
     *
     * @var string
     */
    const SYSTEM_WOOCOMMERCE = 'woocommerce';

    /**
     * Status codes of successful requests.
     *
     * @var array<int>
     */
    private static $_success = array( 200, 201, 204 );

    /**
     * The plugin instance via dependency injection.
     *
     * @var FP_Plugin
     */
    private $_plugin;

    /**
     * The last trace ID that has been generated.
     *
     * @var string|null
     */
    private $_trace_id = null;

    /**
     * Create a new instance of this HTTP client component.
     *
     * @param  FP_Plugin $plugin
     * @return void
     */
    public function __construct(FP_Plugin $plugin)
    {
        $this->_plugin = $plugin;
    }

    /**
     * Load the API token required for authorization.
     *
     * @return string
     */
    private function load_token()
    {
        return $this->_plugin->get_settings()->get_shop_token();
    }

    /**
     * Load the plugin user agent.
     *
     * @return string
     */
    private function load_user_agent()
    {
        return $this->_plugin->get_user_agent();
    }

    /**
     * Load the home url of this shop required for authorization.
     *
     * @return string
     */
    private static function load_home_url()
    {
        return get_home_url();
    }

    /**
     * Generate a random trace ID for this request.
     *
     * @return string
     */
    private function generate_trace_id()
    {
        $this->_trace_id = uniqid();
        return $this->_trace_id;
    }

    /**
     * Return the last trace ID this client has generated.
     *
     * @return string|null
     */
    private function get_last_trace_id()
    {
        return $this->_trace_id;
    }

    /**
     * Load the WordPress locale for language sensitive responses.
     *
     * @return string
     */
    private static function load_locale()
    {
        $locale = strval(get_locale());
        $explode = explode('_', $locale, 2);
        return array_shift($explode);
    }

    /**
     * Load the API base uri to send requests to.
     *
     * @return string
     */
    private function load_base_uri()
    {
        return $this->_plugin->get_server_uri();
    }

    // /**
    //  * Get the value for the curl connection timeout option.
    //  *
    //  * @return int
    //  */
    // private static function option_connecttimeout()
    // {
    //     return FAKTURPRO_HTTP_CONNECTTIMEOUT;
    // }

    /**
     * Get the value for the curl request timeout option.
     *
     * @return int
     */
    private static function option_timeout()
    {
        return FAKTURPRO_HTTP_REQUESTTIMEOUT;
    }

    // /**
    //  * Get the value for the curl verbose option.
    //  *
    //  * @return int
    //  */
    // private static function option_verbose()
    // {
    //     return FAKTURPRO_HTTP_VERBOSE;
    // }

    /**
     * Decode the response body from JSON.
     *
     * @param  string $data
     * @return array<string, mixed>
     */
    private static function decode_body( $data )
    {
        return json_decode( strval( $data ), true );
    }

    /**
     * Prepare the HTTP headers for a request.
     *
     * @return array<string, mixed>
     */
    private function prepare_headers( )
    {
        return array(
            self::HTTP_HEADER_AGENT => $this->load_user_agent(),
            self::HTTP_HEADER_AUTH => "Bearer {$this->load_token()}",
            self::HTTP_HEADER_TRACE_ID => $this->generate_trace_id(),
            self::HTTP_HEADER_SHOP_SYSTEM => self::SYSTEM_WOOCOMMERCE,
            self::HTTP_HEADER_SHOP_URL => self::load_home_url(),
            self::HTTP_HEADER_CONTENT => self::MIME_TYPE_JSON,
            self::HTTP_HEADER_ACCEPT => self::MIME_TYPE_JSON,
            self::HTTP_HEADER_ACCEPT_LANG => self::load_locale(),
        );
    }

    // /**
    //  * Prepare the default curl options for a request.
    //  *
    //  * @return array
    //  */
    // private function prepare_options( )
    // {
    //     $options = array();
    //     $options[CURLOPT_RETURNTRANSFER] = true;
    //     $options[CURLOPT_CONNECTTIMEOUT] = self::option_connecttimeout();
    //     $options[CURLOPT_TIMEOUT] = self::option_timeout();
    //     $options[CURLOPT_VERBOSE] = self::option_verbose();
    //     return $options;
    // }

    /**
     * Prepare the target uri for a request.
     *
     * @param  string $path
     * @return string
     */
    private function prepare_target( $path )
    {
        return rtrim($this->load_base_uri(), '/') . '/' . ltrim($path, '/');
    }

    /**
     * Send a HTTP request using the GET method.
     *
     * @param  string $url
     * @return array<string, mixed>
     */
    public function send_get( $url )
    {
        return $this->request( self::HTTP_METHOD_GET, $url );
    }

    /**
     * Send a HTTP request using the POST method.
     *
     * @param  string $url
     * @param  array<string, mixed>|null  $data
     * @return array<string, mixed>
     */
    public function send_post( $url, $data = null )
    {
        return $this->request( self::HTTP_METHOD_POST, $url, $data );
    }

    /**
     * Send a HTTP request using the PUT method.
     *
     * @param  string $url
     * @param  array<string, mixed>|null  $data
     * @return array<string, mixed>
     */
    public function send_put( $url, $data = null )
    {
        return $this->request( self::HTTP_METHOD_PUT, $url, $data );
    }

    /**
     * Send a HTTP request using the DELETE method.
     *
     * @param  string $url
     * @return array<string, mixed>
     */
    public function send_delete( $url )
    {
        return $this->request( self::HTTP_METHOD_DELETE, $url );
    }

    /**
     * Send a request using the php curl library.
     *
     * @param  string $method
     * @param  string $path
     * @param  array<string, mixed>|null $data
     * @return array<string, mixed>
     */
    private function request( $method, $path, $data = null)
    {
        // Prepare request url
        $url = $this->prepare_target( $path );

        // Prepare request args
        $args = array(
            'method' => $method,
            'timeout' => self::option_timeout(),
            'headers' => $this->prepare_headers(),
            'body' => !empty( $data ) && $method != 'GET' ? json_encode( $data ) : $data,
        );

        // Send the request prepared and return the response
        $response = wp_remote_request( $url, $args );
        
        // Get the http code
        $code = wp_remote_retrieve_response_code( $response );

        // Check if a wp error occured
        // If so, throw a client exception
        // If not, continue with the http result returned
        if ( is_wp_error($response) ) {
            $error = array(
                'error_code' => $response->get_error_code(),
                'message' => strval( $response->get_error_message() ),
            );
            throw new FP_Server_Error( json_encode( $error ), intval( $code ) );
        }

        // Get the response body
        $body = wp_remote_retrieve_body( $response );

        // Check if a client side error ocurred
        // If so, throw a client exception
        // If not, continue with the http result returned
        // Catch HTTP errors
        if (! in_array( $code, self::$_success )) {
            throw new FP_Server_Error( strval( $body ), $code );
        }

        // Return the result containing the response body
        return self::decode_body( $body );
    }
}

endif;
