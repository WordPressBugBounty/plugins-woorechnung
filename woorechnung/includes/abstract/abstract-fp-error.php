<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Abstract_Error' ) ):

/**
 * Faktur Pro Abstract Error Class
 *
 * @class    FP_Abstract_Error
 * @version  1.0.0
 * @package  FakturPro\Abstract
 * @author   Zweischneider
 */
abstract class FP_Abstract_Error extends Exception
{
    /**
     * Render error.
     * 
     * @return array<string, string>
     */
    abstract public function render_error();
}

endif;
