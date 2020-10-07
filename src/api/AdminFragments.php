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

namespace yoannisj\shopify\api;

/**
 * Static class storing fragments used in Admin API requests
 */

class AdminFragments
{
    // =Static
    // =========================================================================

    // =Shop
    // -------------------------------------------------------------------------

    /**
     * @var String GraphQl fagment applied on `Shop` objects in Shopify Admin API requests
     */

    public static $shopDataFragment  = 'fragment shopDataFragment on Shop {
        adminId: id
        name
        setupRequired
        description
        primaryDomain {
            id
            host
            sslEnabled
            url
        }
        myshopifyDomain
        plan {
            displayName
            partnerDevelopment
            shopifyPlus
        }
        features {
            avalaraAvatax
            branding
            captcha
            captchaExternalDomains
            deliveryProfiles
            dynamicRemarketing
            giftCards
            harmonizedSystemCode
            liveView
            multiLocation
            # onboardingVisual
            reports
            showMetrics
            storefront
        }
        email
        contactEmail
        ianaTimezone
        unitSystem
        weightUnit
        currencyCode
        enabledPresentmentCurrencies
        currencyFormats {
            moneyFormat
            moneyInEmailsFormat
            moneyWithCurrencyFormat
            moneyWithCurrencyInEmailsFormat
        }
        currencySettings (first: 250) {
            edges {
                node {
                    currencyCode
                    currencyName
                    enabled
                    rateUpdatedAt
                }
            }
        }
        taxesIncluded
        checkoutApiSupported
        billingAddress {
            firstName
            lastName
            company
            address1
            address2
            city
            zip
            province
            provinceCode
            country
            countryCode: countryCodeV2
            latitude
            longitude
            phone
        }
        paymentSettings {
            supportedDigitalWallets
        }
        orderNumberFormatPrefix
        orderNumberFormatSuffix
    }';

    // =Collections
    // -------------------------------------------------------------------------

    /**
     * @var String
     */

    public static $collectionDataFragment = 'fragment collectionDataFragment on Collection {
        adminId: id
        storefrontId
        handle
        updatedAt
        title
        descriptionHtml
        description
        seo {
            title
            description
        }
        image {
            id
            altText
            originalSrc
        }
        productsCount
        ruleSet {
            appliedDisjunctively
            rules {
                column
                condition
                relation
            }
        }
    }';

    // =Products
    // -------------------------------------------------------------------------
    
    /**
     * @var String
     */

    public static $productDataFragment = 'fragment productDataFragment on Product {
        adminId: id
        storefrontId
        legacyResourceId
        createdAt
        updatedAt
        handle
        title
        descriptionHtml
        description
        seo {
          title
          description
        }
        featuredImage {
          id
          altText
          originalSrc
        }
        onlineStoreUrl
        isGiftCard
        productType
        vendor
        tags
        hasOnlyDefaultVariant
        totalVariants
        tracksInventory
        totalInventory
        hasOutOfStockVariants
        options {
            id
            name
            position
            values
        }
        priceRange {
            minVariantPrice {
                amount
                currencyCode
            }
            maxVariantPrice {
                amount
                currencyCode
            }
        }
    }';

    /**
     * @var String
     */

    public static $productVariantDataFragment = 'fragment productVariantDataFragment on ProductVariant {
        adminId: id
        storefrontId
        legacyResourceId
        createdAt
        updatedAt
        product {
            id
            handle
        }
        position
        selectedOptions {
            name
            value
        }
        displayName
        image {
            id
            altText
            originalSrc
        }
        price
        compareAtPrice
        sku
        barcode
        availableForSale
        inventoryPolicy
        inventoryQuantity
        inventoryItem {
            id
            sku
            duplicateSkuCount
        }
        presentmentPrices (first: 25) {
            edges {
                node {
                    price {
                        amount
                        currencyCode
                    }
                    compareAtPrice {
                        amount
                        currencyCode
                    }
                }
            }
        }
        taxable
        taxCode
        weight
        weightUnit
    }';
    
    // =Discounts
    // -------------------------------------------------------------------------

    /**
     * @var String GraphQl fagment applied on `DiscountCodeBasic` objects in Shopify Admin API requests
     */

    public static $discountCodeBasicFragment = 'fragment discountCodeBasicFragment on DiscountCodeBasic {
        status
        createdAt
        title
        summary
        shortSummary
        codeCount
        codes (first: 25) {
            edges {
                cursor
                node {
                    code
                }
            }
        }
        customerGets {
          ...discountCustomerGetsFragment
        }
        minimumRequirement {
            ...discountMinimumRequirementFragment
        }
        customerSelection {
            ...discountCustomerSelectionFragment
        }
        startsAt
        endsAt
        appliesOncePerCustomer
        usageLimit
        usageCount: asyncUsageCount
    }';

    /**
     * @var String GraphQl fagment applied on `DiscountCodeBxgy` objects in Shopify Admin API requests
     */

    public static $discountCodeBxgyFragment = 'fragment discountCodeBxgyFragment on DiscountCodeBxgy
    {
        status
        createdAt
        title
        summary
        codeCount
        codes (first: 25) {
            edges {
                cursor
                node {
                    code
                }
          }
        }
        customerBuys {
          ...discountCustomerBuysFragment
        }
        customerGets {
          ...discountCustomerGetsFragment
        }
        customerSelection {
          ...discountCustomerSelectionFragment
        }
        startsAt
        endsAt
        usesPerOrderLimit
        usageLimit
        usageCount: asyncUsageCount
    }';

    /**
     * @var String GraphQl fagment applied on `discountCodeFreeShippingFragment` objects in Shopify Admin API requests
     */

    public static $discountCodeFreeShippingFragment = 'fragment discountCodeFreeShippingFragment on DiscountCodeFreeShipping {
        status
        createdAt
        title
        summary
        shortSummary
        codeCount
        codes {
          edges {
            node {
              code
            }
          }
        }
        customerGets {
            ...discountCustomerGetsFragment
        }
        minimumRequirement {
            ...discountMinimumRequirementFragment
        }
        customerSelection {
            ...discountCustomerSelectionFragment
        }
        destinationSelection {
            ...discountShippingDestinationSelectionFragment
        }
        startsAt
        endsAt
        appliesOncePerCustomer
        usageLimit
        usageCount: asyncUsageCount
    }';

    /**
     * @var String GraphQl fagment applied on `DiscountAutomaticBasic` objects in Shopify Admin API requests
     */

    public static $discountAutomaticBasicFragment = 'fragment discountAutomaticBasicFragment on DiscountAutomaticBasic {
        status
        createdAt
        title
        summary
        shortSummary
        minimumRequirement {
            type: __typename
            ... on DiscountMinimumQuantity {
                ...discountMinimumQuantityFragment
            }
            ... on DiscountMinimumSubtotal {
                ...discountMinimumSubtotalFragment 
            }
        }
        customerGets {
            ...discountCustomerGetsFragment
        }
        startsAt
        endsAt
        usageCount
    }';
    
    /**
     * @var String GraphQl fagment applied on `DiscountAutomaticBxgy` objects in Shopify Admin API requests
     */

    public static $discountAutomaticBxgyFragment = 'fragment discountAutomaticBxgyFragment on DiscountAutomaticBxgy {
        status
        createdAt
        title
        customerBuys {
            ...discountCustomerBuysFragment
        }
        customerGets {
            ...discountCustomerGetsFragment
        }
        startsAt
        endsAt
        usesPerOrderLimit
        usageCount
    }';

    /**
     * @var String GraphQl fagment applied on `DiscountCustomerBuys` objects in Shopify Admin API requests
     */

    public static $discountCustomerBuysFragment = 'fragment discountCustomerBuysFragment on DiscountCustomerBuys {
            items {
            type: __typename
            ... on AllDiscountItems {
                ...allDiscountItemsFragment
            }
            ... on DiscountProducts {
                ...discountProductsFragment
            }
            ... on DiscountCollections {
                ...discountCollectionsFragment
            }
        }
        value {
            type: __typename
            ... on DiscountQuantity {
                ...discountQuantityFragment
            }
        }
    }';
    
    /**
     * @var String GraphQl fagment applied on `DiscountCustomerGets` objects in Shopify Admin API requests
     */

    public static $discountCustomerGetsFragment = 'fragment discountCustomerGetsFragment on DiscountCustomerGets {
            items {
            type: __typename
            ... on AllDiscountItems {
                ...allDiscountItemsFragment
            }
            ... on DiscountProducts {
                ...discountProductsFragment
            }
            ... on DiscountCollections {
                ...discountCollectionsFragment
            }
        }
        value {
            type: __typename
            ... on DiscountOnQuantity {
                ...discountOnQuantityFragment
            }
            ... on DiscountAmount {
                ...discountAmountFragment
            }
            ... on DiscountPercentage {
                ...discountPercentageFragment
            }
        }
    }';

    /**
     * @var String GraphQl fagment applied on `DiscountCustomerSelection` objects in Shopify Admin API requests
     */

    public static $discountCustomerSelectionFragment = 'fragment discountCustomerSelectionFragment on DiscountCustomerSelection {
        type: __typename
        ... on DiscountCustomerAll {
            ...discountCustomerAllFragment
        }
        ... on DiscountCustomers {
            ...discountCustomersFragment
        }
        ... on DiscountCustomerSavedSearches {
            ...discountCustomerSavedSearchesFragment
        }
    }';

    /**
     * @var String GraphQl fagment applied on `DiscountCustomerAll` objects in Shopify Admin API requests
     */

    public static $discountCustomerAllFragment = 'fragment discountCustomerAllFragment on DiscountCustomerAll {
      allCustomers
    }';

    /**
     * @var String GraphQl fagment applied on `DiscountCustomers` objects in Shopify Admin API requests
     */

    public static $discountCustomersFragment = 'fragment discountCustomersFragment on DiscountCustomers {
      customers {
        adminId: id
        email
      }
    }';

    /**
     * @var String GraphQl fagment applied on `DiscountCustomerSavedSearches` objects in Shopify Admin API requests
     */

    public static $discountCustomerSavedSearchesFragment = 'fragment discountCustomerSavedSearchesFragment on DiscountCustomerSavedSearches {
      savedSearches {
        ... savedSearchFragment
      }
    }';

    /**
     * @var String GraphQl fagment applied on `AllDiscountItems` objects in Shopify Admin API requests
     */

    public static $allDiscountItemsFragment = 'fragment allDiscountItemsFragment on AllDiscountItems {
        allItems
    }';
    
    /**
     * @var String GraphQl fagment applied on `DiscountProducts` objects in Shopify Admin API requests
     */

    public static $discountProductsFragment = 'fragment discountProductsFragment on DiscountProducts {
        products (first: 25) {
            edges {
                node {
                    adminId: id
                }
            }
        }
        productVariants (first: 25) {
            edges {
                node {
                    adminId: id
                }
            }
        }
    }';
    
    /**
     * @var String GraphQl fagment applied on `DiscountCollections` objects in Shopify Admin API requests
     */

    public static $discountCollectionsFragment = 'fragment discountCollectionsFragment on DiscountCollections {
        collections {
            edges {
                node {
                    adminId: id
                }
            }
        }
    }';
    
    /**
     * @var String GraphQl fagment applied on `DiscountOnQuantity` objects in Shopify Admin API requests
     */

    public static $discountOnQuantityFragment = 'fragment discountOnQuantityFragment on DiscountOnQuantity {
        effect {
            ... discountEffectFragment
        }
        quantity {
            quantity
        }
    }';

    /**
     * @var String GraphQl fagment applied on `DiscountQuantity` objects in Shopify Admin API requests
     */

    public static $discountQuantityFragment = 'fragment discountQuantityFragment on DiscountQuantity {
        quantity
    }';
    
    /**
     * @var String GraphQl fagment applied on `DiscountAmount` objects in Shopify Admin API requests
     */

    public static $discountAmountFragment = 'fragment discountAmountFragment on DiscountAmount {
        amount {
            amount
            currencyCode
        }
        appliesOnEachItem
    }';
    
    /**
     * @var String GraphQl fagment applied on `DiscountEffect` objects in Shopify Admin API requests
     */

    public static $discountEffectFragment = 'fragment discountEffectFragment on DiscountEffect {
        type: __typename
        ... on DiscountPercentage {
            ...discountPercentageFragment
        }
    }';

    /**
     * @var String GraphQl fagment applied on `DiscountPercentage` objects in Shopify Admin API requests
     */

    public static $discountPercentageFragment = 'fragment discountPercentageFragment on DiscountPercentage {
        percentage
    }';

    /**
     * @var String GraphQl fagment applied on `DiscountMinimumRequirement` objects in Shopify Admin API requests
     */

    public static $discountMinimumRequirementFragment = 'fragment discountMinimumRequirementFragment on DiscountMinimumRequirement {
        type: __typename
        ... on DiscountMinimumQuantity {
            ...discountMinimumQuantityFragment
        }
        ... on DiscountMinimumSubtotal {
            ...discountMinimumSubtotalFragment
        }
    }';
    
    /**
     * @var String GraphQl fagment applied on `DiscountMinimumQuantity` objects in Shopify Admin API requests
     */

    public static $discountMinimumQuantityFragment = 'fragment discountMinimumQuantityFragment on DiscountMinimumQuantity {
        greaterThanOrEqualToQuantity
    }';
    
    /**
     * @var String GraphQl fagment applied on `DiscountMinimumSubtotal` objects in Shopify Admin API requests
     */

    public static $discountMinimumSubtotalFragment = 'fragment discountMinimumSubtotalFragment on DiscountMinimumSubtotal {
        greaterThanOrEqualToSubtotal {
            amount
            currencyCode
        }
    }';

    // =Customers
    // -------------------------------------------------------------------------

    /**
     * @var String GraphQl fagment applied on `Customer` objects in Shopify Admin API requests
     */

    public static $customerDataFragment = 'fragment customerDataFragment on Customer {
      adminId: id
      createdAt
      updatedAt
      displayName
      firstName
      lastName
      email
      verifiedEmail
      defaultAddress {
        ...mailingAddressFragment
      }
      taxExempt
      state
      ordersCount
      totalSpent: totalSpentV2 {
        amount
        currencyCode
      }
      acceptsMarketing
      acceptsMarketingUpdatedAt
        marketingOptInLevel
      lifetimeDuration
      note
      hasTimelineComment
    }';

    // =Addresses
    // -------------------------------------------------------------------------

    /**
     * @var String GraphQl fagment applied on `MailingAddress` objects in Shopify Admin API requests
     */

    public static $mailAddressFragment = 'fragment mailingAddressFragment on MailingAddress {
        adminId: id
        name
        firstName
        lastName
        company
        address1
        address2
        zip
        city
        province
        provinceCode
        country
        countryCode: countryCodeV2
        latitude
        longitude
        phone
    }';

    // =Searches
    // -------------------------------------------------------------------------

    /**
     * @var String GraphQl fagment applied on `Event` objects in Shopify Admin API requests
     */

    public static $savedSearchFragment = 'fragment savedSearchFragment on SavedSearch {
        adminId: id
        legacyResourceId
        resourceType
        name
        searchTerms
        filters {
          key
          value
        }
        query 
    }';

    // =Events
    // -------------------------------------------------------------------------

    /**
     * @var String GraphQl fagment applied on `Event` objects in Shopify Admin API requests
     */

    public static $eventFragment = 'fragment eventFragment on Event {
        adminId: id
        createdAt
        criticalAlert
        appTitle
        attributeToApp
        attributeToUser
        message
    }';

}