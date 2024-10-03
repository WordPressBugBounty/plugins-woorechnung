<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( !empty( $plugin_path ) ) {
    require_once ( $plugin_path . 'includes/abstract/abstract-fp-error.php' );
    require_once ( $plugin_path . 'includes/abstract/abstract-fp-module.php' );
    require_once ( $plugin_path . 'includes/abstract/abstract-fp-plugin.php' );
    require_once ( $plugin_path . 'includes/abstract/abstract-fp-logger.php' );
    require_once ( $plugin_path . 'includes/abstract/abstract-fp-client.php' );
    require_once ( $plugin_path . 'includes/abstract/abstract-fp-settings.php' );
    
    require_once ( $plugin_path . 'includes/class-fp-plugin.php' );
    
    require_once ( $plugin_path . 'includes/common/class-fp-loader.php' );
    require_once ( $plugin_path . 'includes/common/class-fp-logger.php' );
    require_once ( $plugin_path . 'includes/common/class-fp-client.php' );
    require_once ( $plugin_path . 'includes/common/class-fp-factory.php' );
    require_once ( $plugin_path . 'includes/common/class-fp-session.php' );
    require_once ( $plugin_path . 'includes/common/class-fp-settings.php' );
    require_once ( $plugin_path . 'includes/common/class-fp-mailer.php' );
    require_once ( $plugin_path . 'includes/common/class-fp-storage.php' );
    require_once ( $plugin_path . 'includes/common/class-fp-handler.php' );
    require_once ( $plugin_path . 'includes/common/class-fp-verbosity.php' );
    require_once ( $plugin_path . 'includes/common/class-fp-viewer.php' );
    
    require_once ( $plugin_path . 'includes/errors/class-fp-client-error.php' );
    require_once ( $plugin_path . 'includes/errors/class-fp-factory-error.php' );
    require_once ( $plugin_path . 'includes/errors/class-fp-export-error.php' );
    require_once ( $plugin_path . 'includes/errors/class-fp-server-error.php' );
    require_once ( $plugin_path . 'includes/errors/class-fp-storage-error.php' );
    
    require_once ( $plugin_path . 'includes/admin/class-fp-plugin-update.php' );
    require_once ( $plugin_path . 'includes/admin/class-fp-admin-assets.php' );
    require_once ( $plugin_path . 'includes/admin/class-fp-admin-settings.php' );
    require_once ( $plugin_path . 'includes/admin/class-fp-admin-notices.php' );
    require_once ( $plugin_path . 'includes/admin/class-fp-bulk-actions.php' );
    require_once ( $plugin_path . 'includes/admin/class-fp-order-action.php' );
    
    require_once ( $plugin_path . 'includes/class-fp-customer-assets.php' );
    require_once ( $plugin_path . 'includes/class-fp-customer-link.php' );
    require_once ( $plugin_path . 'includes/class-fp-email-handler.php' );
    require_once ( $plugin_path . 'includes/class-fp-order-handler.php' );
    
    require_once ( $plugin_path . 'includes/support/class-fp-order-adapter.php' );
    require_once ( $plugin_path . 'includes/support/class-fp-product-adapter.php' );
}
