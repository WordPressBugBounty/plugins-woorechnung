<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Handler' ) ):

/**
 * Faktur Pro Error Handler Class.
 *
 * @class    FP_Handler
 * @version  1.0.0
 * @package  FakturPro\Common
 * @author   Zweischneider
 */
final class FP_Handler
{
    /**
     * The page title to be displayed on the error page.
     *
     * @var string
     */
    const ERROR_TITLE = 'Faktur Pro Fehler';

    /**
     * The URI of the Faktur Pro server application.
     *
     * @var string
     */
     const WEBSITE_LINK = 'https://www.faktur.pro';

    /**
     * The URI of the Faktur Pro knowledge base for customer self-support.
     *
     * @var string
     */
    const SUPPORT_DOCS = 'https://support.faktur.pro/knowledgebase.php';

    /**
     * The email address of the Faktur Pro customer support.
     *
     * @var string
     */
    const SUPPORT_EMAIL = 'support@faktur.pro';

    /**
     * The title to be shown for all default error cases.
     *
     * @var string
     */
    const DEFAULT_TITLE = 'Unbekannter Fehler';

    /**
     * The message to be shown for all default error cases.
     *
     * @var string
     */
    const DEFAULT_MESSAGE = 'Es ist zu einem Fehler mit unbekannter Ursache gekommen. Bitte wende dich an den Support.';

    /**
     * The plugin instance via dependency injection.
     *
     * @var FP_Plugin
     */
    private $_plugin;

    /**
     * Create a new instance of this error handler.
     *
     * @param  FP_Plugin $plugin
     * @return void
     */
    public function __construct( FP_Plugin $plugin )
    {
        $this->_plugin = $plugin;
    }

    /**
     * Decide if the debugging mode is enabled.
     *
     * @return bool
     */
    private function debug()
    {
        return $this->_plugin->is_debugging_enabled();
    }

    /**
     * Call the default WordPress error handler and exit.
     *
     * @param  string $error
     * @return void
     */
    private function call_handler( $error )
    {
        $args  = array( 'response' => __( self::ERROR_TITLE, 'fakturpro' ) );
        $function = apply_filters( 'wp_die_handler', '_default_wp_die_handler' );
        call_user_func( $function, $error, self::ERROR_TITLE, $args );
    }

    /**
     * Exit with a proper error message for the user.
     *
     * @param  string $title
     * @param  string $message
     * @param  bool   $support
     * @return void
     */
    public function exit_error( $title, $message, $support = true )
    {
        $error = array();
        $error[] = self::render_title( $title );
        $error[] = self::render_message( $message );
        $error[] = self::render_website( self::WEBSITE_LINK );
        if ( $support ) {
            $error[] = self::render_support( self::SUPPORT_EMAIL );
        }
        $result = implode( PHP_EOL, $error );
        $this->call_handler( $result );
    }

    /**
     * Exit with a default error message.
     *
     * @return void
     */
    public function exit_default()
    {
        $error = array();
        $error[] = self::render_title( self::DEFAULT_TITLE );
        $error[] = self::render_message( self::DEFAULT_MESSAGE );
        $error[] = self::render_website( self::WEBSITE_LINK );
        $error[] = self::render_support( self::SUPPORT_EMAIL );
        $result = implode( PHP_EOL, $error );
        $this->call_handler( $result );
    }

    /**
     * Exit with a debugging message.
     *
     * @param  Exception $exception
     * @return void
     */
    public function exit_debug( $exception )
    {
        $body = json_decode( $exception->getMessage() );
        $result = print_r( $body, true );
        wp_die( get_class( $exception ).' '.$exception->getCode().' - '.$result );
    }

    /**
     * Render the title for the error to be displayed.
     *
     * @param  string $title
     * @return string
     */
    private static function render_title( $title )
    {
        return "<h1>Fehler: {$title}</h1>";
    }

    /**
     * Render the message for the error to be displayed.
     *
     * @param  string $message
     * @param  bool   $html
     * @return string
     */
    private static function render_message( $message, $html = false )
    {
        return $html ? $message : "<p>{$message}</p>";
    }

    /**
     * Render the website link for the error to be displayed.
     *
     * @param  string $link
     * @return string
     */
    private static function render_website( $link )
    {
        return "<b>Website:</b>&nbsp;<a target='_BLANK' href='{$link}'>{$link}</a><br/>";
    }

    /**
     * Render the support email link for the error to be displayed.
     *
     * @param  string $email
     * @return string
     */
    private static function render_support( $email )
    {
        return "<b>Support:</b>&nbsp;<a href='mailto:{$email}'>{$email}</a><br/>";
    }

    /**
     * Handle an exception and exit with an error message.
     *
     * @param  Exception|FP_Abstract_Error $exception
     * @return void
     */
    public function handle( $exception )
    {
        if ( $this->debug() ) {
            $this->exit_debug( $exception );
        } else if ( is_callable( array( $exception, 'render_error' ) ) ) {
            $result = $exception->render_error();
            $this->exit_error( $result['title'], $result['message'] );
        } else {
            $this->exit_default();
        }
    }

}

endif;
