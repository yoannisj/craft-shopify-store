<?php

/**
 * Shopify plugin for Craft
 *
 * @author Yoannis Jamar
 * @copyright Copyright (c) 2019 Yoannis Jamar
 * @link https://github.com/yoannisj/
 * @package craft-shopify
 */

namespace yoannisj\shopify\services;

use Craft;
use craft\helpers\ArrayHelper;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\api\AdminFragments;
use yoannisj\shopify\base\ApiService;
use yoannisj\shopify\helpers\ApiHelper;

/**
 * Service class (singleton) implementing requests to Shopify's Storefront API
 */

class AdminApi extends ApiService
{
    // =Static
    // =========================================================================

    // =Properties
    // =========================================================================

    // =Public Methods
    // =========================================================================

    // =Shop
    // -------------------------------------------------------------------------

    /**
     * Returns ids of all products in Shopify store
     *
     * @param String $site
     * @param bool $useCache
     *
     * @return Array
     */

    public function getShopId( string $site = null, bool $useCache = true )
    {
        // build graphql query
        $query = 'query {
            shop {
                adminId: id
            }
        }';

        // add field fragments
        $results = $this->request($query, null, $site, $useCache);

        return ArrayHelper::getValue($results, 'data.shop.id');
    }

    /**
     * Returns ids of all products in Shopify store
     *
     * @param String $site
     * @param bool $useCache
     *
     * @return Array
     */

    public function getShopCurrencyCode( string $site = null, bool $useCache = true )
    {
        // build graphql query
        $query = 'query {
            shop {
                currencyCode
            }
        }';

        // add field fragments
        $results = $this->request($query, null, $site, $useCache);

        return ArrayHelper::getValue($results, 'data.shop.currencyCode');
    }

    /**
     * Returns Shopify shop's data
     *
     * @return array
     */

    public function getShopData( string $site = null, bool $useCache = true )
    {
        // build graphql query
        $query = 'query {
            shop {
                ...shopDataFragment
            }
        }';

        // add field fragments
        $query .= ' ' . AdminFragments::$shopDataFragment;

        $results = $this->request($query, null, $site, $useCache);

        return ArrayHelper::getValue($results, 'data.shop');
    }

    // =Collections
    // -------------------------------------------------------------------------

    // =Products
    // -------------------------------------------------------------------------

    /**
     * Returns ids of all products in Shopify store
     *
     * @param String $site
     * @param bool $useCache
     *
     * @return Array
     */

    public function getAllProductIds( string $site = null, bool $useCache = true )
    {
        $query = 'query allProductIds {
            products(first: 250) {
                edges {
                    cursor
                    node {
                        adminId: id
                    }
                }
            }
        }';

        $results = $this->request($query, null, $site, $useCache);
        $productEdges = ArrayHelper::getValue($results, 'data.products.edges');

        // if count($productEdges == 250) {
        //      run new request to fetch missing products...
        // }

        $ids = ArrayHelper::getColumn($productEdges, 'node.adminId');

        return $ids;
    }

    /**
     * Returns product data for given product id
     *
     * @param string $shopifyId The product's id, as returned by Shopify's GraphQl Admin API
     * @param string $site The handle of the Craft site associated to the Shopify shop
     * @param bool $useCache The handle of the Craft site associated to the Shopify shop
     *
     * @return Array
     * @throws Exception Error returned by Shopify's API
     */

    public function getProductDataById( string $shopifyId, string $site = null, bool $useCache = true ): array
    {
        // build graphql query
        $query = 'query productDataById ($adminId: ID!) {
            product (id: $adminId) {
                ...productDataFragment
                variants (first: 1) {
                    edges {
                        cursor
                        node {
                            ...productVariantDataFragment
                        }
                    }
                }
                collections (first: 250) {
                    edges {
                        cursor
                        node {
                            adminId: id
                            storefrontId
                            handle
                        }
                    }
                }
            }
        }';

        // add field fragments
        $query .= ' ' . AdminFragments::$productDataFragment;
        $query .= ' ' . AdminFragments::$productVariantDataFragment;

        // build list of query variables
        $variables = [ 'adminId' => $shopifyId ];

        // get data by posting graphql request
        $results = $this->request($query, $variables, $site, $useCache);

        return ArrayHelper::getValue($results, 'data.product');
    }

    /**
     * Returns data for given product, by its handle
     *
     * @param string $handle
     * @param string $site
     * @param bool $useCache
     *
     * @return Array
     * @throws Exception Error returned by Shopify's API
     *
     * @todo: query variants in separate query(-ies) to avoid API rate limits
     */

    public function getProductDataByHandle( string $handle, string $site = null, bool $useCache = true ): array
    {
    // build graphql query
        $query = 'query ProductDataByHandle ($handle: String!) {
            product: productByHandle (handle: $handle) {
                ...productDataFragment
                variants (first: 1) {
                    edges {
                        cursor
                        node {
                            ...productVariantDataFragment
                        }
                    }
                }
                collections (first: 250) {
                    edges {
                        cursor
                        node {
                            adminId: id
                            handle
                        }
                    }
                }
            }
        }';

        // add field fragments
        $query .= ' ' . AdminFragments::$productDataFragment;
        $query .= ' ' . AdminFragments::$productVariantDataFragment;

        // build list of query variables
        $variables = [ 'handle' => $handle ];

        // get data by posting graphql request
        $results = $this->request($query, $variables, $site, $useCache);

        return ArrayHelper::getValue($results, 'data.product');
    }

    // =Discounts
    // -------------------------------------------------------------------------

    /**
     * Returns ids of all automatic discounts in Shopify store
     *
     * @param String $site
     * @param bool $useCache
     *
     * @return Array
     */

    public function getAllCodeDiscountIds( string $site = null, bool $useCache = true )
    {
        $query = 'query allCodeDiscounts {
            codeDiscounts: codeDiscountNodes (first: 250) {
                edges {
                    cursor
                    node {
                        adminId: id
                    }
                }
            }
        }';

        $results = $this->request($query, null, $site, $useCache);
        $discountEdges = ArrayHelper::getValue($results, 'data.codeDiscounts.edges');

        $ids = ArrayHelper::getColumn($results, 'node.adminId');

        return $ids;
    }

    /**
     * Returns automatic discount data for given automatic discount id
     *
     * @param string $id
     * @param string $site
     * @param bool $useCache
     *
     * @return Array
     * @throws Exception Error returned by Shopify's API
     */

    public function getCodeDiscountDataById( string $id, string $site = null, bool $useCache = true )
    {
        $query = 'query codDiscountDataById ($id: ID!) {
            codeDiscount: automaticDiscountNode (id: $id) {
                adminId: id
                codeDiscount {
                    type: __typename
                    ... on DiscountCodeBasic {
                        ...discountCodeBasicFragment
                    }
                    ... on DiscountCodeBxgy {
                        ...discountCodeBxgyFragment
                    }
                    ... on DiscountCodeFreeShipping {
                        ...discountCodeFreeShippingFragment
                    }
                }
                # events (first: 25) {
                #     edges {
                #         node {
                #             ...eventFragment
                #         }
                #     }
                # }
            }
        }';

        $query .= ' ' . AdminFragments::$discountCodeBasicFragment;
        $query .= ' ' . AdminFragments::$discountCodeBxgyFragment;
        $query .= ' ' . AdminFragments::$discountCodeFreeShippingFragment;
        // $query .= ' ' . AdminFragments::$eventFragment;

        $results = $this->request($query, [ 'id' => $id ], $site, $useCache);
        $data = ArrayHelper::getValue($results, 'data.codeDiscount');

        return $data;
    }

    /**
     * Returns ids of all code discounts in Shopify store
     *
     * @param String $site
     * @param bool $useCache
     *
     * @return Array
     */

    public function getAllAutomaticDiscountIds( string $site = null, bool $useCache = true )
    {
        $query = 'query allAutomaticDiscountIds {
            automaticDiscounts: automaticDiscountNodes (first: 250) {
                edges {
                    cursor
                    node {
                        adminId: id
                    }
                }
            }
        }';

        $results = $this->request($query, null, $site, $useCache);
        $discountEdges = ArrayHelper::getValue($results, 'data.automaticDiscounts.edges');

        $ids = ArrayHelper::getColumn($results, 'node.adminId');

        return $ids;
    }

    /**
     * Returns automatic discount data for given automatic discount id
     *
     * @param string $id
     * @param string $site
     * @param bool $useCache
     *
     * @return Array
     * @throws Exception Error returned by Shopify's API
     */

    public function getAutomaticDiscountDataById( string $id, string $site = null, bool $useCache = true )
    {
        $query = 'query automaticDiscountDataById ($id: ID!) {
            automaticDiscount: automaticDiscountNode (id: $id) {
                adminId: id
                automaticDiscount {
                    type: __typename
                    ... on DiscountAutomaticBasic {
                        ...discountAutomaticBasicFragment
                    }
                    ... on DiscountAutomaticBxgy {
                        ...discountAutomaticBxgyFragment
                    }
                }
                # events (first: 25) {
                #     edges {
                #         node {
                #             ...eventFragment
                #         }
                #     }
                # }
            }
        }';

        $query .= ' ' . AdminFragments::$discountAutomaticBasicFragment;
        $query .= ' ' . AdminFragments::$discountAutomaticBxgyFragment;
        // $query .= ' ' . AdminFragments::$eventFragment;

        $results = $this->request($query, ['id' => $id], $site, $useCahce);
        $data = ArrayHelper::getValue($results, 'data.automaticDiscount');

        return $data;
    }

    // =Protected Methods
    // =========================================================================

    /**
     * @param string $site
     * @return string
     */

    protected function getClientBaseUri( string $site = null )
    {
        $settings = shopify::$plugin->getSettings();
        $storeDomain = $settings->getStoreDomain($site);
        $apiVersion = $settings->getAdminApiVersion($site);

        return 'https://' . rtrim($storeDomain, '/') . '/admin/api/' . $apiVersion . '/';
    }

    /**
     * @param string $site
     * @return array
     */

    protected function getClientHeaders( string $site = null )
    {
        $headers = parent::getClientHeaders($site);
        $apiPassword = Shopify::$plugin->getSettings()->getApiPassword($site);

        return array_merge($headers, [
            'X-Shopify-Access-Token' => $apiPassword
        ]);
    }
}