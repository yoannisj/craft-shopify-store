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

/**
 * 
 */

class Checkout extends Model
{
    // =Static
    // =========================================================================

    const STATUS_PENDING = 'pending';
    const STATUS_CREATING = 'creating';
    const STATUS_UPDATING = 'updating';
    const STATUS_READY = 'ready';
    const STATUS_FAILED = 'failed';

    // =Properties
    // =========================================================================

    /**
     * @var string
     */

    public $key;

    /**
     * @var string
     */

    public $status;

    /**
     * @var string
     */

    public $storefrontId;

    /**
     * @var string
     */

    public $email;

    /**
     * @var \yoannisj\shopify\models\CheckoutLineItem[]
     */

    // public $lineItems;

    /**
     * @var \yoannisj\shopify\models\DiscountApplication[]
     */

    // public $discountApplications;

    /**
     * @var string
     */

    public $note;

    /**
     * @var array
     */

    public $customAttributes;

    /**
     * @var string
     */

    private $_webUrl;

    /**
     * @var bool
     */

    protected $isNormalizedWebUrl = false;

    /**
     * @var array
     */

    public $userErrors;

    // =Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */

    public function init()
    {
        if (!isset($this->status)) {
            $this->status = self::STATUS_PENDING;
        }

        parent::init();
    }

    // =Attributes
    // -------------------------------------------------------------------------

    /**
     * 
     */

    public function attributes()
    {
        $attributes = parent::attributes();

        $attributes[] = 'webUrl';

        return $attributes;
    }

    /**
     * @param string | null $value
     */

    public function setWebUrl( string $value = null )
    {
        $this->_webUrl = $value;
        $this->isNormalizedWebUrl = empty($value); // empty value does not need normalization
    }

    /**
     * @return string | null
     */

    public function getWebUrl()
    {
        if (!$this->isNormalizedWebUrl && !empty($this->_webUrl))
        {
            // use shop's primary domain in webUrl
            $domain = parse_url($this->_webUrl, PHP_URL_HOST);
            $shop = Shopify::$plugin->getShops()->getShopByDomain($domain);

            if ($shop && !empty($shop->primaryDomain)) {
                $this->_webUrl = str_replace($domain, $shop->primaryDomain, $this->_webUrl);
            }
            
            $this->isNormalizedWebUrl = true;
        }

        return $this->_webUrl;
    }

    // =Validation
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */

    public function rules()
    {
        $rules = parent::rules();

        // =requirements
        $rules['attrRequired'] = [ ['key', 'status'], 'required' ];
        $rules['attrRequiredWhenReady'] = [ ['storefrontId', 'webUrl'], 'required',
            'when' => function($model) {
                return $model->getIsReady();
            }
        ];

        // =formatting
        $rules['attrString'] = [ ['key', 'note', 'storefrontId'], 'string' ];

        $rules['statusIn'] = [ 'status', 'in', 'range' => [
            self::STATUS_PENDING,
            self::STATUS_CREATING,
            self::STATUS_UPDATING,
            self::STATUS_READY,
            self::STATUS_FAILED,
        ] ];

        $rules['emailEmail'] = [ 'email', 'email' ];
        $rules['webUrlUrl'] = [ 'webUrl', 'url' ];

        return $rules;
    }

    // =Fields
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */

    public function fields()
    {
        $fields = parent::fields();

        $fields[] = 'isReady';

        return $fields;
    }

    /**
     * @return bool
     */

    public function getIsReady(): bool
    {
        return ($this->status == self::STATUS_READY);
    }

    // =Protected Methods
    // =========================================================================

    // =Private Methods
    // =========================================================================
}