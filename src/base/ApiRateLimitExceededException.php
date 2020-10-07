<?php

namespace yoannisj\shopify\base;

use yii\web\TooManyRequestsHttpException;

/**
 * Class for exceptions thrown when the Shopify Api Limit has been exceeded
 */

class ApiRateLimitExceededException extends TooManyRequestsHttpException
{
    /**
     * @inheritdoc
     */

    public function getName()
    {
        return 'Shopify api rate limit exceeded';
    }

    // @todo: Add throttling information in ApiRateLimitExceededException to help determine best TTR
}