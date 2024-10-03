<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Admin_Assets' ) ):

/**
 * Faktur Pro Admin Assets Class
 *
 * This class conditionally loads the assets for the admin backend.
 * The CSS stylesheet and the JavaScript file are enqueued when
 * a user with admin privileges is logged in.
 *
 * @version  1.0.0
 * @package  FakturPro\Admin
 * @author   Zweischneider
 */
final class FP_Admin_Assets extends FP_Abstract_Module
{
    /**
     * The key to use for the admin assets.
     *
     * @var string
     */
    const ASSETS_KEY = 'fakturpro_admin';

    /**
     * The admin CSS stylesheet.
     *
     * @var string
     */
    const ASSET_CSS = '/assets/css/fakturpro-admin.css';

    /**
     * The admin JavaScript file.
     *
     * @var string
     */
    const ASSET_JS = '/assets/js/fakturpro-admin.js';

    /**
     * Initialize the hooks of this module.
     *
     * @return void
     */
    public function init_hooks()
    {
        if ( is_admin() ) {
            $this->add_action('admin_enqueue_scripts', 'load_styles');
            $this->add_action('admin_enqueue_scripts', 'load_scripts');
        }
    }

    /**
     * Get the full path for the admin styles file.
     *
     * @return string
     */
    private function styles_path()
    {
        return $this->plugin()->get_url( self::ASSET_CSS );
    }

    /**
     * Get the full path for the admin scripts file.
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
    private function register_style($key, $path)
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
    private function register_script($key, $path)
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
