<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business;

use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\ClauseTransfer;
use Generated\Shared\Transfer\CollectedDiscountTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorResponseTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountTransfer;
use Generated\Shared\Transfer\DiscountVoucherTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;

/**
 * @method \Spryker\Zed\Discount\Business\DiscountBusinessFactory getFactory()
 */
interface DiscountFacadeInterface
{
    /**
     * Specification:
     * - Requires `CollectedDiscountTransfer.discount` to be set.
     * - Finds all discounts with voucher within the provided Store.
     * - Finds all discounts matching decision rules.
     * - Collects discountable items for each discount type.
     * - Sorts discounts by priority ASC if `spy_discount.priority` field is present at the DB, otherwise - sorts by discount amount DESC.
     * - Discounts with the same priority are sorted by discount amount DESC.
     * - If a discount does not have numeric priority set, maximum possible number will be assigned from module config (lowest priority).
     * - Applies discount to exclusive if exists.
     * - Distributes discount amount throw all discountable items.
     * - Calculates iteration price based on sum of unit prices of already applied discounts with lower priority, if `spy_discount.priority` field is present at the DB.
     * - Uses `DiscountableItemTransfer.unitPrice` otherwise.
     * - Adds discount totals to quote discount properties.
     * - Filters out discounts that cannot be applied.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function calculateDiscounts(QuoteTransfer $quoteTransfer);

    /**
     * Specification:
     * - Checks if given item transfer matches clause
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return bool
     */
    public function isItemSkuSatisfiedBy(QuoteTransfer $quoteTransfer, ItemTransfer $itemTransfer, ClauseTransfer $clauseTransfer);

    /**
     * Specification:
     * - Checks if quote grand total matches clause
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return bool
     */
    public function isQuoteGrandTotalSatisfiedBy(QuoteTransfer $quoteTransfer, ItemTransfer $itemTransfer, ClauseTransfer $clauseTransfer);

    /**
     * Specification:
     * - Checks if cart total quantity matches clause
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return bool
     */
    public function isTotalQuantitySatisfiedBy(QuoteTransfer $quoteTransfer, ItemTransfer $itemTransfer, ClauseTransfer $clauseTransfer);

    /**
     * Specification:
     * - Check quote subtotal matches clause
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return bool
     */
    public function isSubTotalSatisfiedBy(QuoteTransfer $quoteTransfer, ItemTransfer $itemTransfer, ClauseTransfer $clauseTransfer);

    /**
     * Specification:
     * - Collects all items match given sku in clause
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return array
     */
    public function collectBySku(QuoteTransfer $quoteTransfer, ClauseTransfer $clauseTransfer);

    /**
     * Specification:
     * - Checks if item quantity matches clause
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return bool
     */
    public function isItemQuantitySatisfiedBy(QuoteTransfer $quoteTransfer, ItemTransfer $itemTransfer, ClauseTransfer $clauseTransfer);

    /**
     * Specification:
     * - Collects all items match given quantity in clause
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return array<\Generated\Shared\Transfer\DiscountableItemTransfer>
     */
    public function collectByItemQuantity(QuoteTransfer $quoteTransfer, ClauseTransfer $clauseTransfer);

    /**
     * Specification:
     * - Checks if there is items matching single item price in clause
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return bool
     */
    public function isItemPriceSatisfiedBy(QuoteTransfer $quoteTransfer, ItemTransfer $itemTransfer, ClauseTransfer $clauseTransfer);

    /**
     * Specification:
     * - Collect all items matching given quantity in clause
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return array<\Generated\Shared\Transfer\DiscountableItemTransfer>
     */
    public function collectByItemPrice(QuoteTransfer $quoteTransfer, ClauseTransfer $clauseTransfer);

    /**
     * Specification:
     * - Check if current week in year matching clause
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return bool
     */
    public function isCalendarWeekSatisfiedBy(QuoteTransfer $quoteTransfer, ItemTransfer $itemTransfer, ClauseTransfer $clauseTransfer);

    /**
     * Specification:
     * - Check if current day of the week is matching clause
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return bool
     */
    public function isDayOfTheWeekSatisfiedBy(QuoteTransfer $quoteTransfer, ItemTransfer $itemTransfer, ClauseTransfer $clauseTransfer);

    /**
     * Specification:
     * - Check if current month is matching clause
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return bool
     */
    public function isMonthSatisfiedBy(QuoteTransfer $quoteTransfer, ItemTransfer $itemTransfer, ClauseTransfer $clauseTransfer);

    /**
     * Specification:
     * - Check if current time matching clause
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return bool
     */
    public function isTimeSatisfiedBy(QuoteTransfer $quoteTransfer, ItemTransfer $itemTransfer, ClauseTransfer $clauseTransfer);

