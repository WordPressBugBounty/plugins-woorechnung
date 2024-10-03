<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'FP_Factory') ):

/**
 * Faktur Pro Invoice Factory Component.
 *
 * This class provides a factory to create invoices from WooCommerce
 * orders. In the factoring process, the order properties are mapped
 * onto the invoice properties that the Faktur Pro API expects.
 *
 * @version  1.0.1
 * @package  FakturPro\Common
 * @author   Zweischneider
 */
final class FP_Factory
{
    /**
     * The plugin instance via dependency injection.
     *
     * @var FP_Plugin
     */
    private $_plugin;

    /**
     * Create a new instance of this invoice factory.
     *
     * @param  FP_Plugin $plugin
     * @return void
     */
    public function __construct(FP_Plugin $plugin)
    {
        $this->_plugin = $plugin;
    }

    /**
     * Create an invoice from a given order (using the adapter).
     *
     * @param  FP_Order_Adapter $order
     * @return array<string, mixed>
     */
    public function create_invoice( FP_Order_Adapter $order )
    {
        $plugin_settings            = $this->_plugin->get_settings();
        $order_status               = $order->get_status();
        $number_original            = $order->get_order_number();
        $payment_method             = $order->get_payment_method();
        $payment_title              = $order->get_payment_method_title();
        $number_modified            = $plugin_settings->get_order_number( $number_original );
        $base_location              = wc_get_base_location();
        $base_country               = $base_location['country'];
        $customer_note              = $order->get_customer_note();

        // Check for subscription renewal to update missing meta data
        $subscription_id = $order->get_order()->get_meta( '_subscription_renewal', true );
        if (!empty($subscription_id) && function_exists('wcs_get_subscription')) {
            $subscription = wcs_get_subscription($subscription_id);
            if (!empty($subscription)) {
                if (empty($payment_method)) {
                    $payment_method = $subscription->get_payment_method();
                }
                if (empty($payment_title)) {
                    $payment_title = $subscription->get_payment_method_title();
                }
            }
        }

        $status_paid                = $plugin_settings->mark_invoice_as_paid( $payment_method );
        $payment_date               = $order->get_date_paid();
        $payment_date               = is_null($payment_date) ? null : (int) $payment_date->getTimestamp();

        $result = array();

        $result['order_id']         = (string) $order->get_id();
        $result['order_key']        = (string) $order->get_order_key();
        $result['order_number']     = (string) $number_modified;
        $result['order_date']       = (int) $order->get_date_created()->getTimestamp();
        $result['invoice_currency'] = (string) $order->get_currency();
        $result['invoice_paid']     = (bool) $status_paid;
        $result['taxes_included']   = (bool) $order->get_prices_include_tax();
        $result['payment_method']   = (string) $payment_method;
        $result['payment_title']    = (string) $payment_title;
        $result['payment_date']     = $payment_date;
        $result['base_country']     = (string) $base_country;
        $result['customer_note']    = (string) $customer_note;
        $result['vat_exempt']       = (bool) $order->is_vat_exempt();

        $result['meta']             = $this->make_meta_data( $order );
        $result['billing']          = $this->make_billing( $order );
        $result['shipping']         = $this->make_shipping( $order );
        $result['items']            = $this->make_items( $order );

        return $result;
    }

    /**
     * Create the meta data for the invoice.
     *
     * @param  FP_Order_Adapter $order
     * @return array<array<string, mixed>>
     */
    private function make_meta_data( FP_Order_Adapter $order )
    {
        $meta = $order->get_meta_data();
        $result = array();

        foreach ( $meta as $key => $value )
        {
            $data = $value->get_data();
            $element = array();
            $element['key'] = $data['key'];
            $element['value'] = $data['value'];
            $result[] = $element;
        }

        return $result;
    }

    /**
     * Translates state code to word.
     * 
     * @param  string $country
     * @param  string $state
     * @return string
     */
    private function translate_state( $country, $state )
    {
        if ( empty( $country ) ) {
            return $state;
        }
        $states = WC()->countries->get_states( $country );
        return !empty( $state ) && isset( $states[ $state ] ) ? $states[ $state ] : $state;
    }

