<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Plugin_Update' ) ):

/**
 * Faktur Pro Plugin Update Module
 *
 * @class    FP_Plugin_Update
 * @version  1.0.0
 * @package  FakturPro
 * @author   Zweischneider
 */
final class FP_Plugin_Update extends FP_Abstract_Module
{
    /**
     * Initialize all hooks for this module.
     *
     * @return void
     */
    public function init_hooks()
    {
        $this->add_action('admin_init', 'migration_check');
    }

    /**
     * Checks if a migration is needed and migrates.
     *
     * @return void
     */
    public function migration_check()
    {
        $new_version = $this->plugin()->get_version();
        $old_version = get_option('woorechnung_version', false);
        if ($old_version == false) {
            $old_version = get_option('fakturpro_version', '1.0.0');
        } else {
            add_option('fakturpro_version', $old_version);
            delete_option('woorechnung_version');
        }
        if (version_compare($old_version, $new_version, '<')) {
            $this->migrate($old_version);
            if (version_compare(get_option('fakturpro_version', '1.0.0'), $new_version, '<')) {
                update_option('fakturpro_version', $new_version);
            }
        }
    }

    /**
     * Checks old versions and migrates to next version.
     *
     * @param  string $old_version
     * @return void
     */
    private function migrate( $old_version )
    {
        $migration_versions = array(
            '2.0.0' => ['refactoring'],
            '2.1.0' => ['line description option'],
            '2.3.1' => ['renaming'],
            '3.0.5' => ['renaming'],
            '3.0.6' => ['renaming'],
        );
        foreach ($migration_versions as $migration_version => $migration_names) {
            // Check that the migration (version) has not already been applied
            if (version_compare($old_version, $migration_version, '<')) {
                foreach ($migration_names as $migration_name) {
                    // Load migration file
                    $filename = 'migration-fp-'.$migration_version.'-';
                    $filename .= str_replace(' ', '-', $migration_name).'.php';
                    require_once($this->plugin()->get_path('migrations').'/'.$filename);

                    // Create migration object and call up
                    $class = 'FP_Migration_';
                    $class .= str_replace('.', '_', $migration_version).'_';
                    $class .= str_replace(' ', '_', ucwords($migration_name));
                    if (class_exists($class)) {
                        $migration = new $class($this);
                        $migration->up();
                    }
                }
                // Update to migrated version
                update_option('fakturpro_version', $migration_version);
            }
        }
    }
}

endif;
