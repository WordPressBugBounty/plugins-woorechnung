<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Admin_Notices' ) ):

/**
 * Faktur Pro Admin Notices Class
 *
 * @version  1.0.0
 * @package  FakturPro\Admin
 * @author   Zweischneider
 */
final class FP_Admin_Notices extends FP_Abstract_Module
{
    const NOTICE_TYPE_SUCCESS = 'success';
    const NOTICE_TYPE_ERROR = 'error';
    const NOTICE_TYPE_WARNING = 'warning';
    const NOTICE_TYPE_INFO = 'info';

    /**
     * The admin notices singleton instance.
     *
     * @var FP_Admin_Notices
     */
    private static $instance;

    /**
     * Initialize the hooks of this module.
     *
     * @return void
     */
    public function init_hooks()
    {
        self::$instance = $this;

        if ( ! is_admin() ) {
            return;
        }

        if ( ! $this->plugin()->is_woocommerce_active() ) {
            $this->add_action('admin_notices', 'show_notice_woocommerce');
        }

        if ( ! $this->plugin()->is_germanized_active() ) {
            // $this->add_action('admin_notices', 'show_notice_germanized');
        }

        if ( ! $this->settings()->has_shop_credentials() ) {
            $this->add_action('admin_notices', 'show_notice_credentials');
        }

        $this->add_action('admin_notices', 'show_notice_config');
        $this->add_action('admin_notices', 'show_session_notices');
        $this->add_action('admin_notices', 'show_hideable_notices');
    }

