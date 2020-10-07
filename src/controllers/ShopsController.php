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

namespace yoannisj\shopify\controllers;

use yii\queue\Queue;

use Craft;
use craft\web\Controller;
use craft\helpers\ArrayHelper;

use yoannisj\shopify\Shopify;
use yoannisj\shopify\models\Shop;
use yoannisj\shopify\records\ShopRecord;

use yoannisj\shopify\queue\jobs\PullShopData;
use yoannisj\shopify\queue\jobs\PullProductData;

/**
 *
 */

class ShopsController extends Controller
{
    // =Static
    // =========================================================================

    // =Properties
    // =========================================================================

    /**
     * @var Bool
     */

    public $allowAnonymous = false;

    // =Public Methods
    // =========================================================================

    /**
     * 
     */

    public function actionPull()
    {
        $request = Craft::$app->getRequest();

        $siteIds = $request->getBodyParam('siteIds') ?? [];
        $includeProducts = (bool)$request->getBodyParam('includeProducts');

        if ($siteIds == '*') {
            $siteIds = ArrayHelper::getColumn(Craft::$app->getSites()->getAllSites(), 'id');
        }

        Craft::$app->queue->on(Queue::EVENT_AFTER_ERROR, function() {
            Craft::error($e->getMessage());
        });

        if (!empty($siteIds))
        {
            foreach ($siteIds as $siteId)
            {
                Craft::$app->queue->push(new PullShopData([
                    'siteId' => $siteId,
                    'includeProducts' => $includeProducts
                ]));
            }
        }

        $this->redirectToPostedUrl();
    }

}
