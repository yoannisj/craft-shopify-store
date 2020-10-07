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

namespace yoannisj\shopify\models;

use Craft;
use craft\base\Model;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\models\PriceData;

class PriceRange extends Model
{
    // =Properties
    // =========================================================================

    /**
     * @var yoannisj\shopify\models\PriceData
     */

    public $minVariantPrice;


    /**
     * @var yoannisj\shopify\models\PriceData
     */

    public $maxVariantPrice;

    // =Public Methods
    // =========================================================================

    /**
     * Setter method for the `minVariantPrice` property
     *
     * @param String | Array | PriceData $price
     */

    public function setMinPrice( $price )
    {
        $this->minVariantPrice = ApiHelper::parsePriceValue($price);
    }

    /**
     * Setter method for the `maxVariantPrice` property
     *
     * @param String | Array | PriceData $price
     */

    public function setMaxVariantPrice( $price )
    {
        $this->maxVariantPrice = ApiHelper::parsePriceValue($price);
    }

}