    /**
     * Create the billing data for the invoice.
     *
     * @param  FP_Order_Adapter $order
     * @return array<string, mixed>
     */
    private function make_billing( FP_Order_Adapter $order )
    {
        $plugin_settings = $this->_plugin->get_settings();

        $result = array();

        $country = (string) $order->get_billing_country('edit');
        $state = (string) $order->get_billing_state('edit');
        
        // Find customer vat id
        $meta_name = $plugin_settings->get_customer_vat_id_meta_name();
        $vat_id = empty($meta_name) ? '' : (string) $order->get_billing_vat_id( [ $meta_name ] );
        $vat_id = empty($vat_id) ? (string) $order->get_billing_vat_id() : $vat_id;
        
        $customer = $order->get_customer();
        $vat_exempt = $customer->is_vat_exempt();

        $result['customer_no']      = (string) $order->get_customer_id();
        $result['first_name']       = (string) $order->get_billing_first_name();
        $result['last_name']        = (string) $order->get_billing_last_name();
        $result['company']          = (string) $order->get_billing_company();
        $result['address_1']        = (string) $order->get_billing_address_1();
        $result['address_2']        = (string) $order->get_billing_address_2();
        $result['city']             = (string) $order->get_billing_city();
        $result['state']            = $this->translate_state( $country, $state );
        $result['postcode']         = (string) $order->get_billing_postcode();
        $result['country']          = $country;
        $result['email']            = (string) $order->get_billing_email();
        $result['phone']            = (string) $order->get_billing_phone();
        $result['salutation']       = (string) $order->get_billing_title();
        $result['vat_id']           = $vat_id;
        $result['vat_exempt']       = (bool) $vat_exempt;

        return $result;
    }

    /**
     * Create the shipping data for the invoice.
     *
     * @param  FP_Order_Adapter $order
     * @return array<string, mixed>
     */
    private function make_shipping( FP_Order_Adapter $order )
    {
        $result = array();

        $country = (string) $order->get_shipping_country('edit');
        $state = (string) $order->get_shipping_state('edit');

        $result['first_name']       = (string) $order->get_shipping_first_name();
        $result['last_name']        = (string) $order->get_shipping_last_name();
        $result['company']          = (string) $order->get_shipping_company();
        $result['address_1']        = (string) $order->get_shipping_address_1();
        $result['address_2']        = (string) $order->get_shipping_address_2();
        $result['city']             = (string) $order->get_shipping_city();
        $result['state']            = $this->translate_state( $country, $state );
        $result['postcode']         = (string) $order->get_shipping_postcode();
        $result['country']          = $country;
        $result['salutation']       = (string) $order->get_shipping_title();
        $result['vat_id']           = (string) $order->get_shipping_vat_id();

        return $result;
    }

    /**
     * Create all items for this invoice.
     *
     * @param  FP_Order_Adapter $order
     * @return array<array<string, mixed>>
     */
    private function make_items( FP_Order_Adapter $order )
    {
        $results = array();
        $results = array_merge($results, $this->make_products($order));
        $results = array_merge($results, $this->make_shippings($order));
        $results = array_merge($results, $this->make_fees($order));
        $results = array_merge($results, $this->make_discounts($order));
        $results = array_merge($results, $this->make_credits($order));
        return $results;
    }

    /**
     * Round value.
     * 
     * @param  string|float|int $val
     * @param  int $precision
     * @param  1|2|3|4 $mode
     * @return float
     */
    public static function round( $val, int $precision = 0, int $mode = PHP_ROUND_HALF_UP )
    {
		if (!is_numeric($val)) {
			$val = floatval($val);
		}
		return round($val, $precision, $mode);
	}

