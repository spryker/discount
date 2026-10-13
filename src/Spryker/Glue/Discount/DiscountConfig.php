<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount;

use Spryker\Glue\Kernel\AbstractBundleConfig;
use Spryker\Shared\Discount\DiscountConfig as SharedDiscountConfig;

class DiscountConfig extends AbstractBundleConfig
{
    public const string RESOURCE_DISCOUNT_OPTIONS = 'discount-options';

    public const string RESOURCE_DISCOUNTS = 'discounts';

    public const string RESOURCE_DISCOUNT_VOUCHER_CODES = 'discount-voucher-codes';

    public const string RESOURCE_DISCOUNT_VOUCHER_CODES_GENERATE = 'discount-voucher-codes-generate';

    public const string CALCULATOR_TYPE_PERCENTAGE = 'percentage';

    public const string CALCULATOR_TYPE_FIXED = 'fixed';

    /**
     * @uses \Spryker\Zed\Discount\DiscountDependencyProvider::PLUGIN_CALCULATOR_PERCENTAGE
     */
    protected const string PLUGIN_CALCULATOR_PERCENTAGE = 'PLUGIN_CALCULATOR_PERCENTAGE';

    /**
     * @uses \Spryker\Zed\Discount\DiscountDependencyProvider::PLUGIN_CALCULATOR_FIXED
     */
    protected const string PLUGIN_CALCULATOR_FIXED = 'PLUGIN_CALCULATOR_FIXED';

    public const string ATTRIBUTE_DISPLAY_NAME = 'displayName';

    public const string ATTRIBUTE_CALCULATOR_TYPE = 'calculatorType';

    public const string ATTRIBUTE_FIXED_AMOUNTS = 'fixedAmounts';

    public const string ATTRIBUTE_COLLECTOR = 'collector';

    public const string ATTRIBUTE_DECISION_RULE = 'decisionRule';

    public const string ATTRIBUTE_STORES = 'stores';

    public const string ATTRIBUTE_DISCOUNT_UUID = 'discountUuid';

    public const string ATTRIBUTE_CODE = 'code';

    public const string ATTRIBUTE_CODES = 'codes';

    /**
     * @uses \Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaProviderFactory::TYPE_COLLECTOR
     */
    public const string QUERY_STRING_TYPE_COLLECTOR = 'collector';

    /**
     * @uses \Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaProviderFactory::TYPE_DECISION_RULE
     */
    public const string QUERY_STRING_TYPE_DECISION_RULE = 'decision-rule';

    /**
     * @uses \Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\UniqueDisplayNameDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_DISPLAY_NAME
     */
    protected const string ENTITY_IDENTIFIER_DISPLAY_NAME = 'discountGeneral.displayName';

    /**
     * @uses \Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\CalculatorPluginDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_CALCULATOR_PLUGIN
     */
    protected const string ENTITY_IDENTIFIER_CALCULATOR_PLUGIN = 'discountCalculator.calculatorPlugin';

    /**
     * @uses \Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\MoneyAmountCurrencyDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_MONEY_VALUE_COLLECTION
     */
    protected const string ENTITY_IDENTIFIER_MONEY_VALUE_COLLECTION = 'discountCalculator.moneyValueCollection';

    /**
     * @uses \Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_COLLECTOR_QUERY_STRING
     */
    protected const string ENTITY_IDENTIFIER_COLLECTOR_QUERY_STRING = 'discountCalculator.collectorQueryString';

    /**
     * @uses \Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_DECISION_RULE_QUERY_STRING
     */
    protected const string ENTITY_IDENTIFIER_DECISION_RULE_QUERY_STRING = 'discountCondition.decisionRuleQueryString';

    /**
     * @uses \Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\StoreRelationDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_STORES
     */
    protected const string ENTITY_IDENTIFIER_STORES = 'discountGeneral.storeRelation.stores';

    /**
     * @uses \Spryker\Zed\Discount\Business\Validator\Rule\DiscountVoucher\DiscountTypeDiscountVoucherValidatorRule::ENTITY_IDENTIFIER_ID_DISCOUNT
     */
    protected const string ENTITY_IDENTIFIER_ID_DISCOUNT = 'discountVoucher.idDiscount';

    /**
     * @uses \Spryker\Zed\Discount\Business\Deleter\DiscountVoucherDeleter::ENTITY_IDENTIFIER_CODE
     */
    protected const string ENTITY_IDENTIFIER_CODE = 'discountVoucher.code';

    public const string RESPONSE_CODE_DISCOUNT_NOT_FOUND = '5700';

    public const string RESPONSE_CODE_INVALID_SORT_FIELD = '5740';