    /**
     * Specification:
     * - Given type look for meta data provider
     * - Collect all available fields from all registered plugins
     *
     * @api
     *
     * @param string $type
     *
     * @return array<string>
     */
    public function getQueryStringFieldsByType($type);

    /**
     * Specification:
     * - Given type look for meta data provider
     * - Collect all available comparator operators for given fieldName
     *
     * @api
     *
     * @param string $type
     * @param string $fieldName
     *
     * @return array<string>
     */
    public function getQueryStringFieldExpressionsForField($type, $fieldName);

    /**
     * Specification:
     * - Given type look for meta data provider
     * - Get all available comparators
     *
     * @api
     *
     * @param string $type
     *
     * @return array<string>
     */
    public function getQueryStringComparatorExpressions($type);

    /**
     * Specification:
     * - Given type look for meta data provider
     * - Get boolean logical comparators
     *
     * @api
     *
     * @param string $type
     *
     * @return array<string>
     */
    public function getQueryStringLogicalComparators($type);

    /**
     * Specification:
     * - Given configure clause
     * - Select comparator operator based on clause operator, execute it and return result.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     * @param string $compareWith
     *
     * @throws \Spryker\Zed\Discount\Business\Exception\ComparatorException
     *
     * @return bool
     */
    public function queryStringCompare(ClauseTransfer $clauseTransfer, $compareWith);

    /**
     * Specification:
     * - Configure specification builder on type and query string
     * - Try building query string
     * - Store all occurred error to array and return it
     *
     * @api
     *
     * @param string $type
     * @param string $queryString
     *
     * @return array<string>
     */
    public function validateQueryStringByType($type, $queryString);

    /**
     * Specification:
     * - Hydrate discount entity from DiscountConfiguratorTransfer and persist it.
     * - If discount type is voucher create voucher pool without voucherCodes
     * - Return id of discount entity in persistence.
     *
     * @api
     *
     * @deprecated Use {@link \Spryker\Zed\Discount\Business\DiscountFacadeInterface::createDiscount()} instead.
     *
     * @param \Generated\Shared\Transfer\DiscountConfiguratorTransfer $discountConfigurator
     *
     * @return int
     */
    public function saveDiscount(DiscountConfiguratorTransfer $discountConfigurator);

    /**
     * Specification:
     * - Hydrate discount entity from DiscountConfiguratorTransfer and persist it.
     * - If discount type is voucher create/update voucher pool without voucherCodes
     * - Return bool if discount entity was persisted
     *
     * @api
     *
     * @deprecated Use {@link \Spryker\Zed\Discount\Business\DiscountFacadeInterface::updateDiscountWithValidation()} instead.
     *
     * @param \Generated\Shared\Transfer\DiscountConfiguratorTransfer $discountConfigurator
     *
     * @return bool
     */
    public function updateDiscount(DiscountConfiguratorTransfer $discountConfigurator);

    /**
     * Specification:
     * - Read idDiscount from persistence
     * - Hydrate data from entities to DiscountConfiguratorTransfer
     * - return DiscountConfiguratorTransfer
     *
     * @api
     *
     * @deprecated Use {@link findHydratedDiscountConfiguratorByIdDiscount()} instead.
     *
     * @param int $idDiscount
     *
     * @return \Generated\Shared\Transfer\DiscountConfiguratorTransfer
     */
    public function getHydratedDiscountConfiguratorByIdDiscount($idDiscount);

    /**
     * Specification:
     * - Find discount entity
     * - Change discount state to enabled/disabled.
     * - Persist
     *
     * @api
     *
     * @param int $idDiscount
     * @param bool $isActive
     *
     * @return bool
     */
    public function toggleDiscountVisibility($idDiscount, $isActive = false);

    /**
     * Specification:
     * - Find discount to which voucherCodes have to be generated
     * - Change discount state to enabled/disabled.
     * - Create pool if not created yet.
     * - Using voucher engine generate voucherCodes by provided configuration from DiscountVoucherTransfer
     * - Persist code with reference to current discount
     * - Return VoucherCreateInfoTransfer with error or success messages if there was any
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\DiscountVoucherTransfer $discountVoucherTransfer
     *
     * @return \Generated\Shared\Transfer\VoucherCreateInfoTransfer
     */
    public function saveVoucherCodes(DiscountVoucherTransfer $discountVoucherTransfer);

    /**
     * Specification:
     * - Loops over all discountable items and calculate discount price amount per item
     * - Sums each amount to to total
     * - Rounds up cent fraction for total discount amount.
     * - Returns total calculated discount amount on given discountable items
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\DiscountableItemTransfer> $discountableObjects
     * @param \Generated\Shared\Transfer\DiscountTransfer $discountTransfer
     *
     * @return int
     */
    public function calculatePercentageDiscount(array $discountableObjects, DiscountTransfer $discountTransfer);