    /**
     * Create all product items for this invoice.
     *
     * @param  FP_Order_Adapter $adapter
     * @return array<array<string, mixed>>
     */
    private function make_products( FP_Order_Adapter $adapter )
    {
        $plugin_settings = $this->_plugin->get_settings();
        $order = $adapter->get_order();
        $products = array();

        /** @var WC_Order_Item_Product $product_item */
        foreach ( $order->get_items() as $product_item )
        {
            $name = $product_item->get_name();

            if ( !is_a( $product_item, 'WC_Order_Item_Product' ) ) {
                throw new FP_Factory_Error(
                    json_encode(['name' => $name]), 
                    FP_Factory_Error::WRONG_ORDER_ITEM_ERROR
                );
            }

            $product = new FP_Product_Adapter( $product_item, false );
            if ( empty( $product->get_product() ) ) {
                throw new FP_Factory_Error(
                    json_encode( [ 'name' => $name ] ),
                    FP_Factory_Error::ORDER_ITEM_MISSING_PRODUCT_ERROR
                );
            }

            $product_subtype = null;
            $product_subtype = is_callable( array( $product, 'is_downloadable' ) ) && $product->is_downloadable() ? 'download' : null;
            $product_subtype = is_callable( array( $product, 'is_virtual' ) ) && $product->is_virtual() ? 'virtual' : $product_subtype;
            $product_subtype = $product->get_meta( '_service', true ) === 'yes' ? 'service' : $product_subtype;

            $product_rate = $adapter->get_item_tax_id($product_item);

            if ( ! empty( $product_rate ) )
            {
                $wc_tax = WC_Tax::_get_tax_rate( $product_rate );
                $tax_name = $wc_tax['tax_rate_name'];
                $tax_rate = $wc_tax['tax_rate'];
            }
            else
            {
                $tax_name = null;
                $tax_rate = null;
            }

            $result = array();
            $result['type'] = 'product';
            $result['subtype'] = $product_subtype;
            $result['tax_rate'] = $tax_rate;
            $result['tax_name'] = $tax_name;

            $result['description'] = $this->get_product_description( $product_item );

            $result['price_net'] = $order->get_item_subtotal( $product_item, false, false );
            $result['price_gross'] = $order->get_item_subtotal( $product_item, true, false );

            $result['total_price_net'] = $order->get_item_total( $product_item, false, false );
            $result['total_price_gross'] = $order->get_item_total( $product_item, true, false );

            $price_num_decimals = $plugin_settings->get_price_num_decimals();
            if ($price_num_decimals > 0) {
                $result['price_net'] = $this->round( $result['price_net'], $price_num_decimals );
                $result['price_gross'] = $this->round( $result['price_gross'], $price_num_decimals );
                $result['total_price_net'] = $this->round( $result['total_price_net'], $price_num_decimals );
                $result['total_price_gross'] = $this->round( $result['total_price_gross'], $price_num_decimals );
            }

            /*if (class_exists('\Automattic\WooCommerce\Utilities\NumberUtil') && method_exists(\Automattic\WooCommerce\Utilities\NumberUtil::class, 'round')) {
                $result['price_net'] = \Automattic\WooCommerce\Utilities\NumberUtil::round( $result['price_net'], wc_get_rounding_precision() );
                $result['price_gross'] = \Automattic\WooCommerce\Utilities\NumberUtil::round( $result['price_gross'], wc_get_rounding_precision() );
                $result['total_price_net'] = \Automattic\WooCommerce\Utilities\NumberUtil::round( $result['total_price_net'], wc_get_rounding_precision() );
                $result['total_price_gross'] = \Automattic\WooCommerce\Utilities\NumberUtil::round( $result['total_price_gross'], wc_get_rounding_precision() );
            }*/

            $result['name'] = $this->get_product_name( $product_item );
            $result['quantity'] = $product_item->get_quantity();

            $result['number'] = $product->get_sku();
            $result['unit'] = $product->get_meta( '_unit', true );
            $result['manage_stock'] = $product->get_manage_stock();
            $result['is_in_stock'] = $product->is_in_stock();
            $result['stock_quantity'] = $product->get_stock_quantity();

            $products[] = $result;
        }

        return $products;
    }

    /**
     * Create the meta data for the invoice.
     *
     * @param  WC_Order_Item $product_item
     * @return WC_Product_Variation|null
     */
    private function get_product_variation(WC_Order_Item $product_item)
    {
        if ( is_callable(array($product_item, 'get_variation_id')) ) {
            $variation = new WC_Product_Variation( wc_get_product( $product_item->get_variation_id() ) );
            apply_filters( 'woocommerce_order_item_product', $variation, $product_item );
            return $variation;
        }
        return null;
    }

