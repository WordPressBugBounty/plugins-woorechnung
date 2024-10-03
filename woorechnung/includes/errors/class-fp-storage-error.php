<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Storage_Error' ) ):

/**
 * Faktur Pro Storage Error Class
 *
 * @version  1.0.0
 * @package  FakturPro\Errors
 * @author   Zweischneider
 */
final class FP_Storage_Error extends FP_Abstract_Error
{
    /**
     * @var int READ_DIR_ERROR
     */
    const READ_DIR_ERROR = 0;

    /**
     * @var int WRITE_DIR_ERROR
     */
    const WRITE_DIR_ERROR = 1;

    /**
     * @var int READ_FILE_ERROR
     */
    const READ_FILE_ERROR = 2;

    /**
     * @var int WRITE_FILE_ERROR
     */
    const WRITE_FILE_ERROR = 3;

    /**
     * Render error.
     * 
     * @return array<string, string>
     */
    public function render_error()
    {
        switch( $this->getCode() )
        {
            case self::READ_DIR_ERROR: return $this->error_read_dir();
            case self::WRITE_DIR_ERROR: return $this->error_write_dir();
            case self::READ_FILE_ERROR: return $this->error_read_file();
            case self::WRITE_FILE_ERROR: return $this->error_write_file();
            default: return $this->error_unknown();
        }
    }

    /**
     * Error read dir.
     * 
     * @return array<string, string>
     */
    private function error_read_dir()
    {
        $title = __( 'Folder read error', 'fakturpro' );
        $message = __( 'The folder could not be read.', 'fakturpro' );
        return array( 'title' => $title, 'message' => $message );
    }

    /**
     * Error write dir.
     * 
     * @return array<string, string>
     */
    private function error_write_dir()
    {
        $title = __( 'Order cannot be written', 'fakturpro' );
        $message = __( 'The folder could not be created.', 'fakturpro' );
        return array( 'title' => $title, 'message' => $message );
    }

    /**
     * Error read file.
     * 
     * @return array<string, string>
     */
    private function error_read_file()
    {
        $title = __( 'File read error', 'fakturpro' );
        $message = __( 'The file could not be read.', 'fakturpro' );
        return array( 'title' => $title, 'message' => $message );
    }

    /**
     * Error write file.
     * 
     * @return array<string, string>
     */
    private function error_write_file()
    {
        $title = __( 'File write error', 'fakturpro' );
        $message = __( 'The file could not be created.', 'fakturpro' );
        return array( 'title' => $title, 'message' => $message );
    }

    /**
     * Error unknown.
     * 
     * @return array<string, string>
     */
    private function error_unknown()
    {
        $title = __( 'Cause unclear', 'fakturpro' );
        $message = __( 'An error with an unknown cause has occurred. Please contact support.', 'fakturpro' );
        return array( 'title' => $title, 'message' => $message );
    }

}

endif;
