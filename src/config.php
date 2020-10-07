<?php

/**
 * Configuration settings for Craft-Shopify plugin
 */

return [

    /** 
     * Domain of shopify store to which Shopify requests are sent
     * @accepts string | array (multi-site)
     */

    'storeDomain' => null,

    /** 
     * Version of Shopify APIs (private app; admin API, webhook API and
     * storefront API, unless overridden with 'adminApiVersion',
     * 'webhookApiVersion' or 'storeFrontApiVersion' settings)
     *
     * @var string | array (multi-site)
     */

    'apiVersion' => null,

    /** 
     * Version of Shopify Admin API (private app)
     * @accepts string | array (multi-site)
     */

    'adminApiVersion' => null,

    /** 
     * Version of Shopify webhook API (private app)
     * @accepts string | array (multi-site)
     */

    'webhookApiVersion' => null, 

    /** 
     * Version of Shopify Storefront API (private app)
     * @accepts string | array (multi-site)
     */

    'storefrontApiVersion' => null, 

    /** 
     * Key used for requests to Shopify's Admin API (private app)
     * @accepts string | array (multi-site)
     */

    'apiKey' => null,

    /** 
     * Password used for requests to Shopify's Admin API (private app)
     * @accepts string | array (multi-site)
     */

    'apiPassword' => null,

    /** 
     * Shared Secret used for requests to Shopify's Admin API (private app)
     * @accepts string | array (multi-site)
     */

    'apiSecret' => null,

    /** 
     * Secret used to verify Shopify webhooks
     * @accepts string | array (multi-site)
     */

    'webhookSecret' => null, 

    /** 
     * Acces Token Secret used for requests to Shopify's Storefront API (private app)
     * @accepts string | array (multi-site)
     */

    'accessToken' => null,

    /** 
     * Uri format used to route requests to a shopify product
     * @accepts string | array (multi-site)
     */

    'productUriFormat' => 'products/{handle}',

    /** 
     * Template rendered when routing to a shopify product
     * @accepts string | array (multi-site)
     */

    'productTemplate' => '_shopify-product',

];