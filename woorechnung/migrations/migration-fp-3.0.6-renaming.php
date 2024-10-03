<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Migration_3_0_6_Renaming' ) ):

final class FP_Migration_3_0_6_Renaming
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
        'invoice_for_states',
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
}

endif;
