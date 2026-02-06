<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'FP_Product_Adapter' ) ):

/**
 * Faktur Pro Product Adapter Class
 *
 * This adapter is used to conveniently operate on the
 * WooCommerce WC_Product class within the plugin.
 *
 * @class    FP_Product_Adapter
 * @version  1.0.0
 * @package  FakturPro\Support
 * @author   Zweischneider
 */
final class FP_Product_Adapter
{
    /**
     * The product instance to operate on.
     *
     * @var WC_Product|null
     */
    private $product;

    /**
     * Create a new instance of the product adapter.
     *
     * @param  int|WC_Product|WC_Order_Item $item
     * @param  bool $strict
     * @return void
     */
    public function __construct( $item, $strict = false )
    {
        $this->load_product( $item, $strict );
    }

    /**
     * Get the product id from a request.
     *
     * @param  array<string, mixed>|null $params
     * @return int
     */
    public static function get_request_id( $params = null )
    {
        if ( !isset( $params ) ) {
            $params = $_REQUEST;
        }

        // Check for 'id', 'product_id' and 'post' parameters if there was a redirect.
        $product_id = null;
        $product_id = isset( $params['id'] ) ? $params['id'] : $product_id;
        $product_id = isset( $params['post'] ) ? $params['post'] : $product_id; // woocommerce legacy mode
        $product_id = isset( $params['product_id'] ) ? $params['product_id'] : $product_id;

        return isset( $product_id ) ? absint( $product_id ) : null;
    }

    /**
     * Make all product methods directly accessible.
     *
     * @param  string $method
     * @param  mixed  $args
     * @return mixed
     */
    public function __call( $method, $args )
    {
        if (empty($this->product)) {
            return null;
        }

        if (empty($args)) {
            /*return is_callable( array( $this->product, $method ) )
                ? $this->product->{$method}()
                : null;*/
            return call_user_func( array( $this->product, $method ) );
        }

        /*return is_callable( array($this->product, $method ) )
            ? $this->product->{$method}($args)
            : null;*/
        return call_user_func_array( array( $this->product, $method ), $args );
    }

    /**
     * Load the actual product object using the ID.
     *
     * @param  int|WC_Product|WC_OrderItem $item
     * @param  bool $strict
     * @return void
     */
    private function load_product( $item, $strict = false )
    {
        if ($item instanceof WC_Product) {
            $this->product = $item;
            return;
        }
        if ( $strict && function_exists( 'wc_get_product' ) ) {
            $product_id = is_int( $item ) ? $item : ( is_callable( array( $item, 'get_product_id' ) ) ? $item->get_product_id() : 0 );
            if ($product_id > 0) {
                $this->product = wc_get_product( $product_id );
                apply_filters( 'woocommerce_order_item_product', $this->product, $item );
            }
            return;
        }
        $this->product = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : null;
    }

    /**
     * Return the product object this class operates on.
     *
     * @return WC_Product|null
     */
    public function get_product()
    {
        return $this->product;
    }

    /**
     * Get the alternative product title.
     * 
     * @return string|null
     */
    public function get_alternate_title()
    {
        $meta_fields = array(
            // General meta keys.
            '_alternate_title',
            'alternate_title',
            '_alt_title',
            'alt_title',
            '_subtitle',
            'subtitle',
            '_alternate_name',
            'alternate_name',
            '_alt_name',
            'alt_name',
            '_secondary_name',
            'secondary_name',

            // Meta keys from plugins.
            'wc_ps_subtitle', // Plugin "Product Subtitle For WooCommerce"
            '_secondary_title', // Plugin "Secondary Title"
            'secondary_title',
        );

        // Search for alternate title in order meta
        foreach ($meta_fields as $meta_field) {
            $alternate_title = $this->product->get_meta( $meta_field, true, 'edit' );
            if (!empty($alternate_title)) {
                return $alternate_title;
            }
        }

        return null;
    }
}

endif;
