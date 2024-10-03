<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Migration_3_0_5_Renaming' ) ):

final class FP_Migration_3_0_5_Renaming
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
        $this->update_order_settings();
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