    public const string RESPONSE_CODE_DISPLAY_NAME_NOT_UNIQUE = '5701';

    public const string RESPONSE_CODE_CALCULATOR_PLUGIN_INVALID = '5703';

    public const string RESPONSE_CODE_MONEY_AMOUNT_INVALID = '5705';

    public const string RESPONSE_CODE_QUERY_STRING_INVALID = '5706';

    public const string RESPONSE_CODE_STORE_UNKNOWN = '5710';

    public const string RESPONSE_CODE_VOUCHER_CODE_NOT_FOUND = '5720';

    public const string RESPONSE_CODE_VOUCHER_DISCOUNT_TYPE = '5721';

    public const string RESPONSE_CODE_VOUCHER_CODES_DELETE_REQUEST_INVALID = '5722';

    public const string RESPONSE_CODE_VOUCHER_CODE_GENERATION_FAILED = '5726';

    public const string RESPONSE_CODE_PROMOTION_INVALID = '5730';

    public const string RESPONSE_CODE_VALIDATION = '5799';

    public const string RESPONSE_DETAILS_DISCOUNT_NOT_FOUND = 'Discount with uuid "%s" was not found.';

    public const string RESPONSE_DETAILS_VALIDATION = 'The discount could not be saved because it is invalid.';

    public const string RESPONSE_DETAILS_ERROR = '%s => %s';

    public const string RESPONSE_DETAILS_VOUCHER_CODE_NOT_FOUND = 'Voucher code "%s" was not found for discount with uuid "%s".';

    public const string RESPONSE_DETAILS_VOUCHER_CODES_DELETE_REQUEST_INVALID = 'Send either a non-empty "codes" list or "all": true, not both.';

    public const string RESPONSE_DETAILS_VOUCHER_CODE_VALIDATION = 'The voucher codes could not be generated because the request is invalid.';

    protected const string SORTABLE_FIELD_DISPLAY_NAME = 'displayName';

    protected const string SORTABLE_FIELD_IS_ACTIVE = 'isActive';

    protected const string SORTABLE_FIELD_IS_EXCLUSIVE = 'isExclusive';

    protected const string SORTABLE_FIELD_PRIORITY = 'priority';

    protected const string SORTABLE_FIELD_CREATED_AT = 'createdAt';

    protected const string SORTABLE_FIELD_CODE = 'code';

    protected const string SORTABLE_FIELD_NUMBER_OF_USES = 'numberOfUses';

    protected const string SORTABLE_FIELD_MAX_NUMBER_OF_USES = 'maxNumberOfUses';

    protected const string SORTABLE_FIELD_VOUCHER_BATCH = 'voucherBatch';

    /**
     * Specification:
     * - Returns the discount properties the `GET /discounts` collection accepts in the `sort` query parameter.
     * - A sort field outside this list is rejected with HTTP 400 and code `RESPONSE_CODE_INVALID_SORT_FIELD`.
     *
     * @api
     *
     * @return array<int, string>
     * - Every field must also be present in `\Spryker\Zed\Discount\DiscountConfig::getDiscountCollectionSortableColumnMap()`;
     *   otherwise the business layer rejects it with the same 400 / 5740 error.
     */
    public function getDiscountCollectionSortableFields(): array
    {
        return [
            static::SORTABLE_FIELD_DISPLAY_NAME,
            static::SORTABLE_FIELD_IS_ACTIVE,
            static::SORTABLE_FIELD_IS_EXCLUSIVE,
            static::SORTABLE_FIELD_PRIORITY,
            static::SORTABLE_FIELD_CREATED_AT,
        ];
    }

    /**
     * Specification:
     * - Returns the voucher code properties the `GET /discounts/{discountUuid}/voucher-codes` collection accepts in the
     *   `sort` query parameter.
     * - A sort field outside this list is rejected with HTTP 400 and code `RESPONSE_CODE_INVALID_SORT_FIELD`.
     *
     * @api
     *
     * @return array<int, string>
     * - Every field must also be present in `\Spryker\Zed\Discount\DiscountConfig::getDiscountVoucherCollectionSortableColumnMap()`;
     *   otherwise the business layer rejects it with the same 400 / 5740 error.
     */
    public function getDiscountVoucherCodeCollectionSortableFields(): array
    {
        return [
            static::SORTABLE_FIELD_CODE,
            static::SORTABLE_FIELD_NUMBER_OF_USES,
            static::SORTABLE_FIELD_MAX_NUMBER_OF_USES,
            static::SORTABLE_FIELD_CREATED_AT,
            static::SORTABLE_FIELD_VOUCHER_BATCH,
        ];
    }