    /**
     * Specification:
     * - Returns amount passed as parameter
     * - Returns 0 if negative number is given
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\DiscountableItemTransfer> $discountableObjects
     * @param \Generated\Shared\Transfer\DiscountTransfer $discountTransfer
     *
     * @return int
     */
    public function calculateFixedDiscount(array $discountableObjects, DiscountTransfer $discountTransfer);

    /**
     * Specification:
     * - Loops over each DiscountableItemTransfer and calculate each item price amount share from current discount total, for single item.
     * - Calculates floating point error and store it for later item, add it to next item.
     * - Calculates iteration price based on sum of unit prices of already applied discounts with lower priority, if `spy_discount.priority` field is present at the DB.
     * - Uses `DiscountableItemTransfer.unitPrice` otherwise.
     * - Stores item price share amount into DiscountableItemTransfer::originalItemCalculatedDiscounts array object reference. Points to original item.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CollectedDiscountTransfer $collectedDiscountTransfer
     *
     * @return void
     */
    public function distributeAmount(CollectedDiscountTransfer $collectedDiscountTransfer);

    /**
     * Specification:
     * - For given voucherCodes find all voucher entities with counter
     * - Reduce voucher number of uses property by 1 to indicate it's not used/released.
     *
     * @api
     *
     * @param array<string> $voucherCodes
     *
     * @return int
     */
    public function releaseUsedVoucherCodes(array $voucherCodes);

    /**
     * Specification:
     * - For given voucherCodes finds all voucher entities with counter
     * - Increments voucher number of uses property by 1.
     *
     * @api
     *
     * @param array<string> $voucherCodes
     *
     * @return int
     */
    public function useVoucherCodes(array $voucherCodes);

    /**
     * Specification:
     * - Loops over all quote items, take calculated discounts and persist them discount amount is for single item
     * - Loops over all quote expenses, take calculated discounts and persist them discount amount is for single item
     * - If there is voucher codes marks them as already used by incrementing number of uses.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return void
     */
    public function saveOrderDiscountsForCheckout(QuoteTransfer $quoteTransfer, SaveOrderTransfer $saveOrderTransfer);

    /**
     * Specification:
     *  - Hydrates sales discount data for current order to `OrderTransfer`.
     *  - If discount has voucher code, it is added to `OrderTransfer.voucherDiscounts`.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    public function hydrateOrder(OrderTransfer $orderTransfer);

    /**
     * Specification:
     * - Look for meta data provider by the given type.
     * - Collect all available value options from all registered plugins.
     *
     * @api
     *
     * @param string $type
     *
     * @return array
     */
    public function getQueryStringValueOptions($type);

    /**
     * Specification:
     *  - Checks if current currency equals to provided in decision rule
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return bool
     */
    public function isCurrencyDecisionRuleSatisfiedBy(
        QuoteTransfer $quoteTransfer,
        ItemTransfer $itemTransfer,
        ClauseTransfer $clauseTransfer
    );

    /**
     * Specification:
     *  - Check if price mode equals provided in decision rule
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return bool
     */
    public function isPriceModeDecisionRuleSatisfiedBy(
        QuoteTransfer $quoteTransfer,
        ItemTransfer $itemTransfer,
        ClauseTransfer $clauseTransfer
    );

    /**
     * Specification:
     * - Checks discount cart changes
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $resultQuoteTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $sourceQuoteTransfer
     *
     * @return void
     */
    public function checkDiscountChanges(QuoteTransfer $resultQuoteTransfer, QuoteTransfer $sourceQuoteTransfer): void;

    /**
     * Specification:
     * - Finds discount by id.
     * - Hydrates data from entity to DiscountConfiguratorTransfer.
     * - Returns NULL if discount doesn't exist.
     *
     * @api
     *
     * @param int $idDiscount
     *
     * @return \Generated\Shared\Transfer\DiscountConfiguratorTransfer|null
     */
    public function findHydratedDiscountConfiguratorByIdDiscount(int $idDiscount): ?DiscountConfiguratorTransfer;

    /**
     * Specification:
     * - Validates voucher discounts on Max Usage in Quote.
     * - Returns `true` if all used vouchers are under the usage limit.
     * - Otherwise it returns `false` and adds messages to `$checkoutResponseTransfer`.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\CheckoutResponseTransfer $checkoutResponseTransfer
     *
     * @return bool
     */
    public function validateVoucherDiscountsMaxUsage(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): bool;

