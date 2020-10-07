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
use craft\i18n\Locale;
use craft\i18n\Formatter;

use yoannisj\shopify\Shopify;

class PriceData extends Model
{
    // =Static
    // =========================================================================

    // =Properties
    // =========================================================================

    /**
     * @var float
     */

    public $amount;

    /**
     * @var string
     */

    public $currency;

    /**
     * @var int
     */

    public $localeId;

    /** 
     * @var \craft\i18n\Locale
     */

    protected $locale;

    /**
     * @var string
     */

    protected $currencySymbol;

    // =Public Methods
    // =========================================================================

    // =Initialization
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */

    public function init()
    {
        parent::init();

        $this->formatter = new Formatter();
    }

    // =Attributes
    // -------------------------------------------------------------------------

    /**
     * Setter method for the `currencyCode` attribute
     */

    public function setCurrency( string $currency )
    {
        unset($this->currencySymbol);

        $this->currency = $currency;

        return $this;
    }

    /**
     * Setter method for the `localeId` attribute
     */

    public function getLocaleId( string $localeId )
    {
        unset($this->locale);
        unset($this->currencySymbol);

        $this->localeId = $localeId;

        return $this;
    }

    /**
     * Getter method for the `localeId` attribute
     */

    public function getLocaleId( string $localeId )
    {
        if (!isset($this->localeId))
        {
            $this->localeId = Craft::$app->language;
        }

        return $this->localeId;
    }

    /**
     * Getter method for the `locale` attribute (read-only)
     */

    public function getLocale(): Locale
    {
        if (!isset($this->locale))
        {
            $this->locale = new Locale($this->localeId);
        }

        return $this->locale;
    }

    /**
     * Getter method for the `currencySymbol` attribute (read-only)
     */

    public function getCurrencySymbol(): string
    {
        if (!isset($this->currencySymbol))
        {
            $this->currencySymbol = $this->getLocale()->getCurrencySymbol($this->currency);
        }

        return $this->currencySymbol;
    }

    // =Validation
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */

    public function rules(): array
    {
        $rules = parent::rules();

        $rules['defaultLocaleId'] = [ 'localeId', 'default', 'value' => Craft::$app->language ];

        return $rules;
    }

    // =Exporting
    // -------------------------------------------------------------------------

    /**
     * @inheritdocs
     */

    public function extraFields()
    {
        $fields = parent::extraFields();
        
        $fields = array_merge($fields, [
            'currencySymbol'
        ]);

        return $fields;
    }

    // =Magic Methods
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */

    public function __toString(): string
    {
        // @todo: Format price data according to Shopify store settings
        return $this->getLocale()->getFormatter()->asCurrency($this->amount);
    }

    // =Protected Methods
    // =========================================================================



}