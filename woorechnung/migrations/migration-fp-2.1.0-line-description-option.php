<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Migration_2_1_0_Line_Description_Option' ) ):

final class FP_Migration_2_1_0_Line_Description_Option
{
    /**
     * The plugin update object
     *
     * @var FP_Plugin_Update
     */
    private $update;

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
        if (get_option('woorechnung_line_description', '') == 'variation') {
            update_option('woorechnung_line_description', 'article');
        }
    }
}

endif;
