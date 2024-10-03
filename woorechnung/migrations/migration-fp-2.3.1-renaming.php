<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Migration_2_3_1_Renaming' ) ):

final class FP_Migration_2_3_1_Renaming
{
    /**
     * The plugin update object
     *
     * @var FP_Plugin_Update
     */
    private $update;

    /**
     * @var string
     */
    private $old_prefix = 'woorechnung_';

    /**
     * @var string
     */
    private $new_prefix = 'fakturpro_';

    /**
     * @var array<string>
     */
    private $plugin_setting_names = array(
        // 'version',
        'updated_2_0_0',
        'shop_url',
        'shop_token',
        'send_error_mails_to',
        'create_invoices',
        'invoice_for_methods',
        'no_invoice_for_methods',
        'paid_for_methods',
        'zero_value_invoices',
        'cancel_invoices',
        'open_invoices',
        'customer_link',
        'line_description',
        'article_name_shipping',
        'article_numer_shipping',
        'order_number_prefix',
        'order_number_suffix',
        'email_filename',
        'invoice_email',
        'email_to_append_to',
        'email_for_states',
        'no_email_for_methods',
        'email_subject',
        'email_copy',
        'email_content_text',
        'email_content_html',
    );

    /**
     * @var array<string>
     */
    private $order_setting_names = array(
        'uuid',
        'number',
        'email',
        'invoice_date',
        'invoice_appended_to_email',
        'invoice_canceled',
    );

    /**
     * Create a new instance of this migration.
     *
     * @param  FP_Plugin_Update $update
     * @return void
     */
    public function __construct(FP_Plugin_Update $update)
    {
        $this->update = $update;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->update_plugin_settings();
        $this->update_order_settings();
    }

    /**
     * Update the plugin settings.
     *
     * @return void
     */
    public function update_plugin_settings()
    {
        foreach ($this->plugin_setting_names as $name) {
            $value = get_option( $this->old_prefix . $name, null );
            add_option( $this->new_prefix . $name, $value );
            delete_option( $this->old_prefix . $name );
        }
    }

    /**
     * Update the order settings.
     *
     * @return void
     */
    public function update_order_settings()
    {
        global $wpdb;

        foreach ($this->order_setting_names as $name) {
            $wpdb->update(
                $wpdb->postmeta,
                array( 'meta_key' => $this->new_prefix . $name ),
                array( 'meta_key' => $this->old_prefix . $name )
            );
        }
    }
}

endif;
