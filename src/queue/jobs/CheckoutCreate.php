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

namespace yoannisj\shopify\queue\jobs;

use yii\base\InvalidConfigException;
use yii\queue\Queue;

use Craft;
use craft\queue\QueueInterface;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\models\Checkout;
use yoannisj\shopify\queue\BaseShopifyJob;

/**
 * Job class used to pull a Shopify product's data using the API, and store it in the Database
 */

class CheckoutCreate extends BaseShopifyJob
{
    // =Static
    // =========================================================================

    // =Properties
    // =========================================================================

    /**
     * @var string
     */

    public $key;

    /**
     * @var array
     */

    public $input;

    // =Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */

    public function init()
    {
        if (!isset($this->key)) {
            throw new InvalidConfigExceptio('Missing required setting `key`.');
        }

        if (empty($this->input)) {
            throw new InvalidConfigException('Setting `input` can not be empty.');
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */

    public function execute( $queue )
    {
        $checkouts = Shopify::$plugin->getCheckouts();
        $checkout = $checkouts->getCheckoutByKey($this->key);

        if (!$checkout) {
            $checkout = new Checkout([ 'key' => $this->key ]);
        }

        // update checkout status
        $checkout->status = Checkout::STATUS_CREATING;
        $checkouts->saveCheckout($checkout);

        // get checkout data by creating it via Shopify API
        $input = $this->input;
        $site = $this->getSite();

        $data = Shopify::$plugin->getStorefrontApi()->createCheckout($input, $site->handle);

        if ($data)
        {
            // update checkout properties
            $checkout->status = Checkout::STATUS_READY;
            $checkout->setAttributes($data, false);
        }

        else {
            $checkout->status = Checkout::STATUS_FAILED;
            // @todo: set `userErrors` attribute on the checkout model
        }

        // save checkout
        $success = $checkouts->saveCheckout($checkout);
    }

    // =Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */

    protected function defaultDescription(): string
    {
        return Craft::t('shopify-store', 'Create Shopify checkout');
    }

}