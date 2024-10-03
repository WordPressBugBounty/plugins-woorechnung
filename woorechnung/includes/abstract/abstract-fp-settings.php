<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Abstract_Settings' ) ):

/**
 * Faktur Pro Abstract Settings Class
 *
 * @class    FP_Abstract_Settings
 * @version  1.0.0
 * @package  FakturPro\Abstract
 * @author   Zweischneider
 */
abstract class FP_Abstract_Settings
{
    /**
     * The (unprefixed) keys of all settings.
     *
     * @var array<string>
     */
    protected $_keys = array();

    /**
     * The plugin instance via dependency injection.
     *
     * @var FP_Plugin
     */
    private $_plugin;

    /**
     * Create a new instance of the options component.
     *
     * @param  FP_Plugin $plugin
     * @return void
     */
    public function __construct(FP_Plugin $plugin)
    {
        $this->_plugin = $plugin;
    }

    /**
     * Prefix a given key with the plugin settings prefix.
     *
     * @param  string $key
     * @return string
     */
    protected function prefix( $key )
    {
        return $this->_plugin->get_settings_prefix() . $key;
    }

    /**
     * Return the (prefixed) keys for all settings of this plugin.
     *
     * @return array<string>
     */
    protected function keys()
    {
        return array_map( array($this, 'prefix'), $this->_keys );
    }

    /**
     * Return the values for all settings of this plugin.
     *
     * @return array<mixed>
     */
    protected function values()
    {
        return array_map( 'get_option', $this->keys() );
    }

    /**
     * Return the plugin settings as key value pairs.
     *
     * @return array<string, mixed>
     */
    public function all()
    {
        return array_combine( $this->keys(), $this->values() );
    }

    /**
     * Decide if a specific setting is available.
     *
     * @param string $key
     * @return bool
     */
    protected function has( $key )
    {
        return ! is_null( get_option( $this->prefix( $key ) ) );
    }

    /**
     * Decide if a specific setting is empty.
     *
     * @param  string $key
     * @return bool
     */
    protected function is_empty( $key )
    {
        $option = get_option( $this->prefix( $key ) );
        $option = trim ( $option );
        return empty( $option );
    }

    /**
     * Get a specific value for a settings key or return a default value.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    protected function get( $key, $default = null )
    {
        return get_option( $this->prefix( $key ), $default);
    }

    /**
     * Create a specific value for a settings key.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function create( $key, $value )
    {
        return add_option( $this->prefix( $key ), $value );
    }

    /**
     * Update an existing value for a settings key.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function update( $key, $value )
    {
        return update_option( $this->prefix( $key ), $value );
    }

    /**
     * Create or update a setting for a settings key.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function set( $key, $value )
    {
        return $this->has( $key ) ? $this->update( $key, $value ) : $this->create( $key, $value );
    }
}

endif;
