<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Abstract_Logger' ) ):

/**
 * Faktur Pro Abstract Logger Class
 *
 * @class    FP_Abstract_Logger
 * @version  1.0.0
 * @package  FakturPro\Abstract
 * @author   Zweischneider
 */
abstract class FP_Abstract_Logger
{
    /**
     * The emergency log level string.
     *
     * @var string
     */
    const LEVEL_EMERGENCY = 'emergency';

    /**
     * The alert log level string.
     *
     * @var string
     */
    const LEVEL_ALERT = 'alert';

    /**
     * The critical log level string.
     *
     * @var string
     */
    const LEVEL_CRITICAL = 'critical';

    /**
     * The error log level string.
     *
     * @var string
     */
    const LEVEL_ERROR = 'error';

    /**
     * The warning log level string.
     *
     * @var string
     */
    const LEVEL_WARNING = 'warning';

    /**
     * The notice log level string.
     *
     * @var string
     */
    const LEVEL_NOTICE = 'notice';

    /**
     * The info log level string.
     *
     * @var string
     */
    const LEVEL_INFO = 'info';

    /**
     * The debug log level string.
     *
     * @var string
     */
    const LEVEL_DEBUG = 'debug';

    /**
     * The date format to use when logging events.
     *
     * @var string
     */
    const DATE_FORMAT = "Y-m-d H:i:sO";

    /**
     * The plugin instance via dependency injection.
     *
     * @var FP_Plugin
     */
    private $_plugin;

    /**
     * Create a new logger instance.
     *
     * @param  FP_Plugin $plugin
     * @return void
     */
    public function __construct(FP_Plugin $plugin)
    {
        $this->_plugin = $plugin;
        $this->init_log();
    }

    /**
     * Initialize the log by creating an empty log file.
     *
     * @return void
     */
    public function init_log()
    {
        // Create temporary folder if it does not exist
        if (!is_dir($this->_plugin->get_temp_path())) {
            @mkdir($this->_plugin->get_temp_path(), 0777, true);
        }

        // Create the log file if it does not exist
        if (!file_exists($this->get_filepath())) {
            @touch($this->get_filepath());
        }
    }

    /**
     * Decide if logging is enabled in configuration file.
     *
     * @return bool
     */
    private function is_enabled()
    {
        return $this->_plugin->is_logging_enabled();
    }

    /**
     * Decide if verbose logging is enabled in configuration file.
     *
     * @return bool
     */
    private function is_verbose()
    {
        return $this->_plugin->is_logging_verbose();
    }

    /**
     * Get the name of the file to write the log to.
     *
     * @return string
     */
    private function get_filename()
    {
        return $this->_plugin->get_logging_filename();
    }

    /**
     * Get the full path of the file to write the log to.
     *
     * @return string
     */
    private function get_filepath()
    {
        return $this->_plugin->get_temp_path( $this->get_filename() );
    }


    /**
     * Decide if the log file exists and is readable.
     *
     * @return bool
     */
    private function is_file_readable()
    {
        return is_readable( $this->get_filepath() );
    }

    /**
     * Read log contents from file.
     *
     * @return string
     */
    private function read_contents()
    {
        return file_get_contents( $this->get_filepath() );
    }

    /**
     * Get contents from the log file if possible.
     *
     * @return string|null
     */
    public function load()
    {
        return $this->is_file_readable() ? $this->read_contents() : null;
    }

    /**
     * Determine if the log file exists and is writable.
     *
     * @return bool
     */
    private function is_file_writable()
    {
        return is_writable( $this->get_filepath() );
    }

    /**
     * Write log contents to file.
     *
     * @param  string $contents
     * @return void
     */
    private function write_contents($contents)
    {
        file_put_contents( $this->get_filepath(), $contents, FILE_APPEND );
    }

    /**
     * Get the current datetime in the selected format.
     *
     * @return string
     */
    private static function get_datetime()
    {
        return date( self::DATE_FORMAT );
    }

