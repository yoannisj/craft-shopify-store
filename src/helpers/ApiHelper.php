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

namespace yoannisj\shopify\helpers;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Json as JsonHelper;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\models\PriceData;

class ApiHelper
{

    /**
     * Returns a list of nodes from the results of a GraphQL connection query
     *
     * @param Object | Array $data
     * @return Array
     */

    public static function getConnectionNodes( $data ): array
    {
        $edges = ArrayHelper::getValue($data, 'edges');

        if (!empty($edges))
        {
            return ArrayHelper::getColumn($edges, 'node');
        }

        return $data;
    }

    /**
     * Parses price value returned by API, and returns a PriceData model
     *
     * @param String | Int | Array $value
     * @param String $site
     *
     * @return yoannisj\shopify\models\PriceData
     */

    public static function parsePriceValue( $value, string $site = null ): PriceData
    {
        if ($value instanceof PriceData) {
            return $value;
        }

        else if (is_numeric($value))
        {
            $currencyCode = Shopify::$plugin->adminApi->getShopCurrencyCode($site, true);

            $value = [
                'amount' => (float)$value,
                'currencyCode' => $currencyCode,
            ];
        }

        else if (is_string($value)) {
            $value = JsonHelper::decodeIfJson($value);
        }

        if ($site && !isset($value['localeId']))
        {
            $siteModel = Craft::$app->getSites()->getSiteByHandle($site);

            if ($siteModel) {
                $value['localeId'] = $siteModel->language;
            }
        }

        return new PriceData($value);
    }
}