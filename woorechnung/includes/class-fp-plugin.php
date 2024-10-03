<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Plugin' ) ):

/**
 * Faktur Pro Plugin Class
 *
 * @version  2.0.0
 * @package  FakturPro
 * @author   Zweischneider
 */
final class FP_Plugin extends FP_Abstract_Plugin
{
    /**
     * The name of this class since self::class is not supported.
     *
     * @var string
     */
    const PLUGIN_CLASS = 'FP_Plugin';

    /**
     * The name of the this plugin.
     *
     * @var string
     */
    const PLUGIN_NAME = 'Faktur Pro';

    /**
     * The text domain of this plugin.
     *
     * @var string
     */
    const TEXT_DOMAIN = 'fakturpro';

    /**
     * The settings prefix for this plugin.
     *
     * @var string
     */
    const SETTINGS_PREFIX = 'fakturpro_';

    /**
     * The user agent name for this plugin.
     *
     * @var string
     */
    const USER_AGENT = 'fakturpro-wc-plugin';

    /**
     * String representing development environment.
     *
     * @var string
     */
     const ENV_DEVELOPMENT = 'development';

    /**
     * String representing production environment;
     *
     * @var string
     */
    const ENV_PRODUCTION = 'production';

    /**
     * The path of the WooCommerce plugin.
     *
     * @var string
     */
    const PLUGIN_WOOCOMMERCE = 'woocommerce/woocommerce.php';

    /**
     * The path of the WooCommerce Subscriptions plugin.
     *
     * @var string
     */
    const PLUGIN_WOOCOMMERCE_SUBSCRIPTIONS = 'woocommerce-subscriptions/woocommerce-subscriptions.php';

    /**
     * The path of the WooCommerce Germanized plugin.
     *
     * @var string
     */
    const PLUGIN_GERMANIZED = 'woocommerce-germanized/woocommerce-germanized.php';

    /**
     * The path of the Secondary Title plugin.
     *
     * @var string
     */
    const PLUGIN_SECONDARY_TITLE = 'secondary-title/secondary-title.php';

    
    /**
     * The path of the Secondary Title plugin.
     *
     * @var string
     */
    const PLUGIN_WC_PRODUCT_SUBTITLE = 'wc-product-subtitle/wc-product-subtitle.php';

    /**
     * The hook loader instance to register any WordPress hooks.
     *
     * @var FP_Loader
     */
    private $_loader;

    /**
     * The logger instance to log plugin events.
     *
     * @var FP_Logger
     */
    private $_logger;

    /**
     * The session instance to store session data.
     *
     * @var FP_Session
     */
    private $_session;

    /**
     * The API client to use for making requests to the API.
     *
     * @var FP_Client
     */
    private $_client;

    /**
     * The invoice factory instance to create invoices.
     *
     * @var FP_Factory
     */
    private $_factory;

    /**
     * The mailer instance to send emails.
     *
     * @var FP_Mailer
     */
    private $_mailer;

    /**
     * The plugin settings instance.
     *
     * @var FP_Settings
     */
    private $_settings;

    /*
     * The plugin settings instance.
     *
     * @var FP_Options
     */
    // private $_options;

    /**
     * The plugin storage instance.
     *
     * @var FP_Storage
     */
    private $_storage;

    /**
     * The plugin PDF viewer instance.
     *
     * @var FP_Viewer
     */
    private $_viewer;

    /**
     * The plugin error handler instance.
     *
     * @var FP_Handler
     */
    private $_handler;

    /**
     * Create a new instance of the plugin.
     *
     * @param  string $plugin_file
     * @param  string $plugin_path
     * @param  string $plugin_url
     * @return void
     */
    public function __construct($plugin_file, $plugin_path, $plugin_url)
    {
        $this->set_file($plugin_file);
        $this->set_path($plugin_path);
        $this->set_url($plugin_url);
        $this->load_commons();
        $this->load_modules();
    }