    /**
     * Get product alternate title.
     *
     * @param  FP_Product_Adapter $product
     * @param  string $setting
     * @return string
     */
    public function get_product_alternate_title(FP_Product_Adapter $product, $setting = '')
    {
        $alternate_title = '';

        // Get alternate title from plugin "Product Subtitle For WooCommerce".
        if ($this->_plugin->is_wc_product_subtitle_active()) {
            if ( function_exists('wcps_get_subtitle') ) {
                $alternate_title = wcps_get_subtitle( $product->get_id() );
            }
        }

        // Get alternate title from plugin "Secondary Title".
        if (
            empty($alternate_title)
            && $this->_plugin->is_secondary_title_active()
        ) {
            if (
                function_exists('has_secondary_title')
                && function_exists('get_secondary_title')    
            ) {
                $alternate_title = has_secondary_title( $product->get_id() )
                    ? get_secondary_title( $product->get_id() )
                    : '';
            } else if ( $setting == 'secondary_title_format' ) {
                $alternate_title = $product->get_meta( '_secondary_title', true );
            }
        }

        if ( empty($alternate_title) && $setting != 'secondary_title_format' ) {
            $alternate_title = $product->get_alternate_title();
        }

        return $alternate_title;
    }

    /**
     * Format the product name.
     *
     * @param  string $name
     * @param  string $alternate_title
     * @param  string $setting
     * @return string
     */
    public function format_product_name( $name, $alternate_title, $setting = '' )
    {
        if ( !empty($alternate_title) ) {
            if (
                $this->_plugin->is_secondary_title_active()
                && $setting == 'secondary_title_format'
            ) {
                /** Apply title format */
                $format = str_replace(
                    '"',
                    "'",
                    stripslashes( get_option( "secondary_title_title_format" ) )
                );

                $name = str_replace( "%title%", $name, $format );

                $name = str_replace(
                    "%secondary_title%",
                    html_entity_decode( $alternate_title ),
                    $name
                );
                return $name;
            }

            if ( $setting == 'product_name_and_alternate_title' ) {
                $name .= ' - ' . $alternate_title;
            } else if ( $setting == 'alternate_title_and_product_name' ) {
                $name = $alternate_title . ' - ' . $name;
            } else {
                $name = $alternate_title;
            }
        }
        return $name;
    }

    /**
     * Create the product name of the order item.
     *
     * @param  WC_Order_Item $product_item
     * @return string
     */
    private function get_product_name( WC_Order_Item $product_item )
    {
        $settings = $this->_plugin->get_settings();
        $setting = $settings->get_line_name();
        $name = $product_item->get_name();

        $filter = array(
            'product_name_and_alternate_title',
            'alternate_title_and_product_name',
            'alternate_title',
            'secondary_title_format',
        );
        if ( in_array( $setting, $filter ) ) {
            $product = new FP_Product_Adapter( $product_item, false );
            $alternate_title = $this->get_product_alternate_title( $product, $setting );
            $name = $this->format_product_name( $name, $alternate_title, $setting );
        }

        return $name;
    }

