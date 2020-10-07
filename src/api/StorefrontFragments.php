<?php

/**
 * Shopify plugin for Craft
 *
 * @author Yoannis Jamar
 * @copyright Copyright (c) 2019 Yoannis Jamar
 * @link https://github.com/yoannisj/
 * @package craft-shopify
 *
 */

namespace yoannisj\shopify\api;

/**
 * Static class storing fragments used in Admin API requests
 */

class StorefrontFragments
{
    // =Static
    // =========================================================================

    // =Checkout
    // -------------------------------------------------------------------------

    /**
     * @var string GraphQl fagment applied on `CheckoutLineItem` objects in Shopify Storefront API requests
     */

    public static $checkoutLineItemDataFragment = 'fragment checkoutLineItemDataFragment on CheckoutLineItem {
        id
        title
        quantity
        value
    }';

}