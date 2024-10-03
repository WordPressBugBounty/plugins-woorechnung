<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'FP_Session') ):

/**
 * Faktur Pro Session Component.
 *
 * @version  1.0.0
 * @package  FakturPro\Common
 * @author   Zweischneider
 */
final class FP_Session
{
    /**
     * The plugin instance via dependency injection.
     *
     * @var FP_Plugin $_plugin
     */
    private $_plugin;

	/**
	 * Cookie name used for the session.
	 *
	 * @var string $_cookie_name
	 */
    private $_cookie_name;

    /**
     * Data of the session.
     * 
     * @var array<string, mixed> $_data
     */
    private $_data = array();

    /**
     * Create a new instance of this invoice factory.
     *
     * @param  FP_Plugin $plugin
     */
    public function __construct(FP_Plugin $plugin)
    {
        $this->_plugin = $plugin;
        add_action('init', array($this, 'init'));
    }

    /**
     * Initializes the session.
     *
     * @return void
     */
    public function init()
    {
        // Load cookie raw data
        $this->_cookie_name = 'wp_fakturpro_session_' . COOKIEHASH;
        if ( empty( $_COOKIE[ $this->_cookie_name ] ) ) {
            return;
        }
        $cookie = wp_unslash( $_COOKIE[ $this->_cookie_name ] );

        // Decode cookie raw data
        $data = json_decode( base64_decode( $cookie ), true);

        // Only read allowed data (filters unknown fields)
        $fp_notices = isset( $data[ 'fp_notices' ] ) ? $data[ 'fp_notices' ] : array();

        // Map and sanitize data
        $this->_data = array(
            'fp_notices' => array_map(
                function ( $notice ) {
                    $type = sanitize_key( $notice['type'] );
                    return array(
                        'message' => wp_kses( $notice['message'], FP_Admin_Notices::allowed_html() ),
                        'type' => in_array( $type, array( 'success', 'error', 'warning', 'info' ) ) ? $type : 'info',
                        'is_dismissible' => is_bool( $notice[ 'is_dismissible' ] ) ? $notice[ 'is_dismissible' ] : true,
                    );
                },
                $fp_notices
            )
        );

        // Read session data
        $this->_data = $this->sanitize_session( $_COOKIE[$this->_cookie_name] );
    }

    /**
     * Sanitize the session cookie.
     * 
     * @param  string $cookie_data
     * @return array<string, mixed>
     */
    private function sanitize_session( $cookie_data )
    {
        // Decode cookie raw data
        $data = json_decode( base64_decode( wp_unslash( $cookie_data ) ), true) ;

        // Only read allowed data (filters unknown fields)
        $fp_notices = isset( $data[ 'fp_notices' ] ) ? $data[ 'fp_notices' ] : array();

        // Map and sanitize data
        return array(
            'fp_notices' => array_map(
                function ( $notice ) {
                    $type = sanitize_key( $notice[ 'type' ] );
                    return array(
                        'message' => wp_kses( $notice[ 'message' ], FP_Admin_Notices::allowed_html() ),
                        'type' => in_array( $type, array( 'success', 'error', 'warning', 'info' ) ) ? $type : 'info',
                        'is_dismissible' => is_bool( $notice[ 'is_dismissible' ] ) ? $notice[ 'is_dismissible' ] : true,
                    );
                },
                $fp_notices
            )
        );
    }

    /**
     * Updates session cookie data.
     * 
     * @return void
     */
    public function update_cookie()
    {
        if ( !headers_sent() ) {
            setcookie( $this->_cookie_name, base64_encode( json_encode ($this->_data ) ), time() + HOUR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
        } else {
            headers_sent( $file, $line );
            $this->_plugin->get_logger()->error( "Session start failed! Headers already sent by {$file} on line {$line}." );
        }
    }

    /**
     * Set value in session via key.
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function set( $key, $value )
    {
        $this->_data[ sanitize_key( $key ) ] = $value;
        $this->update_cookie();
    }

    /**
     * Check if key isset in session.
     *
     * @param  string $key
     * @return bool
     */
    public function isset( $key )
    {
        return isset( $this->_data[ sanitize_key( $key ) ] );
    }

    /**
     * Unset value in session via key.
     *
     * @param  string $key
     * @return void
     */
    public function unset( $key )
    {
        $key = sanitize_key( $key );
        if ( isset( $this->_data[ $key ] ) ) {
            unset( $this->_data[ $key ] );
            $this->update_cookie();
        }
    }

    /**
     * Get value from session via key or default if not set.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get( $key, $default = null )
    {
        $key = sanitize_key( $key );
        return isset( $this->_data[ $key ] ) ? $this->_data[ $key ] : $default;
    }
}

endif;