    /**
     * Render the datetime value as to appear in the written log.
     *
     * @return string
     */
    private static function render_datetime()
    {
        return '[' . self::get_datetime() . ']';
    }

    /**
     * Render the version as to appear in the written log.
     *
     * @param  string $version
     * @return string
     */
    private static function render_version( $version )
    {
        return '[version ' . $version . '] ';
    }

    /**
     * Render the level as to appear in the written log.
     *
     * @param  string $level
     * @return string
     */
    private static function render_level($level)
    {
        return strtoupper( $level ) . ': ';
    }

    /**
     * Render a log entry from level, message and context.
     *
     * @param  string $level
     * @param  string $message
     * @return string
     */
    private function render($level, $message)
    {
        $partials = array();
        $partials[] = self::render_datetime();
        $partials[] = self::render_version($this->_plugin->get_version());
        $partials[] = self::render_level($level);
        $partials[] = $message;
        $partials[] = PHP_EOL;
        $result = implode('', $partials);
        return $result;
    }

    /**
     * Write to the log if logging is enabled and the file is writable.
     *
     * @param  string $level
     * @param  string $message
     * @return void
     */
    public function log($level, $message)
    {
        if ( $this->is_enabled() && $this->is_file_writable() ) {
            $contents = $this->render($level, $message);
            $this->write_contents($contents);
        }
    }

    /**
     * Log an emergency error which makes the plugin unusable.
     *
     * @param  string $message
     * @return void
     */
    public function emergency( $message )
    {
        $this->log( self::LEVEL_EMERGENCY, $message );
    }

    /**
     * Log an emergency error which makes the plugin unusable.
     *
     * @param  string $message
     * @return void
     */
    public function alert( $message )
    {
        $this->log( self::LEVEL_ALERT, $message );
    }

    /**
     * Log a critical error occurred during processing.
     *
     * @param  string $message
     * @return void
     */

    public function critical( $message )
    {
        $this->log( self::LEVEL_CRITICAL, $message );
    }

    /**
     * Log a runtime error which is not critical.
     *
     * @param  string $message
     * @return void
     */
    public function error( $message )
    {
        $this->log( self::LEVEL_ERROR, $message );
    }

    /**
     * Log an exceptional occurrence that is not an error.
     *
     * @param  string $message
     * @return void
     */
    public function warning( $message )
    {
        $this->log( self::LEVEL_WARNING, $message );
    }

    /**
     * Log a normal but significant event.
     *
     * @param  string $message
     * @return void
     */
    public function notice( $message )
    {
        $this->log( self::LEVEL_NOTICE, $message );
    }

    /**
     * Log an interesting event that happend during processing.
     *
     * @param  string $message
     * @return void
     */
    public function info( $message )
    {
        $this->log( self::LEVEL_INFO, $message );
    }

    /**
     * Log information for debugging purposes.
     *
     * @param  string $message
     * @return void
     */
    public function debug( $message )
    {
        $this->log( self::LEVEL_DEBUG, $message );
    }

    /**
     * Log an interesting event that happend during processing if verbose logging is active.
     *
     * @param  string $message
     * @param  mixed $data
     * @return void
     */
    public function verbose( $message, $data = null )
    {
        if ( $this->is_verbose() ) {
            $backtrace = FP_Plugin::get_caller_info( 2 );
            $class = isset( $backtrace['class'] ) ? $backtrace['class'] : null;
            $verbosity = new FP_Verbosity();
            $message = $verbosity->get_verbosity_string( $message, $class, $backtrace['function'], $data );
            $this->log( self::LEVEL_DEBUG, $message );
        }
    }

    /**
     * Capture a given exception.
     *
     * @param  \Exception $exception
     * @return void
     */
    public function capture( $exception )
    {
        $this->error( $exception->getMessage() );
    }
}

endif;
