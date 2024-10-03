<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Customer_Assets' ) ):

/**
 * Faktur Pro global Assets Class
 *
 * This class conditionally loads the assets for the global backend.
 * The CSS stylesheet and the JavaScript file are globally enqueued.
 *
 * @version  1.0.0
 * @package  FakturPro
 * @author   Zweischneider
 */
final class FP_Customer_Assets extends FP_Abstract_Module
{
    /**
     * The key to use for the global assets.
     *
     * @var string
     */
    const ASSETS_KEY = 'fakturpro_customer';

    /**
     * The global CSS stylesheet.
     *
     * @var string
     */
    const ASSET_CSS = '/assets/css/fakturpro-customer.css';

    /**
     * The global JavaScript file.
     *
     * @var string
     */
    const ASSET_JS = '/assets/js/fakturpro-customer.js';

    /**
     * Initialize the hooks of this module.
     *
     * @return void
     */
    public function init_hooks()
    {
        $this->add_action( 'wp_enqueue_scripts', 'load_styles', 30 );
        $this->add_action( 'wp_enqueue_scripts', 'load_scripts', 30 );
    }

    /**
     * Get the full path for the global styles file.
     *
     * @return string
     */
    private function styles_path()
    {
        return $this->plugin()->get_url( self::ASSET_CSS );
    }

    /**
     * Get the full path for the global scripts file.
     *
     * @return string
     */
    private function scripts_path()
    {
        return $this->plugin()->get_url( self::ASSET_JS );
    }

    /**
     * Register a stylesheet within the plugin.
     *
     * @param  string $key
     * @param  string $path
     * @return void
     */
    private function register_style( $key, $path )
    {
        wp_register_style( $key, $path );
        wp_enqueue_style( $key );
    }

    /**
     * Register a script within the plugin.
     *
     * @param  string $key
     * @param  string $path
     * @return void
     */
    private function register_script( $key, $path )
    {
        wp_register_script( $key, $path );
        wp_enqueue_script( $key );
    }

    /**
     * Register and enqueue the plugin stylesheet.
     *
     * @return void
     */
    public function load_styles()
    {
        $this->register_style( self::ASSETS_KEY, $this->styles_path() );
    }

    /**
     * Register and enqueue the javascript file.
     *
     * @return void
     */
    public function load_scripts()
    {
        $this->register_script( self::ASSETS_KEY, $this->scripts_path() );
    }
}

endif;