    /**
     * Checks and hides notices, which can be permanently hidden per user.
     *
     * @return void
     */
    public function dismiss_hideable_notice()
    {
        if ( ! is_admin() ) {
            return;
        }

        if ( isset( $_GET['fp-dismiss-notice'] ) && isset( $_GET['_fp_notice_nonce'] ) ) {
            if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_fp_notice_nonce'] ) ), 'fakturpro_dismiss_notices_nonce' ) ) {
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'fakturpro' ) );
			}
            update_user_meta( get_current_user_id(), 'fp_notice_' . $_GET['fp-dismiss-notice'] . '_dismissed', true );
            do_action( 'fakturpro_dismiss_' . $_GET['fp-dismiss-notice'] . '_notice' );
        }

    }

    /**
     * Displays a notice that can be permanently hidden per user.
     *
     * @param  string $id
     * @param  string $message
     * @param  string $type
     * @return void
     */
    public static function show_hideable_notice( $id, $message, $type = self::NOTICE_TYPE_INFO )
    {
        if ( ! is_admin() ) {
            return;
        }

        $dismissed = maybe_unserialize( get_user_meta( get_current_user_id(), 'fp_notice_' . $id . '_dismissed', false ) );
        if ($dismissed == false) {
            $dismiss_url = esc_url( wp_nonce_url( add_query_arg( 'fp-dismiss-notice', $id ), 'fakturpro_dismiss_notices_nonce', '_fp_notice_nonce' ) );
            echo "<div class='notice notice-".esc_attr( $type )." is-dismissible'>"
                . "<p>"
                . wp_kses( $message, self::allowed_html() )
                . "</p>"
                . '<p><a class="button button-primary" href="' . $dismiss_url . '" target="_self">' . __( 'Hide permanently', 'fakturpro' ) . '</a></p>'
                . "<a class='notice-dismiss' style='text-decoration: none;' href='".$dismiss_url."'>"
                . "<span class='screen-reader-text'>".esc_html( __( 'Dismiss this notice.' ) )."</span>"
                . "</a>"
                . "</div>";
        }
    }

    /**
     * Shows notices that are persist hideable per user.
     *
     * @return void
     */
    public function show_hideable_notices()
    {
        $this->dismiss_hideable_notice();

        $today = new \DateTime('today');

        if ($this->plugin()->is_in_time( '2024-01-09', '2024-01-31', $today )) {
            $href = "https://faktur.pro";
            $message = '<strong>20% Rabatt</strong> auf die ersten 6 Monate für alle Neukunden eines Monatstarifes mit dem Code: <strong>20FOR8</strong><br>';
            $message .= '<strong>Nur für kurze Zeit Verfügbar, also schnell sein!</strong><br>';
            $message .= 'Wir danken euch für 8 Jahre Faktur Pro!<br>';
            $message .= 'Gleich bei <a href="'.esc_url( $href ).'" target="_blank">Faktur Pro</a> einlösen.';
            self::show_hideable_notice('promo_code_jan_2024', $message, self::NOTICE_TYPE_INFO);
        }
    }

    /**
     * Add notice to be shown as an admin notice.
     *
     * @param  string $message
     * @param  string $type
     * @param  bool   $is_dismissible
     * @return void
     */
    public static function add_notice($message, $type = self::NOTICE_TYPE_INFO, $is_dismissible = true)
    {
        if ( ! is_admin() ) {
            return;
        }

        $notices = self::$instance->session()->get('fp_notices', array());
        $notices[] = array('message' => $message, 'type' => $type, 'is_dismissible' => $is_dismissible);
        self::$instance->session()->set('fp_notices', $notices);
    }

    /**
     * Shows notices previously added via add_notice().
     *
     * @return void
     */
    public function show_session_notices()
    {
        if ( ! is_admin() ) {
            return;
        }

        $notices = $this->session()->get('fp_notices', null);
        if (!empty($notices)) {
            foreach ($notices as $notice) {
                self::notice($notice['message'], $notice['type'], $notice['is_dismissible']);
            }
            $this->session()->unset('fp_notices');
        }
    }

    /**
     * Get allowed html for messages.
     * 
     * @return array<string, mixed>
     */
    public static function allowed_html()
    {
        return array(
            'br' => array(),
            'strong' => array(),
            'a' => array(
                'href'  => array(
                    // "https://de.wordpress.org/plugins/woocommerce-germanized/",
                    // admin_url('/admin.php?page=wc-settings&tab=fakturpro'),
                ),
                'target' => array(),
            ),
        );
    }

    /**
     * Show an admin notice.
     *
     * @param  string $message
     * @param  string $type
     * @param  bool   $is_dismissible
     * @return void
     */
    private static function notice($message, $type, $is_dismissible = true)
    {
        if ( ! is_admin() ) {
            return;
        }

        echo "<div class='notice notice-".esc_attr( $type )."".($is_dismissible ? " is-dismissible" : '')."'>"
            ."<p><strong>".wp_kses( $message, self::allowed_html() )."</strong></p>"
            . (
                $is_dismissible
                ? "<button type='button' class='notice-dismiss'>"
                . "<span class='screen-reader-text'>".esc_html( __( 'Dismiss this notice.' ) )."</span>"
                . "</button>"
                : ''
            )
            . "</div>";
    }

    /**
     * Return a HTML string that is to be shown as a success message.
     *
     * @param  string $message
     * @param  bool   $is_dismissible
     * @return void
     */
    private static function notice_success($message, $is_dismissible = true)
    {
        self::notice($message, self::NOTICE_TYPE_SUCCESS, $is_dismissible);
    }

    /**
     * Return a HTML string that is to be shown as an error message.
     *
     * @param  string $message
     * @param  bool   $is_dismissible
     * @return void
     */
    private static function notice_error($message, $is_dismissible = true)
    {
        self::notice($message, self::NOTICE_TYPE_ERROR, $is_dismissible);
    }

    /**
     * Return a HTML string that is to be shown as a warning.
     *
     * @param  string $message
     * @param  bool   $is_dismissible
     * @return void
     */
    private static function notice_warning($message, $is_dismissible = true)
    {
        self::notice($message, self::NOTICE_TYPE_WARNING, $is_dismissible);
    }

    /**
     * Return a HTML string that is to be shown as a piece of information.
     *
     * @param  string $message
     * @param  bool   $is_dismissible
     * @return void
     */
    private static function notice_information($message, $is_dismissible = true)
    {
        self::notice($message, self::NOTICE_TYPE_INFO, $is_dismissible);
    }

    /**
     * Show an error message to signify that WooCommerce is inactive.
     *
     * @return void
     */
    public function show_notice_woocommerce()
    {
		if (current_user_can('activate_plugins')) {
            $message = 'Faktur Pro: bitte aktiviere WooCommerce, um Faktur Pro zu nutzen.';
            self::notice_error($message);
        }
    }

    /**
     * Show an informational notice recommending Germanized.
     *
     * @return void
     */
    public function show_notice_germanized()
    {
        $href = "https://de.wordpress.org/plugins/woocommerce-germanized/";
        $message = "Faktur Pro: wir empfehlen die Verwendung von "
            . "<a href='".esc_url( $href )."' target='_BLANK'>WooCommerce Germanized</a>!";
        self::notice_information($message);
    }

    /**
     * Show an error message to signify that the credentials are missing.
     *
     * @return void
     */
    public function show_notice_credentials()
    {
        $href = admin_url('/admin.php?page=wc-settings&tab=fakturpro');
        $message = "Faktur Pro: Zugangsdaten noch nicht eingegeben "
            . "<a href='".esc_url( $href )."'>(hier konfigurieren)</a>.";
        self::notice_error($message);
    }

    /**
     * Show an warning message if specific config settings are made.
     *
     * @return void
     */
    public function show_notice_config()
    {
        $is_enabled = array(
            'debugging' => $this->plugin()->is_debugging_enabled(),
            'logging' => $this->plugin()->is_logging_enabled(),
            'verbose' => $this->plugin()->is_logging_verbose()
        );
        if (in_array(true, $is_enabled)) {
            $message = $this->plugin()->get_name().' '.implode(', ', array_keys($is_enabled, true)).' ist aktiv.';
            self::notice_warning($message, false);
        }
    }

}

endif;