    /**
     * Get the name of this plugin.
     *
     * @return string
     */
    public function get_name()
    {
        return self::PLUGIN_NAME;
    }

    /**
     * Get the current version of this plugin.
     *
     * @return string
     */
    public function get_version()
    {
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH.'wp-admin/includes/plugin.php');
        }
        $plugin_data = get_plugin_data( $this->get_file() );
        return !empty($plugin_data['Version']) ? $plugin_data['Version'] : '';
    }

    /**
     * Get the settings prefox string of this plugin.
     *
     * @return string
     */
    public function get_settings_prefix()
    {
        return self::SETTINGS_PREFIX;
    }

    /**
     * Get the user agent for this plugin.
     *
     * @return string
     */
    public function get_user_agent()
    {
        $version = $this->get_version();
        return self::USER_AGENT.(!empty($version) ? '/'.$version : '');
    }

    /**
     * Get the text domain of this plugin.
     *
     * @return string
     */
    public function get_text_domain()
    {
        return self::TEXT_DOMAIN;
    }

    /**
     * Get the URI of the server application.
     *
     * @return string
     */
    public function get_server_uri()
    {
        return FAKTURPRO_SERVER_URI;
    }

    /**
     * Get the email address of the support.
     *
     * @return string
     */
    public function get_support_email()
    {
        return FAKTURPRO_SUPPORT_EMAIL;
    }

    /**
     * Get the current environment.
     *
     * Possible values are 'development' and 'production'.
     *
     * @return string
     */
    public function get_environment()
    {
        return FAKTURPRO_ENVIRONMENT;
    }

    /**
     * Decide if a particular environment is present.
     *
     * @param  string $environment
     * @return bool
     */
    public function has_environment( $environment )
    {
        return $this->get_environment() == $environment;
    }

    /**
     * Check if the plugin is run in a development environment.
     *
     * @return bool
     */
    public function is_env_development()
    {
        return $this->has_environment(self::ENV_DEVELOPMENT);
    }

    /**
     * Check if the plugin is run in a production environment.
     *
     * @return bool
     */
    public function is_env_production()
    {
        return $this->has_environment(self::ENV_PRODUCTION);
    }

    /**
     * Decide if debugging mode is enabled by configuration.
     *
     * @return bool
     */
    public function is_debugging_enabled()
    {
        return FAKTURPRO_DEBUGGING_ENABLED;
    }

    /**
     * Decide if logging is enabled by configuration.
     *
     * @return bool
     */
    public function is_logging_enabled()
    {
        return FAKTURPRO_LOGGING_ENABLED;
    }

    /**
     * Decide if verbose logging is enabled by configuration.
     *
     * @return bool
     */
    public function is_logging_verbose()
    {
        if (!defined('FAKTURPRO_LOGGING_VERBOSE')) {
            return false;
        }
        return FAKTURPRO_LOGGING_VERBOSE;
    }

    /**
     * Decide if now is in range.
     * 
     * @param  \DateTime|string|null $from
     * @param  \DateTime|string|null $to
     * @param  \DateTime|string|null $check
     * @return bool
     */
    public function is_in_time( $from, $to, $check = null )
    {
        if (empty($check)) {
            $check = new \DateTime('now');
        }
        if (is_string($check)) {
            $check = new \DateTime($check);
        }
        if (!empty($from) && is_string($from)) {
            $from = new \DateTime($from);
        }
        if (!empty($to) && is_string($to)) {
            $to = new \DateTime($to);
        }
        if (!empty($from)) {
            if ($check < $from) {
                return false;
            }
        }
        if (!empty($to)) {
            if ($check > $to) {
                return false;
            }
        }
        return true;
    }

    /**
     * Return the name of the log file from configuration.
     *
     * @return string
     */
    public function get_logging_filename()
    {
        return FAKTURPRO_LOGGING_FILENAME;
    }

    /**
     * Array map assoc.
     * 
     * @param  callable $callback
     * @param  array<string, mixed> $array
     * @return array<string, mixed>
     */
    public function array_map_assoc( $callback, $array)
    {
        $array = array_map( $callback, array_keys( $array ), $array );
        return array_column( $array, 1, 0 );
    }

    /**
     * Array map placeholders.
     * 
     * @param  array<string, mixed> $placeholders
     * @param  bool $brackets
     * @return array<string, mixed>
     */
    public function array_map_placeholders( $placeholders, $brackets = true )
    {
        return $this->array_map_assoc(
            function ( $key, $value ) use ( $brackets ) {
                return [ $brackets ? '{' . $key . '}' : '%' . $key . '%', $value ];
            },
            $placeholders
        );
    }

    /**
     * Return the variable names and descriptions for the invoice filename.
     *
     * @param  bool $brackets
     * @return array<string, string>
     */
    public function get_invoice_filename_variables( $brackets = true )
    {
        return $this->array_map_placeholders(
            array(
                'order_id' => __('Order ID', 'fakturpro'),
                'order_no' => __('Order number', 'fakturpro'),
                'invoice_no' => __('Invoice number', 'fakturpro'),
                'invoice_date' => __('Invoice date US', 'fakturpro'),
                'invoice_date_de' => __('Invoice date DE', 'fakturpro'),
                'invoice_date_day' => __('Day of invoice date', 'fakturpro'),
                'invoice_date_month' => __('Month of invoice date', 'fakturpro'),
                'invoice_date_year' => __('Year of invoice date', 'fakturpro'),
                'company_or_name' => __('Customer company or last name', 'fakturpro'),
                'company' => __('Customer company name', 'fakturpro'),
                'first_name' => __('Customer first name', 'fakturpro'),
                'last_name' => __('Customer last name', 'fakturpro')
            ),
            $brackets
        );
    }

    /**
     * Return the variable names and descriptions for replacements in texts.
     *
     * @param  bool $brackets
     * @return array<string, string>
     */
    public function get_invoice_placeholder_variables( $brackets = true )
    {
        return array_merge(
            $this->get_invoice_filename_variables( $brackets ),
            $this->array_map_placeholders(
                array(
                    'order_date' => __('Order date US', 'fakturpro'),
                    'order_date_de' => __('Order date DE', 'fakturpro'),
                    'order_date_day' => __('Day of order date', 'fakturpro'),
                    'order_date_month' => __('Month of order date', 'fakturpro'),
                    'order_date_year' => __('Year of order date', 'fakturpro'),
                    'page_title' => __('Page title', 'fakturpro'),
                ),
                $brackets
            )
        );
    }

    /**
     * Return all available order statuses.
     *
     * @param  bool $with_names
     * @return array<string, string>|array<string>
     */
    public function get_order_statuses($with_names = true)
    {
        $statuses = array();
        $wc_statuses = function_exists('wc_get_order_statuses') ? wc_get_order_statuses() : array();
        foreach ($wc_statuses as $index => $value) {
            $statuses[str_replace('wc-', '', $index)] = $value;
        }
        return $with_names ? $statuses : array_keys($statuses);
    }

    /**
     * Get the path for the directory for temporary files.
     *
     * @param  string $append
     * @return string
     */
    public function get_temp_path( $append = '' )
    {
        return $this->get_path("temp/{$append}");
    }

    /**
     * Get the path for the invoice PDF files.
     *
     * @param  string $file
     * @return string
     */
    public function get_invoices_path( $file = '' )
    {
        return $this->get_temp_path("invoices/{$file}");
    }

    /**
     * Get the path for the bulk export files.
     *
     * @param  string $file
     * @return string
     */
    public function get_exports_path( $file = '' )
    {
        return $this->get_temp_path("exports/{$file}");
    }

    /**
     * Get the hook loader instance to register actions and filters.
     *
     * @return FP_Loader
     */
    public function get_loader()
    {
        return $this->_loader;
    }

    /**
     * Get the logger instance to log plugin events.
     *
     * @return FP_Logger
     */
    public function get_logger()
    {
        return $this->_logger;
    }

    /**
     * Get the session instance to store session data.
     *
     * @return FP_Session
     */
    public function get_session()
    {
        return $this->_session;
    }

    /**
     * Get the HTTP client to make external requests.
     *
     * @return FP_Client
     */
     public function get_client()
     {
        return $this->_client;
     }

    /**
     * Get the invoice factory to create invoices.
     *
     * @return FP_Factory
     */
     public function get_factory()
     {
        return $this->_factory;
     }

    /**
     * Get the plugin mailer instance.
     *
     * @return FP_Mailer
     */
    public function get_mailer()
    {
        return $this->_mailer;
    }

    /**
     * Get the plugin settings instance.
     *
     * @return FP_Settings
     */
    public function get_settings()
    {
        return $this->_settings;
    }

    /*
     * Get the plugin options instance.
     *
     * @return FP_Options
     */
    /*public function get_options()
    {
        return $this->_options;
    }*/

    /**
     * Get the plugin storage instance.
     *
     * @return FP_Storage
     */
    public function get_storage()
    {
        return $this->_storage;
    }

    /**
     * Get the plugin viewer instance.
     *
     * @return FP_Viewer
     */
    public function get_viewer()
    {
        return $this->_viewer;
    }

    /**
     * Get the error handler instance.
     *
     * @return FP_Handler
     */
    public function get_handler()
    {
        return $this->_handler;
    }

    /**
     * Run the plugin loading all hooks registered within the loader.
     *
     * @return void
     */
    public function run_plugin()
    {
        $this->get_loader()->load();
    }

    /**
     * Check if the WooCommerce plugin is active.
     *
     * @return bool
     */
    public function is_woocommerce_active()
    {
        return $this->is_plugin_active( self::PLUGIN_WOOCOMMERCE );
    }

    /**
     * Check if the WooCommerce Subscriptions plugin is active.
     *
     * @return bool
     */
    public function is_woocommerce_subscriptions_active()
    {
        return $this->is_plugin_active( self::PLUGIN_WOOCOMMERCE_SUBSCRIPTIONS );
    }

    /**
     * Check if the WooCommerce Germanized plugin is active.
     *
     * @return bool
     */
    public function is_germanized_active()
    {
        return $this->is_plugin_active( self::PLUGIN_GERMANIZED );
    }

    /**
     * Check if the Secondary Title plugin is active.
     *
     * @return bool
     */
    public function is_secondary_title_active()
    {
        return $this->is_plugin_active( self::PLUGIN_SECONDARY_TITLE );
    }

    /**
     * Check if the Product Subtitle for WooCommerce plugin is active.
     *
     * @return bool
     */
    public function is_wc_product_subtitle_active()
    {
        return $this->is_plugin_active( self::PLUGIN_WC_PRODUCT_SUBTITLE );
    }

    /**
     * Load all components that are shared by the mondules.
     *
     * @return void
     */
    private function load_commons()
    {
        $this->_session = new FP_Session($this);
        $this->_loader = new FP_Loader($this);
        $this->_logger = new FP_Logger($this);
        $this->_client = new FP_Client($this);
        $this->_factory = new FP_Factory($this);
        $this->_settings = new FP_Settings($this);
        $this->_mailer = new FP_Mailer($this);
        $this->_storage = new FP_Storage($this);
        $this->_viewer = new FP_Viewer($this);
        $this->_handler = new FP_Handler($this);
    }

    /**
     * Load all modules that constitute this plugin.
     *
     * @return void
     */
    public function load_modules()
    {
        new FP_Plugin_Update($this);
        new FP_Admin_Assets($this);
        new FP_Admin_Notices($this);
        new FP_Admin_Settings($this);
        new FP_Bulk_Actions($this);
        new FP_Customer_Assets($this);
        new FP_Customer_Link($this);
        new FP_Email_Handler($this);
        new FP_Order_Action($this);
        new FP_Order_Handler($this);
    }
}

endif;
