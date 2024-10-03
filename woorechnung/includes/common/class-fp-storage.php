<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'FP_Storage' ) ):

/**
 * Faktur Pro Storage class.
 *
 * This class provides an interface to a persistent data storage.
 * In order to send invoices as email attachments, the invoice PDF has
 * to be stored in the local filesystem. When to mail is actually sent,
 * the PDF file is being loaded from storage again.
 *
 * @class    FP_Storage
 * @version  1.0.0
 * @package  FakturPro
 * @author   Zweischneider
 */
final class FP_Storage
{
    /**
     * The plugin instance via dependency injection.
     *
     * @var FP_Plugin $_plugin
     */
    protected $_plugin;

    /**
     * Create a new instance of this storage class.
     *
     * @param  FP_Plugin $plugin
     */
    public function __construct( FP_Plugin $plugin )
    {
        $this->_plugin = $plugin;
    }

    /**
     * Store a file given the full path and data.
     *
     * @param  string $path
     * @param  string $data
     * @throws FP_Storage_Error
     * @return void
     */
    public function store_file( $path, $data )
    {
        $result = @file_put_contents( $path, $data );

        if ($result === false) {
            $code = FP_Storage_Error::WRITE_FILE_ERROR;
            throw new FP_Storage_Error( $path, $code );
        }
    }

    /**
     * Load file data from a given path.
     *
     * @param  string $path
     * @throws FP_Storage_Error
     * @return string
     */
    public function load_file( $path )
    {
        $result = @file_get_contents( $path );

        if ( $result === false ) {
            $code = FP_Storage_Error::READ_FILE_ERROR;
            throw new FP_Storage_Error( $path, $code );
        }

        return $result;
    }

    /**
     * Create a new directory from a given path.
     *
     * @param  string $path
     * @param  int $permissions
     * @throws FP_Storage_Error
     * @return void
     */
    public function create_directory( $path, $permissions = 0777 )
    {
        $result = @mkdir( $path, $permissions, true );

        if ( $result === false ) {
            $code = FP_Storage_Error::WRITE_DIR_ERROR;
            throw new FP_Storage_Error( $path, $code );
        }
    }
}

endif;