    /**
     * Specification:
     * - Sets voucher discount to the quote if the code hasn't been added already.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param string $cartCode
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function addCartCode(QuoteTransfer $quoteTransfer, string $cartCode): QuoteTransfer;

    /**
     * Specification:
     * - Removes matching applied and not applied voucher discount from quote.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param string $cartCode
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function removeCartCode(QuoteTransfer $quoteTransfer, string $cartCode): QuoteTransfer;

    /**
     * Specification:
     * - Clears all applied and not applied voucher codes from the quote.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function clearCartCodes(QuoteTransfer $quoteTransfer): QuoteTransfer;

    /**
     * Specification:
     * - Executes {@link \Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountVoucherApplyCheckerStrategyPluginInterface} plugin stack.
     * - Returns voucher apply success message in case the given voucher code has been applied successfully.
     * - Returns voucher apply failed message in case the given voucher code hasn't been applied successfully.
     * - Returns an empty failed message if code is not relevant.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param string $cartCode
     *
     * @return \Generated\Shared\Transfer\MessageTransfer|null
     */
    public function findOperationResponseMessage(QuoteTransfer $quoteTransfer, string $cartCode): ?MessageTransfer;

    /**
     * Specification:
     * - Requires `DiscountConfigurator.discountGeneral`, `DiscountConfigurator.discountCalculator`, `DiscountConfigurator.discountCondition`,
     *   `DiscountConfigurator.discountGeneral.validFrom`, `DiscountConfigurator.discountGeneral.validTo`, `DiscountConfigurator.discountGeneral.displayName`,
     *   `DiscountConfigurator.discountGeneral.discountType`, `DiscountConfigurator.discountCalculator.calculatorPlugin` to be set.
     * - Validates `DiscountConfigurator.discountGeneral.validFrom` and `DiscountConfigurator.discountGeneral.validTo` are valid dates.
     * - Validates that `DiscountConfigurator.discountGeneral.validFrom` date is before `DiscountConfigurator.discountGeneral.validTo`.
     * - Hydrates discount entity from `DiscountConfiguratorTransfer` and persists it.
     * - Creates discount store relationships.
     * - If discount type is voucher, creates voucher pool without voucherCodes.
     * - If discount calculator input type is `CALCULATOR_MONEY_INPUT_TYPE`, creates discount amounts.
     * - Executes {@link \Spryker\Zed\Discount\Dependency\Plugin\DiscountPostCreatePluginInterface} plugins stack.
     * - Returns "DiscountConfiguratorResponseTransfer.isSuccessful=true" on successful validation and entity persistence.
     * - Returns "isSuccessful=false" with error messages otherwise.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\DiscountConfiguratorTransfer $discountConfigurator
     *
     * @return \Generated\Shared\Transfer\DiscountConfiguratorResponseTransfer
     */
    public function createDiscount(DiscountConfiguratorTransfer $discountConfigurator): DiscountConfiguratorResponseTransfer;

    /**
     * Specification:
     * - Requires `DiscountConfigurator.discountGeneral`, `DiscountConfigurator.discountCalculator`, `DiscountConfigurator.discountCondition`,
     *   `DiscountConfigurator.discountGeneral.idDiscount`, `DiscountConfigurator.discountGeneral.validFrom`, `DiscountConfigurator.discountGeneral.validTo`,
     *   `DiscountConfigurator.discountGeneral.displayName`, `DiscountConfigurator.discountGeneral.discountType`,
     *   `DiscountConfigurator.discountCalculator.calculatorPlugin` to be set.
     * - Validates `DiscountConfigurator.discountGeneral.validFrom` and `DiscountConfigurator.discountGeneral.validTo` are valid dates.
     * - Validates that `DiscountConfigurator.discountGeneral.validFrom` date is before `DiscountConfigurator.discountGeneral.validTo`.
     * - Validates discount with id `DiscountConfigurator.discountGeneral.idDiscount` exists.
     * - Hydrates discount entity from `DiscountConfiguratorTransfer` and persists it.
     * - Updates discount store relationships.
     * - If discount type is voucher, creates/updates voucher pool without voucherCodes.
     * - If discount calculator input type is `CALCULATOR_MONEY_INPUT_TYPE`, updates discount amounts.
     * - Executes {@link \Spryker\Zed\Discount\Dependency\Plugin\DiscountPostUpdatePluginInterface} plugins stack.
     * - Returns "DiscountConfiguratorResponseTransfer.isSuccessful=true" on successful validation and entity persistence.
     * - Returns "DiscountConfiguratorResponseTransfer.isSuccessful=false" with error messages otherwise.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\DiscountConfiguratorTransfer $discountConfigurator
     *
     * @return \Generated\Shared\Transfer\DiscountConfiguratorResponseTransfer
     */
    public function updateDiscountWithValidation(DiscountConfiguratorTransfer $discountConfigurator): DiscountConfiguratorResponseTransfer;
}
