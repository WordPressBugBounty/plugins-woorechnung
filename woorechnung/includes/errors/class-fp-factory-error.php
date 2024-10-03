<?php

// Exit if accessed directly
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! class_exists( 'FP_Factory_Error' ) ):

/**
 * Faktur Pro Factory Error Class
 *
 * @version  1.0.0
 * @package  FakturPro\Errors
 * @author   Zweischneider
 */
final class FP_Factory_Error extends FP_Abstract_Error
{
    /**
     * @var int WRONG_ORDER_ITEM_ERROR
     */
    const WRONG_ORDER_ITEM_ERROR = 0;

    /**
     * @var int ORDER_ITEM_MISSING_PRODUCT_ERROR
     */
    const ORDER_ITEM_MISSING_PRODUCT_ERROR = 1;

    /**
     * Create a new instance of this error.
     *
     * @param  string $data
     * @param  int    $code
     */
    public function __construct( $data, $code )
    {
        parent::__construct( $data, $code );
    }

    /**
     * Render this error to a printable title and message.
     *
     * @return array<string, string>
     */
    public function render_error()
    {
        switch ( $this->getCode() )
        {
            case self::WRONG_ORDER_ITEM_ERROR: return $this->error_wrong_order_item();
            case self::ORDER_ITEM_MISSING_PRODUCT_ERROR: return $this->error_order_item_missing_product();
            default: return $this->error_unknown_reasons();
        }
    }

    /**
     * Render an error for an missing product of an order item.
     *
     * @return array<string, string>
     */
    public function error_order_item_missing_product()
    {
        $title = __( 'Product for order item does not exist', 'fakturpro' );
        $message = __( 'The order contains a line item for which the product does not exist (anymore). It is not possible to create invoices that contain items whose product has already been deleted.', 'fakturpro' );
        return array( 'title' => $title, 'message' => $message );
    }

    /**
     * Render an error for an wrong order item.
     *
     * @return array<string, string>
     */
    public function error_wrong_order_item()
    {
        $title = __( 'Order item is not a product', 'fakturpro' );
        $message = __( 'The order contains an item that is not a product. Please check the order and contact support.', 'fakturpro' );
        return array( 'title' => $title, 'message' => $message );
    }

    /**
     * Render an error with an unknown reason.
     *
     * @return array<string, string>
     */
    public function error_unknown_reasons()
    {
        $title = __( 'Cause unclear', 'fakturpro' );
        $message = __( 'An unknown error has occurred. Please contact support if the problem persists.', 'fakturpro' );
        return array( 'title' => $title, 'message' => $message );
    }
}

endif;
