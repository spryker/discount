<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\Filter;

use ArrayObject;
use Generated\Shared\Transfer\CalculatedDiscountTransfer;
use Generated\Shared\Transfer\CollectedDiscountTransfer;
use Generated\Shared\Transfer\DiscountableItemTransfer;
use Generated\Shared\Transfer\DiscountTransfer;

class CollectedDiscountItemFilter implements CollectedDiscountItemFilterInterface
{
    protected const int DEFAULT_DISCOUNT_AMOUNT = 0;

    protected const string IS_DISCOUNT_APPLICABLE = 'IS_DISCOUNT_APPLICABLE';

    protected const string DISCOUNT_AMOUNT_TO_REDUCE = 'DISCOUNT_AMOUNT_TO_REDUCE';

    /**
     * @var array<string, int>
     */
    protected $remainingUnitPricesByGroupKeys;

    /**
     * @param array<\Generated\Shared\Transfer\CollectedDiscountTransfer> $collectedDiscountTransfers
     *
     * @return array<\Generated\Shared\Transfer\CollectedDiscountTransfer>
     */
    public function filter(array $collectedDiscountTransfers): array
    {
        $this->remainingUnitPricesByGroupKeys = [];
        $filteredCollectedDiscountTransfers = [];

        foreach ($collectedDiscountTransfers as $collectedDiscountTransfer) {
            $filteredCollectedDiscountTransfer = $this->filterCollectedDiscountWithItems($collectedDiscountTransfer);

            if ($this->isCollectedDiscountValid($filteredCollectedDiscountTransfer)) {
                $filteredCollectedDiscountTransfers[] = $filteredCollectedDiscountTransfer;
            }
        }

        return $filteredCollectedDiscountTransfers;
    }

    protected function filterCollectedDiscountWithItems(CollectedDiscountTransfer $collectedDiscountTransfer): CollectedDiscountTransfer
    {
        $discountTransfer = $collectedDiscountTransfer->getDiscount();
        if (!$discountTransfer) {
            return $collectedDiscountTransfer;
        }

        $totalDiscountAmountToReduce = static::DEFAULT_DISCOUNT_AMOUNT;

        $validDiscountableItemTransfers = [];
        foreach ($collectedDiscountTransfer->getDiscountableItems() as $discountableItemTransfer) {
            $processResult = $this->processDiscountableItem($discountableItemTransfer, $discountTransfer);

            $totalDiscountAmountToReduce += $processResult[static::DISCOUNT_AMOUNT_TO_REDUCE];

            if ($processResult[static::IS_DISCOUNT_APPLICABLE]) {
                $validDiscountableItemTransfers[] = $discountableItemTransfer;
            }
        }

        $collectedDiscountTransfer->setDiscountableItems(new ArrayObject($validDiscountableItemTransfers));

        $discountTransfer->setAmount($discountTransfer->getAmount() - $totalDiscountAmountToReduce);
        $collectedDiscountTransfer->setDiscount($discountTransfer);

        return $collectedDiscountTransfer;
    }

    /**
     * @return array<string, mixed>
     */
    protected function processDiscountableItem(DiscountableItemTransfer $discountableItemTransfer, DiscountTransfer $discountTransfer): array
    {
        $discountableItemProcessResult = [
            static::IS_DISCOUNT_APPLICABLE => true,
            static::DISCOUNT_AMOUNT_TO_REDUCE => static::DEFAULT_DISCOUNT_AMOUNT,
        ];

        $originalItemTransfer = $discountableItemTransfer->getOriginalItem();
        if (!$originalItemTransfer) {
            return $discountableItemProcessResult;
        }

        if (!$originalItemTransfer->getId()) {
            return $discountableItemProcessResult;
        }

        $originalItemGroupKey = $originalItemTransfer->getGroupKey();
        if (!$originalItemGroupKey) {
            return $discountableItemProcessResult;
        }

        if (!isset($this->remainingUnitPricesByGroupKeys[$originalItemGroupKey])) {
            $this->remainingUnitPricesByGroupKeys[$originalItemGroupKey] = $discountableItemTransfer->getUnitPrice() * $discountableItemTransfer->getQuantity();
        }

        foreach ($discountableItemTransfer->getOriginalItemCalculatedDiscounts() as $originalItemCalculatedDiscountTransfer) {
            $discountableItemProcessResult = $this->processDiscountableItemCalculatedDiscount(
                $originalItemCalculatedDiscountTransfer,
                $discountTransfer,
                $originalItemGroupKey,
                $discountableItemProcessResult,
            );
        }

        return $discountableItemProcessResult;
    }

    /**
     * @param array<string, mixed> $discountableItemProcessResult
     *
     * @return array<string, mixed>
     */
    protected function processDiscountableItemCalculatedDiscount(
        CalculatedDiscountTransfer $originalItemCalculatedDiscountTransfer,
        DiscountTransfer $discountTransfer,
        string $originalItemGroupKey,
        array $discountableItemProcessResult
    ): array {
        if ($originalItemCalculatedDiscountTransfer->getIdDiscount() !== $discountTransfer->getIdDiscount()) {
            return $discountableItemProcessResult;
        }

        $discountAmount = $originalItemCalculatedDiscountTransfer->getUnitAmount();

        if (!$this->remainingUnitPricesByGroupKeys[$originalItemGroupKey]) {
            $discountableItemProcessResult[static::IS_DISCOUNT_APPLICABLE] = false;
            $discountableItemProcessResult[static::DISCOUNT_AMOUNT_TO_REDUCE] += $discountAmount;

            return $discountableItemProcessResult;
        }

        if ($discountAmount <= $this->remainingUnitPricesByGroupKeys[$originalItemGroupKey]) {
            $this->remainingUnitPricesByGroupKeys[$originalItemGroupKey] -= $discountAmount;

            return $discountableItemProcessResult;
        }

        $discountableItemProcessResult[static::DISCOUNT_AMOUNT_TO_REDUCE] += $discountAmount - $this->remainingUnitPricesByGroupKeys[$originalItemGroupKey];
        $this->remainingUnitPricesByGroupKeys[$originalItemGroupKey] = static::DEFAULT_DISCOUNT_AMOUNT;

        return $discountableItemProcessResult;
    }

    protected function isCollectedDiscountValid(CollectedDiscountTransfer $collectedDiscountTransfer): bool
    {
        if (!$collectedDiscountTransfer->getDiscountableItems()->count()) {
            return false;
        }

        if (!$collectedDiscountTransfer->getDiscount()) {
            return true;
        }

        return $collectedDiscountTransfer->getDiscount()->getAmount() > static::DEFAULT_DISCOUNT_AMOUNT;
    }
}
