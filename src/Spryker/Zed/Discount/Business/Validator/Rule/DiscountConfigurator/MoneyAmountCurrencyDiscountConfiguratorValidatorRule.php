<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator;

use ArrayObject;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\ErrorCollectionTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Spryker\Shared\Discount\DiscountConfig;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Discount\Dependency\Facade\DiscountToCurrencyInterface;
use Spryker\Zed\Discount\Dependency\Facade\DiscountToStoreFacadeInterface;
use Spryker\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginWithAmountInputTypeInterface;

class MoneyAmountCurrencyDiscountConfiguratorValidatorRule implements DiscountConfiguratorValidatorRuleInterface
{
    public const string ENTITY_IDENTIFIER_MONEY_VALUE_COLLECTION = 'discountCalculator.moneyValueCollection';

    protected const string ENTITY_IDENTIFIER_MONEY_VALUE_PATTERN = '%s.%d';

    /**
     * @param array<string, \Spryker\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginInterface> $calculatorPlugins
     */
    public function __construct(
        protected array $calculatorPlugins,
        protected DiscountToStoreFacadeInterface $storeFacade,
        protected DiscountToCurrencyInterface $currencyFacade
    ) {
    }

    public function validate(DiscountConfiguratorTransfer $discountConfiguratorTransfer): ErrorCollectionTransfer
    {
        $errorCollectionTransfer = new ErrorCollectionTransfer();
        $discountCalculatorTransfer = $discountConfiguratorTransfer->getDiscountCalculator();

        if ($discountCalculatorTransfer === null || !$this->isMoneyInputCalculator($discountCalculatorTransfer->getCalculatorPlugin())) {
            return $errorCollectionTransfer;
        }

        $filledMoneyValueTransfers = $this->filterFilledMoneyValues($discountCalculatorTransfer->getMoneyValueCollection());

        if ($filledMoneyValueTransfers === []) {
            return $errorCollectionTransfer->addError(
                (new ErrorTransfer())
                    ->setMessage(DiscountConfig::TRANSLATION_KEY_MONEY_AMOUNT_INVALID)
                    ->setEntityIdentifier(static::ENTITY_IDENTIFIER_MONEY_VALUE_COLLECTION),
            );
        }

        $allowedCurrencyIsoCodes = $this->findCurrencyIsoCodesOfSelectedStores($discountConfiguratorTransfer);

        foreach ($filledMoneyValueTransfers as $index => $moneyValueTransfer) {
            $currencyIsoCode = $this->findCurrencyIsoCode($moneyValueTransfer);

            if ($currencyIsoCode !== null && ($allowedCurrencyIsoCodes === null || in_array($currencyIsoCode, $allowedCurrencyIsoCodes, true))) {
                continue;
            }

            $errorCollectionTransfer->addError(
                (new ErrorTransfer())
                    ->setMessage(DiscountConfig::TRANSLATION_KEY_MONEY_AMOUNT_INVALID)
                    ->setEntityIdentifier(
                        sprintf(static::ENTITY_IDENTIFIER_MONEY_VALUE_PATTERN, static::ENTITY_IDENTIFIER_MONEY_VALUE_COLLECTION, $index),
                    ),
            );
        }

        return $errorCollectionTransfer;
    }

    protected function isMoneyInputCalculator(?string $calculatorPlugin): bool
    {
        $plugin = $this->calculatorPlugins[$calculatorPlugin] ?? null;

        if (!$plugin instanceof DiscountCalculatorPluginWithAmountInputTypeInterface) {
            return false;
        }

        return $plugin->getInputType() === DiscountConstants::CALCULATOR_MONEY_INPUT_TYPE;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\MoneyValueTransfer> $moneyValueTransfers
     *
     * @return array<int, \Generated\Shared\Transfer\MoneyValueTransfer> Keyed by the position in the submitted collection.
     */
    protected function filterFilledMoneyValues(ArrayObject $moneyValueTransfers): array
    {
        $filledMoneyValueTransfers = [];

        foreach ($moneyValueTransfers as $index => $moneyValueTransfer) {
            if ($moneyValueTransfer->getNetAmount() === null && $moneyValueTransfer->getGrossAmount() === null) {
                continue;
            }

            $filledMoneyValueTransfers[$index] = $moneyValueTransfer;
        }

        return $filledMoneyValueTransfers;
    }

    protected function findCurrencyIsoCode(MoneyValueTransfer $moneyValueTransfer): ?string
    {
        $currencyIsoCode = $moneyValueTransfer->getCurrency()?->getCode();

        if ($currencyIsoCode !== null || $moneyValueTransfer->getFkCurrency() === null) {
            return $currencyIsoCode;
        }

        return $this->currencyFacade->getByIdCurrency($moneyValueTransfer->getFkCurrencyOrFail())->getCode();
    }

    /**
     * @return array<int, string>|null Null when no store is selected, so every currency is accepted.
     */
    protected function findCurrencyIsoCodesOfSelectedStores(DiscountConfiguratorTransfer $discountConfiguratorTransfer): ?array
    {
        $storeRelationTransfer = $discountConfiguratorTransfer->getDiscountGeneral()?->getStoreRelation();
        $selectedStoreIds = $storeRelationTransfer?->getIdStores() ?? [];
        $selectedStoreNames = [];

        foreach ($storeRelationTransfer?->getStores() ?? [] as $storeTransfer) {
            $selectedStoreNames[] = $storeTransfer->getName();

            if ($storeTransfer->getIdStore() !== null) {
                $selectedStoreIds[] = $storeTransfer->getIdStore();
            }
        }

        if ($selectedStoreIds === [] && $selectedStoreNames === []) {
            return null;
        }

        $currencyIsoCodes = [];

        foreach ($this->storeFacade->getAllStores() as $storeTransfer) {
            if (
                !in_array($storeTransfer->getIdStore(), $selectedStoreIds, true)
                && !in_array($storeTransfer->getName(), $selectedStoreNames, true)
            ) {
                continue;
            }

            $currencyIsoCodes[] = $storeTransfer->getAvailableCurrencyIsoCodes();
        }

        return $currencyIsoCodes === [] ? [] : array_values(array_unique(array_merge(...$currencyIsoCodes)));
    }
}
