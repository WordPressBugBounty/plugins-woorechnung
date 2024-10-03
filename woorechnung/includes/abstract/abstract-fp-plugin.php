<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Abstract_Plugin' ) ):

/**
 * Faktur Pro Abstract Module Class
 *
 * @version  1.0.0
 * @package  FakturPro\Abstract
 * @author   Zweischneider
 */
abstract class FP_Abstract_Plugin
{
    /**
     * The filesystem plugin file to the plugin.
     *
     * @var string
     */
    private $_plugin_file;

    /**
     * The filesystem path to the plugin's folder.
     *
     * @var string
     */
    private $_plugin_path;

    /**
     * The server URL pointing to the plugin installation.
     *
     * @var string
     */
    private $_plugin_url;

    /**
     * Get the name of this plugin.
     *
     * @return string
     */
    abstract public function get_name();

    /**
     * Get the current version of this plugin.
     *
     * @return string
     */
    abstract public function get_version();

    /**
     * Set the filesystem plugin file to this plugin.
     *
     * @param  string $file
     * @return void
     */
    public function set_file( $file )
    {
        $this->_plugin_file = $file;
    }

    /**
     * Set the filesystem path to this plugin.
     *
     * @param  string $path
     * @return void
     */
    public function set_path( $path )
    {
        $this->_plugin_path = $path;
    }

    /**
     * Set the server url to this plugin.
     *
     * @param  string $url
     * @return void
     */
    public function set_url( $url )
    {
        $this->_plugin_url = $url;
    }

    /**
     * Get the plugin file to the plugin.
     *
     * @return string
     */
    public function get_file()
    {
        return $this->_plugin_file;
    }

    /**
     * Get the path to the plugin, optionally appending a subpath.
     *
     * @param  string $append
     * @return string
     */
    public function get_path( $append = '' )
    {
        return $this->_plugin_path . $append;
    }

    /**
     * Get the base url of the plugin, optionally appending another part.
     *
     * @param  string $append
     * @return string
     */
    public function get_url( $append = '' )
    {
        return $this->_plugin_url . $append;
    }

    /**
     * Decide if a particular plugin is active.
     *
     * @param  string $name
     * @return bool
     */
    public function is_plugin_active($name)
    {
        $active = in_array( $name, get_option('active_plugins') );
        if ( is_multisite() ) {
            $active = in_array( $name, array_keys( get_site_option('active_sitewide_plugins') ) ) ? true : $active;
        }
        return $active;
    }

    /**
     * Is email type an email to send to customer?
     *
     * @param   string  $email_type
     * @return  bool
     */
    public function is_email_to_customer($email_type)
    {
        return !in_array($email_type, array(
            'new_order',
            'cancelled_order',
            'failed_order',

            // WooCommerce Subscriptions
            'new_renewal_order',
            'cancelled_subscription',
            'expired_subscription',
            'new_switch_order',
            'suspended_subscription',
            'payment_retry',
        ));
    }

    /**
     * Check for min php version
     *
     * @param   string  $version    PHP version e.g. "5.4.0"
     * @return  bool    Returns true if php version is greater or equal $version
     */
    public static function min_php_version_check($version)
    {
        return version_compare(phpversion(), $version, '>=');
    }

    /**
     * Get informations about the calling function or method.
     *
     * @param   int $trace_index        The index to trace back to find the correct function or method (default: 1).
     * @return  array<string, mixed>    Associative array with index "function" and "class".
     */
    public static function get_caller_info( $trace_index = 1 )
    {
        $options = 0;
        if (defined('DEBUG_BACKTRACE_IGNORE_ARGS')) {
            $options = DEBUG_BACKTRACE_IGNORE_ARGS;
        } else if (self::min_php_version_check('5.3.6') == false) {
            $options = false;
        }

        if (self::min_php_version_check('5.4.0')) {
            $backtrace = debug_backtrace($options, $trace_index+1);
        } else {
            $backtrace = debug_backtrace($options);
        }

        return array(
            'function' => $backtrace[$trace_index]['function'],
            'class' => !empty($backtrace[$trace_index]['class']) ? $backtrace[$trace_index]['class'] : null
        );
    }

    /**
     * Encodes html content to save in database.
     *
     * @param   string  $value
     * @return  string
     */
    public static function encode_html_content($value)
    {
        return 'base64:'.base64_encode($value);
    }

    /**
     * Decodes html content that was saved in database.
     *
     * @param   string  $value
     * @return  string
     */
    public static function decode_html_content($value)
    {
        if (substr($value, 0, 7) === 'base64:') {
            $value = substr($value, 7);
            return stripslashes(base64_decode($value));
        }
        return $value;
    }
}

endif;