    /**
     * Create the product description of the order item.
     *
     * @param  WC_Order_Item $product_item
     * @return string
     */
    private function get_product_description( WC_Order_Item $product_item )
    {
        $settings = $this->_plugin->get_settings();
        $setting = $settings->get_line_description();
        $variation_id = is_callable( array( $product_item, 'get_variation_id' ) ) ? $product_item->get_variation_id() : null;

        // Use short description
        if ( $setting == 'short' ) {
            $product = new FP_Product_Adapter( $product_item, false );
            $description = $product->get_short_description();
            $description = strip_tags($description);
            return $description;
        }

        // Description explicitly from product or variation
        if ( $setting == 'article' ) {
            $product = new FP_Product_Adapter( $product_item, false );
            $description = $product->get_description();
            $description = strip_tags( $description );
            return $description;
        }

        // Description inherit from product or variation
        if ( $setting == 'article_or_variation_inherit' ) {
            $description = '';
            if ( ! empty( $variation_id ) ) {
                $variation = $this->get_product_variation( $product_item );
                $description = $variation->get_description();
                $description = strip_tags( $description );
            }
            if ( ! is_string( $description ) || empty( trim( $description ) ) ) {
                $product = new FP_Product_Adapter( $product_item, true );
                $description = $product->get_description();
                $description = strip_tags( $description );
            }
            return $description;
        }

        // Description from product and variation
        if ( $setting == 'article_and_variation' ) {
            $description = '';
            if ( ! empty( $variation_id ) ) {
                $variation = $this->get_product_variation( $product_item );
                $description = $variation->get_description();
                $description = strip_tags( $description );
                if ( ! empty( $description ) ) {
                    $description .= ' ';
                }
            }
            $product = new FP_Product_Adapter( $product_item, true );
            $product_description = $product->get_description();
            $product_description .= !empty( $product_description ) && !empty( $description ) ? ' ' : '';
            $description = $product_description . $description;
            $description = strip_tags( $description );
            return $description;
        }

        // Description strictly from product (never from variation)
        if ( $setting == 'article_strict' ) {
            $product = new FP_Product_Adapter( $product_item, true );
            $description = $product->get_description();
            $description = strip_tags( $description );
            return $description;
        }

        // Description strictly from variation (never from product)
        if ( $setting == 'variation' && ! empty( $variation_id ) ) {
            $variation = $this->get_product_variation( $product_item );
            $description = $variation->get_description();
            $description = strip_tags( $description );
            return $description;
        }

        // Use title as description
        if ( $setting == 'variation_title' && ! empty( $variation_id ) ) {
            $variation = $this->get_product_variation( $product_item );
            $attributes = $variation->get_variation_attributes();
            $description = implode( ', ', $attributes );
            return $description;
        }

        // Use meta data as description
        if ( $setting == 'meta_data' ) {
            $properties = array();
            foreach ( $product_item->get_meta_data() as $property ) {
                if ( substr( $property->key, 0, 1 ) === '_' ) {
                    continue;
                }
                $value = strip_tags( $property->value );
                $properties[] = "{$property->key}: {$value}";
            }
            $description = implode( PHP_EOL, $properties );
            return $description;
        }

        // Use mini description
        if ( $setting == 'mini_desc' ) {
            $product = new FP_Product_Adapter( $product_item, false );
            $description = $product->get_meta( '_mini_desc', true );
            $description = strip_tags( strval( $description ) );
            return $description;
        }

        // Use variation mini description
        if ( $setting == 'variation_mini_desc' ) {
            $variation = $this->get_product_variation( $product_item );
            $description = $variation->get_meta( '_mini_desc', true );
            $description = strip_tags( strval( $description ) );
            return $description;
        }

        // Use alternate title
        if ( $setting == 'alternate_title' ) {
            $product = new FP_Product_Adapter( $product_item, false );
            $description = $this->get_product_alternate_title( $product, $setting );
            $description = strip_tags( strval( $description ) );
            return $description;
        }

        return '';
    }

