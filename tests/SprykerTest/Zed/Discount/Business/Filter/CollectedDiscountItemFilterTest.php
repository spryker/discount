<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\Filter;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CalculatedDiscountTransfer;
use Generated\Shared\Transfer\CollectedDiscountTransfer;
use Generated\Shared\Transfer\DiscountableItemTransfer;
use Generated\Shared\Transfer\DiscountTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Spryker\Zed\Discount\Business\Filter\CollectedDiscountItemFilter;
use SprykerTest\Zed\Discount\DiscountBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group Filter
 * @group CollectedDiscountItemFilterTest
 * Add your own group annotations below this line
 */
class CollectedDiscountItemFilterTest extends Unit
{
    protected const int DISCOUNT_ID = 0;

    protected const int ITEM_ID = 1;

    protected const string GROUP_KEY = 'group-key-1';

    protected const string GROUP_KEY_SECOND_CART_LINE = 'group-key-2';

    protected const int DISCOUNT_AMOUNT_PER_CART_LINE = 4000;

    protected const int ITEM_QUANTITY = 5;

    protected const int ITEM_UNIT_PRICE = 1000;

    protected const int DISCOUNT_TOTAL_AMOUNT = 2500;

    protected DiscountBusinessTester $tester;

    public function testFilterAppliesDiscountWhenOriginalItemCalculatedDiscountAmountExceedItemUnitPrice(): void
    {
        // Arrange
        $discountTransfer = (new DiscountTransfer())
            ->setAmount(static::DISCOUNT_TOTAL_AMOUNT)
            ->setIdDiscount(static::DISCOUNT_ID);

        $itemTransfer = (new ItemTransfer())
            ->setId(static::ITEM_ID)
            ->setGroupKey(static::GROUP_KEY);

        $discountableItemTransfer = (new DiscountableItemTransfer())
            ->setOriginalItem($itemTransfer)
            ->setUnitPrice(static::ITEM_UNIT_PRICE)
            ->setQuantity(static::ITEM_QUANTITY);

        $calculatedDiscountTransfer = (new CalculatedDiscountTransfer())
            ->setIdDiscount($discountTransfer->getIdDiscount())
            ->setUnitAmount($discountTransfer->getAmount() / $discountableItemTransfer->getQuantity());

        for ($i = 0; $i < static::ITEM_QUANTITY; $i++) {
            $discountableItemTransfer->addOriginalItemCalculatedDiscounts($calculatedDiscountTransfer);
        }

        $collectedDiscountItemFilter = new CollectedDiscountItemFilter();
        $collectedDiscountTransfers = [
            (new CollectedDiscountTransfer())
                ->setDiscount($discountTransfer)
                ->addDiscountableItems($discountableItemTransfer),
        ];

        // Act
        $collectedDiscountTransfers = $collectedDiscountItemFilter->filter($collectedDiscountTransfers);

        // Assert
        $this->assertCount(1, $collectedDiscountTransfers);
        $this->assertCount(1, $collectedDiscountTransfers[0]->getDiscountableItems());
    }

    public function testFilterCapsEachCartLineAgainstItsOwnPriceWhenSeveralCartLinesShareTheSameProduct(): void
    {
        // Arrange
        $discountTransfer = (new DiscountTransfer())
            ->setAmount(static::DISCOUNT_AMOUNT_PER_CART_LINE * 2)
            ->setIdDiscount(static::DISCOUNT_ID);

        $collectedDiscountItemFilter = new CollectedDiscountItemFilter();
        $collectedDiscountTransfers = [
            (new CollectedDiscountTransfer())
                ->setDiscount($discountTransfer)
                ->addDiscountableItems($this->createDiscountableItemTransfer(static::GROUP_KEY, static::ITEM_ID))
                ->addDiscountableItems($this->createDiscountableItemTransfer(static::GROUP_KEY_SECOND_CART_LINE, static::ITEM_ID)),
        ];

        // Act
        $collectedDiscountTransfers = $collectedDiscountItemFilter->filter($collectedDiscountTransfers);

        // Assert
        $this->assertCount(1, $collectedDiscountTransfers);
        $this->assertCount(
            2,
            $collectedDiscountTransfers[0]->getDiscountableItems(),
            'Every cart line has to be capped against its own price, not against the price of the first cart line carrying the same product.',
        );
        $this->assertSame(
            static::DISCOUNT_AMOUNT_PER_CART_LINE * 2,
            $collectedDiscountTransfers[0]->getDiscountOrFail()->getAmount(),
            'The discount fits into the price of each cart line, so the reported discount amount must stay untouched.',
        );
    }

    public function testFilterDoesNotCapItemsThatCarryNoProductId(): void
    {
        // Arrange
        $exceedingDiscountAmount = static::ITEM_UNIT_PRICE * static::ITEM_QUANTITY * 2;

        $discountTransfer = (new DiscountTransfer())
            ->setAmount($exceedingDiscountAmount)
            ->setIdDiscount(static::DISCOUNT_ID);

        // An order item hydrated from `spy_sales_order_item` carries a group key but no product id.
        $discountableItemTransfer = $this->createDiscountableItemTransfer(
            static::GROUP_KEY,
            null,
            intdiv($exceedingDiscountAmount, static::ITEM_QUANTITY),
        );

        $collectedDiscountItemFilter = new CollectedDiscountItemFilter();
        $collectedDiscountTransfers = [
            (new CollectedDiscountTransfer())
                ->setDiscount($discountTransfer)
                ->addDiscountableItems($discountableItemTransfer),
        ];

        // Act
        $collectedDiscountTransfers = $collectedDiscountItemFilter->filter($collectedDiscountTransfers);

        // Assert
        $this->assertCount(1, $collectedDiscountTransfers);
        $this->assertSame(
            $exceedingDiscountAmount,
            $collectedDiscountTransfers[0]->getDiscountOrFail()->getAmount(),
            'Order recalculation was never capped by this filter and must stay that way, so that a refund cannot re-cap what an order was charged with.',
        );
    }

    protected function createDiscountableItemTransfer(
        string $groupKey,
        ?int $idProduct,
        ?int $unitDiscountAmount = null
    ): DiscountableItemTransfer {
        $unitDiscountAmount ??= intdiv(static::DISCOUNT_AMOUNT_PER_CART_LINE, static::ITEM_QUANTITY);

        $itemTransfer = (new ItemTransfer())
            ->setId($idProduct)
            ->setGroupKey($groupKey);

        $discountableItemTransfer = (new DiscountableItemTransfer())
            ->setOriginalItem($itemTransfer)
            ->setUnitPrice(static::ITEM_UNIT_PRICE)
            ->setQuantity(static::ITEM_QUANTITY);

        for ($unit = 0; $unit < static::ITEM_QUANTITY; $unit++) {
            $discountableItemTransfer->addOriginalItemCalculatedDiscounts(
                (new CalculatedDiscountTransfer())
                    ->setIdDiscount(static::DISCOUNT_ID)
                    ->setQuantity(1)
                    ->setUnitAmount($unitDiscountAmount)
                    ->setSumAmount($unitDiscountAmount),
            );
        }

        return $discountableItemTransfer;
    }
}
