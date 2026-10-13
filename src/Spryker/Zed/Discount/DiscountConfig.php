<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount;

use Orm\Zed\Discount\Persistence\Map\SpyDiscountTableMap;
use Orm\Zed\Discount\Persistence\Map\SpyDiscountVoucherTableMap;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class DiscountConfig extends AbstractBundleConfig
{
    /**
     * @api
     *
     * @var int
     */
    public const DEFAULT_VOUCHER_CODE_LENGTH = 6;

    /**
     * @api
     *
     * @var int
     */
    public const DEFAULT_MINIMUM_ITEM_AMOUNT = 1;

    /**
     * @api
     *
     * @var string
     */
    public const KEY_VOUCHER_CODE_CONSONANTS = 'consonants';

    /**
     * @api
     *
     * @var string
     */
    public const KEY_VOUCHER_CODE_VOWELS = 'vowels';

    /**
     * @api
     *
     * @var string
     */
    public const KEY_VOUCHER_CODE_NUMBERS = 'numbers';

    /**
     * @var string
     */
    protected const REDIRECT_URL_DEFAULT = '/discount/index/list';

    /**
     * @var int
     */
    protected const PRIORITY_MIN_VALUE = 1;

    /**
     * @var int
     */
    protected const PRIORITY_MAX_VALUE = 9999;

    /**
     * @var int
     */
    protected const VOUCHER_CODES_QUANTITY_MIN_VALUE = 1;

    /**
     * @var int
     */
    protected const VOUCHER_CODES_QUANTITY_MAX_VALUE = 14000;

    /**
     * @var bool
     */
    protected const IS_MONEY_COLLECTION_FORM_TYPE_PLUGIN_ENABLED = false;

    /**
     * @see https://en.wikipedia.org/wiki/Year_2038_problem
     *
     * @var string
     */
    protected const MAX_ALLOWED_DATETIME = '2038-01-19 03:14:07';

    protected const bool BASE64_ENCODE_QUERY_STRING_FIELD_ENABLED = true;

    /**
     * @uses \Spryker\Glue\Discount\DiscountConfig::getDiscountCollectionSortableFields()
     * @uses \Spryker\Glue\Discount\DiscountConfig::getDiscountVoucherCodeCollectionSortableFields()
     */
    protected const string SORTABLE_FIELD_DISPLAY_NAME = 'displayName';

    protected const string SORTABLE_FIELD_IS_ACTIVE = 'isActive';

    protected const string SORTABLE_FIELD_IS_EXCLUSIVE = 'isExclusive';

    protected const string SORTABLE_FIELD_PRIORITY = 'priority';

    protected const string SORTABLE_FIELD_CREATED_AT = 'createdAt';

    protected const string SORTABLE_FIELD_CODE = 'code';

    protected const string SORTABLE_FIELD_NUMBER_OF_USES = 'numberOfUses';

    protected const string SORTABLE_FIELD_MAX_NUMBER_OF_USES = 'maxNumberOfUses';

    protected const string SORTABLE_FIELD_VOUCHER_BATCH = 'voucherBatch';

    protected const string COALESCE_ZERO_PATTERN = 'COALESCE(%s, 0)';

    protected const int VOUCHER_CODE_LENGTH_MIN_VALUE = 3;

    protected const int VOUCHER_CODE_LENGTH_MAX_VALUE = 10;

    /**
     * @api
     *
     * @return int
     */
    public function getVoucherCodeLength()
    {
        return static::DEFAULT_VOUCHER_CODE_LENGTH;
    }

    /**
     * @api
     *
     * @return array<string, array<mixed>>
     */
    public function getVoucherCodeCharacters()
    {
        return [
            static::KEY_VOUCHER_CODE_CONSONANTS => [
                'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z',
            ],
            static::KEY_VOUCHER_CODE_VOWELS => [
                'a', 'e', 'u',
            ],
            static::KEY_VOUCHER_CODE_NUMBERS => [
                1, 2, 3, 4, 5, 6, 7, 8, 9,
            ],
        ];
    }

    /**
     * @api
     *
     * @return int
     */
    public function getAllowedCodeCharactersLength()
    {
        /** @var int $charactersLength */
        $charactersLength = array_reduce($this->getVoucherCodeCharacters(), function ($length, $items) {
            $length += count($items);

            return $length;
        });

        return $charactersLength;
    }

    /**
     * @api
     *
     * @return string
     */
    public function getVoucherPoolTemplateReplacementString()
    {
        return '[code]';
    }

    /**
     * @api
     *
     * @return string
     */
    public function getDefaultRedirectUrl(): string
    {
        return static::REDIRECT_URL_DEFAULT;
    }

    /**
     * Specification:
     * - Defines the minimum numeric value for priority (the highest possible priority).
     *
     * @api
     *
     * @return int
     */
    public function getPriorityMinValue(): int
    {
        return static::PRIORITY_MIN_VALUE;
    }

    /**
     * Specification:
     * - Defines the maximum numeric value for priority (the lowest possible priority).
     *
     * @api
     *
     * @return int
     */
    public function getPriorityMaxValue(): int
    {
        return static::PRIORITY_MAX_VALUE;
    }

    /**
     * Specification:
     * - Defines the minimum quantity of generated voucher codes.
     *
     * @api
     *
     * @return int
     */
    public function getVoucherCodesQuantityMinValue(): int
    {
        return static::VOUCHER_CODES_QUANTITY_MIN_VALUE;
    }

    /**
     * Specification:
     * - Defines the maximum quantity of generated voucher codes.
     *
     * @api
     *
     * @return int
     */
    public function getVoucherCodesQuantityMaxValue(): int
    {
        return static::VOUCHER_CODES_QUANTITY_MAX_VALUE;
    }

    /**
     * Specification:
     * - Enables using money collection form type plugin.
     *
     * @api
     *
     * @return bool
     */
    public function isMoneyCollectionFormTypePluginEnabled(): bool
    {
        return static::IS_MONEY_COLLECTION_FORM_TYPE_PLUGIN_ENABLED;
    }

    /**
     * Specification:
     * - Defines the maximum datetime value that can be used for timestamps columns.
     *
     * @api
     *
     * @return string
     */
    public function getMaxAllowedDatetime(): string
    {
        return static::MAX_ALLOWED_DATETIME;
    }

    /**
     * Specification:
     * - Enables/disables query string fields encoding.
     * Enable this if you are experiencing 403 errors due to AWS WAF protection.
     *
     * @api
     *
     * @return bool
     */
    public function isBase64EncodeQueryStringFieldEnabled(): bool
    {
        return static::BASE64_ENCODE_QUERY_STRING_FIELD_ENABLED;
    }

    /**
     * Specification:
     * - Defines the minimum length of a randomly generated voucher code.
     *
     * @api
     */
    public function getVoucherCodeLengthMinValue(): int
    {
        return static::VOUCHER_CODE_LENGTH_MIN_VALUE;
    }

    /**
     * Specification:
     * - Defines the maximum length of a randomly generated voucher code.
     *
     * @api
     */
    public function getVoucherCodeLengthMaxValue(): int
    {
        return static::VOUCHER_CODE_LENGTH_MAX_VALUE;
    }

    /**
     * Specification:
     * - Enables filtering discounts by the `uuid` column of the `spy_discount` table (the `DiscountUuid` schema folder).
     * - When disabled, a discount query with a uuid condition throws `DiscountUuidNotEnabledException`.
     * - Consumers relying on the discount UUID, such as the Backend API resources, require this to be enabled.
     *
     * @api
     */
    public function isDiscountUuidEnabled(): bool
    {
        return false;
    }

    /**
     * Specification:
     * - Maps the sortable fields of the discount collection to the database columns or SQL expressions they sort by.
     * - A sort field outside this map makes `DiscountFacadeInterface::getDiscountConfiguratorCollection()` throw
     *   `DiscountSortFieldNotSupportedException`; extend this map together with the Glue allow-list to add a field.
     *
     * @api
     *
     * @return array<string, string>
     */
    public function getDiscountCollectionSortableColumnMap(): array
    {
        return [
            static::SORTABLE_FIELD_DISPLAY_NAME => SpyDiscountTableMap::COL_DISPLAY_NAME,
            static::SORTABLE_FIELD_IS_ACTIVE => SpyDiscountTableMap::COL_IS_ACTIVE,
            static::SORTABLE_FIELD_IS_EXCLUSIVE => SpyDiscountTableMap::COL_IS_EXCLUSIVE,
            static::SORTABLE_FIELD_PRIORITY => SpyDiscountTableMap::COL_PRIORITY,
            static::SORTABLE_FIELD_CREATED_AT => SpyDiscountTableMap::COL_CREATED_AT,
        ];
    }

    /**
     * Specification:
     * - Maps the sortable fields of the voucher code collection to the database columns or SQL expressions they sort by.
     * - Nullable counters sort through `COALESCE(column, 0)`, so MySQL and PostgreSQL order them the same way.
     * - A sort field outside this map makes `DiscountFacadeInterface::getDiscountVoucherCollection()` throw
     *   `DiscountSortFieldNotSupportedException`; extend this map together with the Glue allow-list to add a field.
     *
     * @api
     *
     * @return array<string, string>
     */
    public function getDiscountVoucherCollectionSortableColumnMap(): array
    {
        return [
            static::SORTABLE_FIELD_CODE => SpyDiscountVoucherTableMap::COL_CODE,
            static::SORTABLE_FIELD_NUMBER_OF_USES => sprintf(static::COALESCE_ZERO_PATTERN, SpyDiscountVoucherTableMap::COL_NUMBER_OF_USES),
            static::SORTABLE_FIELD_MAX_NUMBER_OF_USES => sprintf(static::COALESCE_ZERO_PATTERN, SpyDiscountVoucherTableMap::COL_MAX_NUMBER_OF_USES),
            static::SORTABLE_FIELD_CREATED_AT => SpyDiscountVoucherTableMap::COL_CREATED_AT,
            static::SORTABLE_FIELD_VOUCHER_BATCH => SpyDiscountVoucherTableMap::COL_VOUCHER_BATCH,
        ];
    }
}