    /**
     * Create all shipping items for this invoice.
     *
     * @param  FP_Order_Adapter $adapter
     * @return array<array<string, mixed>>
     */
    private function make_shippings( FP_Order_Adapter $adapter )
    {
        $order = $adapter->get_order();

        $tax_rates = array();
        $gross_amounts = array();
        $net_amounts = array();
        $tax_amounts = array();
        $shippings = array();

        $settings = $this->_plugin->get_settings();
        $article_name = $settings->get_article_name_shipping();
        $article_number = $settings->get_article_number_shipping();

        foreach ( $order->get_taxes() as $tax_item )
        {
            $rate_id = $tax_item->get_rate_id();
            $wc_tax = WC_Tax::_get_tax_rate( $rate_id );
            $tax_rate = array();
            $tax_rate['name'] = $wc_tax['tax_rate_name'];
            $tax_rate['rate'] = $wc_tax['tax_rate'];
            $tax_rates[ $rate_id ] = $tax_rate;
            $tax_amounts[ $rate_id ] = 0;
            $gross_amounts[ $rate_id ] = 0;
            $net_amounts[ $rate_id ] = 0;
        }

        /** @var WC_Order_Item_Product $product_item */
        foreach ( $order->get_items() as $product_item )
        {
            $product_taxes = $product_item->get_taxes();
            $product_rate = $product_taxes['subtotal'];
            $product_rate = array_filter( $product_rate );
            $product_rate = array_flip( $product_rate );
            $product_rate = array_shift( $product_rate );

            if ( ! empty( $product_rate ) )
            {
                $net_amount = $product_item->get_subtotal();
                $tax_amount = $product_item->get_subtotal_tax();
                $gross_amount = floatval( $net_amount ) + floatval( $tax_amount );
                $net_amounts [ $product_rate ] += floatval( $net_amount );
                $tax_amounts [ $product_rate ] += floatval( $tax_amount );
                $gross_amounts [ $product_rate ] += $gross_amount;
            }
        }

        $total_net = array_sum($net_amounts);
        $total_tax = array_sum($tax_amounts);
        $total_gross = array_sum($gross_amounts);

        /** @var WC_Order_Item_Shipping $shipping_item */
        foreach ( $order->get_items('shipping') as $shipping_item )
        {
            $shipping_name = $shipping_item->get_name();
            $shipping_name = ! empty( $article_name ) ? $article_name : $shipping_name;
            $shipping_number = ! empty( $article_number ) ? $article_number : null;

            $shipping_taxes = $shipping_item->get_taxes();
            $shipping_taxes = $shipping_taxes['total'];
            $shipping_taxes = array_filter( $shipping_taxes );

            $shipping_tax = $shipping_item->get_total_tax();
            $shipping_net = $shipping_item->get_total();
            $shipping_gross = floatval( $shipping_net ) + floatval( $shipping_tax );

            // Case if no tax rates are applied to shipping
            // This is mostly the case when shipping is free
            // Use the first tax rate in order in this case if there is one
            // Pass the shipping untaxed if the hole order is untaxed

            if ( empty( $shipping_taxes ) )
            {
                if ( !empty( $tax_rates ) )
                {
                    $tax_rate = array_shift( $tax_rates );
                    $tax_name = ! empty( $tax_rate ) ? $tax_rate['name'] : null;
                    $tax_rate = ! empty( $tax_rate ) ? $tax_rate['rate'] : null;
                }
                else
                {
                    $tax_name = null;
                    $tax_rate = null;
                }

                $result = array();
                $result['type'] = 'shipping';
                $result['unit'] = null;
                $result['quantity'] = 1;
                $result['name'] = $shipping_name;
                $result['number'] = $shipping_number;
                $result['description'] = null;
                $result['price_net'] = $shipping_net;
                $result['price_gross'] = $shipping_gross;
                $result['tax_rate'] = $tax_rate;
                $result['tax_name'] = $tax_name;
                $shippings[] = $result;

                continue;
            }

            // Case if there is only one tax rate applied
            // This might be the case due to the setting to use the highest rate
            // Altough there are multiple tax rates in the order
            // Apply the single tax rate then and return the single item

            if ( count( $shipping_taxes ) === 1 )
            {
                $tax_rates = array_flip( $shipping_taxes );
                $rate_id = array_shift( $tax_rates );
                $wc_tax = WC_Tax::_get_tax_rate( $rate_id );
                $tax_name = $wc_tax['tax_rate_name'];
                $tax_rate = $wc_tax['tax_rate'];

                $result = array();
                $result['type'] = 'shipping';
                $result['unit'] = null;
                $result['quantity'] = 1;
                $result['name'] = $shipping_name;
                $result['number'] = $shipping_number;
                $result['description'] = null;
                $result['price_net'] = $shipping_net;
                $result['price_gross'] = $shipping_gross;
                $result['tax_rate'] = $tax_rate;
                $result['tax_name'] = $tax_name;
                $shippings[] = $result;

                continue;
            }

            // Case for multiple tax rates applied to the shipping costs
            // This is most likely the case when Germanized forces detailed calculation
            // Calculate the partial amounts in this case and add them to the items

            $split_taxes = $shipping_item->get_meta( '_split_taxes', true );
            if ( !empty($split_taxes) )
            {
                foreach ( $split_taxes as $split_tax )
                {
                    $tax_id = $split_tax['tax_rates'][0];
                    $price_net = $split_tax['net_amount'];
                    $price_gross = $split_tax['taxable_amount'];
    
                    $wc_tax = WC_Tax::_get_tax_rate( $tax_id );
                    $tax_name = $wc_tax['tax_rate_name'];
                    $tax_rate = $wc_tax['tax_rate'];
    
                    $result = array();
                    $result['type'] = 'shipping';
                    $result['unit'] = null;
                    $result['quantity'] = 1;
                    $result['name'] = $shipping_name;
                    $result['number'] = $shipping_number;
                    $result['description'] = null;
                    $result['tax_rate'] = $tax_rate;
                    $result['tax_name'] = $tax_name;
                    $result['price_net'] = $price_net;
                    $result['price_gross'] = $price_gross;
                    $shippings[] = $result;
                }

                continue;
            }
            
            foreach( $shipping_taxes as $tax_id => $tax_amount )
            {
                $wc_tax = WC_Tax::_get_tax_rate( $tax_id );
                $tax_name = $wc_tax['tax_rate_name'];
                $tax_rate = $wc_tax['tax_rate'];

                $net_amount = floatval( $net_amounts[ $tax_id ] );
                $gross_amount = floatval( $gross_amounts[ $tax_id ] );
                $net_ratio = ($total_net > 0) ? ( $net_amount / $total_net ) : 0;
                $gross_ratio = ($total_gross > 0) ? ( $gross_amount / $total_gross ) : 0;
                $price_net = round( floatval( $shipping_net ) * $net_ratio, 4 );
                $price_gross = round( $shipping_gross * $gross_ratio, 4 );

                $result = array();
                $result['type'] = 'shipping';
                $result['unit'] = null;
                $result['quantity'] = 1;
                $result['name'] = $shipping_name;
                $result['number'] = $shipping_number;
                $result['description'] = null;
                $result['tax_rate'] = $tax_rate;
                $result['tax_name'] = $tax_name;
                $result['price_net'] = $price_net;
                $result['price_gross'] = $price_gross;
                $shippings[] = $result;
            }
        }

        return $shippings;
    }

