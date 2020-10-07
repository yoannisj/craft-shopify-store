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

use yii\base\Component;
use yii\base\InvalidConfigException;

use Craft;
use craft\helpers\ArrayHelper;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\models\Checkout;
use yoannisj\shopify\queue\jobs\CheckoutCreate;

/**
 * Service Class (singleton) to manage Shopify checkouts in Craft
 */

class Checkouts extends Component
{
    /**
     * @var int
     *
     * @todo: move checkout cache duration to a config setting
     */

    const CHECKOUT_CACHE_DURATION = 1200;

    /**
     *
     */

    public function getCheckoutByKey( string $key )
    {
        $data = Craft::$app->getCache()->get($key);

        if (!$data) return null;

        $checkout = new Checkout();
        $checkout->setAttributes($data, false);

        return $checkout;
    }

    /**
     * 
     */

    public function createCheckout( string $key, array $input, int $siteId = null, bool $useQueue = false ): Checkout
    {        
        // get requested checkout site
        $sites = Craft::$app->getSites();
        $site = $siteId ? $sites->getSiteById($siteId) : $sites->getCurrentSite();

        // initialize new checkout model
        $checkout = new Checkout([ 'key' => $key ]);

        if ($useQueue)
        {
            $this->saveCheckout($checkout, false);

            // add job to create checkout on shopify to the queue
            $job = new CheckoutCreate([
                'key' => $key,
                'input' => $input,
                'siteId' => $site->id,
            ]);

            Craft::$app->queue->push($job);

            // return new checkout (not populated yet)
            return $checkout;
        }

        // get checkout data by creating checkout on shopify
        $data = Shopify::$plugin->getStorefrontApi()->createCheckout($input, $site->handle);

        if ($data)
        {
            $checkout->setAttributes($data, false);
            $checkout->status = Checkout::STATUS_READY;
        } else {
            // @todo: set `userErrors` attribute on the checkout model
            $checkout->status = Checkout::STATUS_FAILED;
        }

        $success = $this->saveCheckout($checkout);

        if (!$success) {
            // throw error
        }

        return $checkout;
    }

    /**
     * 
     */

    public function saveCheckout( Checkout $checkout, bool $runValidation = true )
    {
        if ($runValidation && !$checkout->validate()) {
            return false;
        }

        if (!isset($checkout->key)) {
            throw new InvalidConfigException('Can not save checkout if key is not set.');
        }

        $data = $checkout->toArray();
        Craft::$app->getCache()->set($checkout->key, $data, self::CHECKOUT_CACHE_DURATION);

        return true;
    }

}