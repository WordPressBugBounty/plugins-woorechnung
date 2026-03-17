<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Admin_Pages' ) ):

/**
 * Faktur Pro Log Component
 *
 * @version  1.0.0
 * @package  FakturPro\Admin
 * @author   Zweischneider
 */
final class FP_Admin_Pages extends FP_Abstract_Module
{
    /**
     * Initialize the hooks of this module.
     *
     * @return void
     */
    public function init_hooks()
    {
        if ( is_admin() ) {
            $this->add_action( 'admin_init', 'handle_actions' );
            $this->add_action( 'admin_menu', 'admin_menu', 99999 );
        }
    }

    /**
     * Adds admin menu points for pages.
     *
     * @return void
     */
    public function admin_menu()
    {
        if ( $this->plugin()->is_logging_enabled() ) {
            add_submenu_page(
                'woocommerce',
                __( 'Faktur Pro Log', 'fakturpro' ),
                __( 'Faktur Pro Log', 'fakturpro' ),
                'manage_woocommerce',
                'fakturpro-log',
                array( $this, 'log_page' ),
                99999
            );
        }
    }

    /**
     * Handles actions.
     *
     * @return void
     */
    public function handle_actions()
    {
		if ( isset( $_GET['action'], $_GET['_nonce'] ) ) {
            $action = wc_clean( wp_unslash( $_GET['action'] ) );
            $nonce = wp_unslash( $_GET['_nonce'] );

            if ( $action == 'fakturpro-clear-log' && wp_verify_nonce( $nonce, $action ) ) {
                $this->logger()->clear_contents();
                FP_Admin_Notices::add_notice(
                    __( 'Log file cleared.', 'fakturpro' ),
                    FP_Admin_Notices::NOTICE_TYPE_SUCCESS,
                    true
                );
            }
        }
    }

    /**
     * Output the log page content.
     *
     * @return void
     */
    public function log_page()
    {
        echo '<div class="wrap">';
        echo '<h1>' . __( 'Faktur Pro Log', 'fakturpro' ) . '</h1>';

        // Clear button
        $params = array( 'page' => 'fakturpro-log', 'action' => 'fakturpro-clear-log' );
        $url = 'admin.php?' . http_build_query( $params );
        $url = wp_nonce_url( admin_url( $url ), 'fakturpro-clear-log', '_nonce' );
        echo '<a href="' . $url . '" target="_self" class="button button-primary">';
        echo __( 'Clear log', 'fakturpro' );
        echo '</a>';

        // Log content
        echo '<p style="white-space: pre-wrap;">';
        echo $this->logger()->read_contents();
        echo '</p>';

        echo '</div>';
    }
}

endif;