    /**
     * Create all fee items for this invoice.
     *
     * @param  FP_Order_Adapter $adapter
     * @return array<array<string, mixed>>
     */
    private function make_fees( FP_Order_Adapter $adapter )
    {
        $order = $adapter->get_order();
        $fees = array();

        /** @var WC_Order_Item_Fee $fee_item */
        foreach ( $order->get_items('fee') as $fee_item )
        {
            $fee_taxes = $fee_item->get_taxes();
            $fee_taxes = $fee_taxes['total'];
            $fee_taxes = array_filter( $fee_taxes );

            if ( empty($fee_taxes) )
            {
                $result = array();
                $result['type'] = 'fee';
                $result['name'] = $fee_item->get_name();
                $result['unit'] = null;
                $result['number'] = null;
                $result['quantity'] = $fee_item->get_quantity();
                $result['description'] = $fee_item->get_name();
                $result['price_net'] = $fee_item->get_total();
                $result['price_gross'] = $fee_item->get_total();
                $result['tax_rate'] = null;
                $result['tax_name'] = null;
                $fees[] = $result;
            }

            foreach ( $fee_taxes as $tax_id => $tax_amount )
            {
                $wc_tax = WC_Tax::_get_tax_rate( $tax_id );
                $tax_name = $wc_tax['tax_rate_name'];
                $tax_rate = $wc_tax['tax_rate'];
                $price_net = round( $tax_amount / ( $tax_rate / 100 ), 4 );
                $price_gross = round( $price_net + $tax_amount, 4 );

                $result = array();
                $result['type'] = 'fee';
                $result['name'] = $fee_item->get_name();
                $result['unit'] = null;
                $result['number'] = null;
                $result['quantity'] = $fee_item->get_quantity();
                $result['description'] = $fee_item->get_name();
                $result['price_net'] = $price_net;
                $result['price_gross'] = $price_gross;
                $result['tax_rate'] = $tax_rate;
                $result['tax_name'] = $tax_name;
                $fees[] = $result;
            }
        }

        return $fees;
    }