    /**
     * Specification:
     * - Maps the `calculatorType` values of the `discounts` resource to the calculator plugin keys registered in
     *   `DiscountDependencyProvider::getCalculatorPlugins()`.
     * - A `calculatorType` outside this map is passed to the business layer as it is and rejected with `RESPONSE_CODE_CALCULATOR_PLUGIN_INVALID`.
     * - Extend it in the project to expose a project calculator plugin under a readable type.
     *
     * @api
     *
     * @return array<string, string>
     */
    public function getCalculatorPluginKeyByCalculatorType(): array
    {
        return [
            static::CALCULATOR_TYPE_PERCENTAGE => static::PLUGIN_CALCULATOR_PERCENTAGE,
            static::CALCULATOR_TYPE_FIXED => static::PLUGIN_CALCULATOR_FIXED,
        ];
    }

    /**
     * Specification:
     * - Maps a `discounts` resource attribute that another module contributes (through its own `discounts.resource.yml`)
     *   to the dot-separated path of the nested `DiscountConfiguratorTransfer` property that stores it,
     *   e.g. `['promotion' => 'discountCalculator.discountPromotion']`.
     * - The attribute must be an object property; on read its nested object is built from the transfer found at the path,
     *   on write a sent object is merged field by field into the transfer at the path (transfers along the path are created
     *   when missing) and an omitted or `null` attribute keeps the stored value.
     * - Extend it in the project for every module whose resource schema contributes such an attribute.
     *
     * @api
     *
     * @return array<string, string>
     */
    public function getTransferPathByAttribute(): array
    {
        return [];
    }

    /**
     * Specification:
     * - Maps the entity identifier of a business validation error (a transfer property path) to the attribute of the
     *   resource the API client sent, so an error names the attribute the client knows.
     * - An identifier outside this map is reported as it is.
     * - Extend it in the project for the identifiers of project validation rules.
     *
     * @api
     *
     * @return array<string, string>
     */
    public function getAttributeByEntityIdentifier(): array
    {
        return [
            static::ENTITY_IDENTIFIER_DISPLAY_NAME => static::ATTRIBUTE_DISPLAY_NAME,
            static::ENTITY_IDENTIFIER_CALCULATOR_PLUGIN => static::ATTRIBUTE_CALCULATOR_TYPE,
            static::ENTITY_IDENTIFIER_MONEY_VALUE_COLLECTION => static::ATTRIBUTE_FIXED_AMOUNTS,
            static::ENTITY_IDENTIFIER_COLLECTOR_QUERY_STRING => static::ATTRIBUTE_COLLECTOR,
            static::ENTITY_IDENTIFIER_DECISION_RULE_QUERY_STRING => static::ATTRIBUTE_DECISION_RULE,
            static::ENTITY_IDENTIFIER_STORES => static::ATTRIBUTE_STORES,
            static::ENTITY_IDENTIFIER_ID_DISCOUNT => static::ATTRIBUTE_DISCOUNT_UUID,
            static::ENTITY_IDENTIFIER_CODE => static::ATTRIBUTE_CODE,
        ];
    }

    /**
     * Specification:
     * - Returns the response code of each business validation glossary key the discount resources can report.
     * - A glossary key outside this map is reported with `RESPONSE_CODE_VALIDATION`.
     *
     * @api
     *
     * @return array<string, string>
     */
    public function getResponseCodeByErrorMessage(): array
    {
        return [
            SharedDiscountConfig::TRANSLATION_KEY_DISPLAY_NAME_NOT_UNIQUE => static::RESPONSE_CODE_DISPLAY_NAME_NOT_UNIQUE,
            SharedDiscountConfig::TRANSLATION_KEY_CALCULATOR_PLUGIN_INVALID => static::RESPONSE_CODE_CALCULATOR_PLUGIN_INVALID,
            SharedDiscountConfig::TRANSLATION_KEY_MONEY_AMOUNT_INVALID => static::RESPONSE_CODE_MONEY_AMOUNT_INVALID,
            SharedDiscountConfig::TRANSLATION_KEY_QUERY_STRING_INVALID => static::RESPONSE_CODE_QUERY_STRING_INVALID,
            SharedDiscountConfig::TRANSLATION_KEY_STORE_UNKNOWN => static::RESPONSE_CODE_STORE_UNKNOWN,
            SharedDiscountConfig::TRANSLATION_KEY_VOUCHER_DISCOUNT_TYPE => static::RESPONSE_CODE_VOUCHER_DISCOUNT_TYPE,
            SharedDiscountConfig::TRANSLATION_KEY_VOUCHER_CODE_NOT_FOUND => static::RESPONSE_CODE_VOUCHER_CODE_NOT_FOUND,
        ];
    }
}