    /**
     * Create all discount items for this invoice.
     *
     * @param  FP_Order_Adapter $adapter
     * @return array<array<string, mixed>>
     */
    public function make_discounts( FP_Order_Adapter $adapter )
    {
        $order = $adapter->get_order();
        $tax_rates = array();
        $discounts = array();
        $tax_rates[0] = array();
        $tax_rates[0]['rate'] = 0;
        $tax_rates[0]['name'] = null;
        $discounts[0] = 0;

        foreach ( $order->get_taxes() as $tax )
        {
            $rate_id = $tax->get_rate_id();
            $wc_tax = WC_Tax::_get_tax_rate( $rate_id );
            $tax_rate = array();
            $tax_rate['name'] = $wc_tax['tax_rate_name'];
            $tax_rate['rate'] = $wc_tax['tax_rate'];
            $tax_rates[ $rate_id ] = $tax_rate;
            $discounts[ $rate_id ] = 0;
        }

        $coupons = array();
        $names = array();

        /** @var WC_Order_Item_Coupon $coupon_item */
        foreach ( $order->get_items( 'coupon' ) as $coupon_item )
        {
            $coupon = array();
            $coupon['name'] = $coupon_item->get_name();
            $coupon['code'] = $coupon_item->get_code();
            $names[] = $coupon_item->get_name();
            $coupons[] = $coupon;
        }

        $names = implode( ', ', $names );

        /*foreach ($order->get_items() as $product_item)
        {
            $product_taxes = $product_item->get_taxes();
            $product_rates = $product_taxes['subtotal'];
            $product_rates = array_filter($product_rates);
            $product_rates = array_flip($product_rates);
            $product_rate = array_shift($product_rates);
            $product_rate = !empty($product_rate) ? $product_rate : 0;

            $total = $product_item->get_total();
            $subtotal = $product_item->get_subtotal();
            $discount = $total - $subtotal;
            $discounts[$product_rate] += $discount;
        }*/

        $results = array();

        // TODO: Check commented out code above and if discount items are currently needed.
        /*foreach ($discounts as $rate_id => $discount)
        {
            if ( $discount == 0 ) continue;

            $tax_rate = $tax_rates[$rate_id]['rate'];
            $tax_name = $tax_rates[$rate_id]['name'];
            $price_net = $discount;
            $price_gross = $discount * ( ( 100 + $tax_rate ) / 100);

            $result = array();
            $result['type'] = 'discount';
            $result['name'] = __( 'Discount', 'fakturpro' ) . ( !empty( $names ) ? ' (' . $names . ')' : '' );
            $result['unit'] = null;
            $result['number'] = null;
            $result['quantity'] = 1;
            $result['description'] = __( 'Discount', 'fakturpro' );
            $result['price_net'] = $price_net;
            $result['price_gross'] = $price_gross;
            $result['tax_rate'] = $tax_rate;
            $result['tax_name'] = $tax_name;
            $results[] = $result;
        }*/

        return $results;
    }

    /**
     * Create all credit items for this invoice.
     *
     * @param  FP_Order_Adapter $adapter
     * @return array<array<string, mixed>>
     */
    public function make_credits( FP_Order_Adapter $adapter )
    {
        $settings = $this->_plugin->get_settings();
        $merge_credits = $settings->get_merge_credits();

        $credits = $adapter->get_credits_used();
        if ($merge_credits && !empty($credits)) {
            $names = array();
            $sum = 0;
            foreach ($credits as $credit) {
                $names[] = $credit['name'];
                $sum += $credit['amount'];
            }
            if ($sum != 0) {
                $credits = array(
                    array(
                        'name' => implode(', ', $names),
                        'amount' => $sum,
                    )
                );
            }
        }

        $results = array();

        foreach ($credits as $credit) {
            if ($credit['amount'] == 0) {
                continue;
            }

            $price_net = -$credit['amount'];
            $price_gross = $price_net;

            $result = array();
            $result['type'] = 'credit';
            $result['name'] = __( 'Credits', 'fakturpro' ) . (!empty($credit['name']) ? ' (' . $credit['name'] . ')' : '');
            $result['unit'] = null;
            $result['number'] = null;
            $result['quantity'] = 1;
            $result['description'] = __( 'Credits', 'fakturpro' );
            $result['price_net'] = $price_net;
            $result['price_gross'] = $price_gross;
            $result['tax_rate'] = null;
            $result['tax_name'] = null;
            $results[] = $result;
        }

        return $results;
    }

}

endif;
