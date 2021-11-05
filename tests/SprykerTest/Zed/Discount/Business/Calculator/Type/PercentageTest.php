<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\Calculator\Type;

use ArrayObject;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\DiscountableItemTransfer;
use Generated\Shared\Transfer\DiscountTransfer;
use Spryker\Zed\Discount\Business\Calculator\Type\PercentageType;
use Spryker\Zed\Discount\Business\Exception\CalculatorException;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group Calculator
 * @group Type
 * @group PercentageTest
 * Add your own group annotations below this line
 */
class PercentageTest extends Unit
{
    /**
     * @var int
     */
    public const ITEM_GROSS_PRICE_1000 = 1000;

    /**
     * @var int
     */
    public const DISCOUNT_PERCENTAGE_10 = 1000;

    /**
     * @var int
     */
    public const DISCOUNT_PERCENTAGE_100 = 10000;

    /**
     * @var int
     */
    public const DISCOUNT_PERCENTAGE_200 = 20000;

    /**
     * @return void
     */
    public function testCalculatePercentageShouldNotGrantDiscountsHigherThanHundredPercent(): void
    {
        $items = $this->getDiscountableItems(
            [
                static::ITEM_GROSS_PRICE_1000,
                static::ITEM_GROSS_PRICE_1000,
                static::ITEM_GROSS_PRICE_1000,
            ],
        );

        $calculator = new PercentageType();
        $discountTransfer = (new DiscountTransfer())->setAmount(static::DISCOUNT_PERCENTAGE_200);
        $discountAmount = $calculator->calculateDiscount($items, $discountTransfer);

        $this->assertSame(static::ITEM_GROSS_PRICE_1000 * 3, $discountAmount);
    }

    /**
     * @return void
     */
    public function testCalculatePercentageShouldNotGrantDiscountsLessThanZeroPercent(): void
    {
        $items = $this->getDiscountableItems(
            [
                static::ITEM_GROSS_PRICE_1000,
                static::ITEM_GROSS_PRICE_1000,
                static::ITEM_GROSS_PRICE_1000,
            ],
        );

        $calculator = new PercentageType();
        $discountTransfer = (new DiscountTransfer())->setAmount(-1 * static::DISCOUNT_PERCENTAGE_200);
        $discountAmount = $calculator->calculateDiscount($items, $discountTransfer);

        $this->assertSame(0, $discountAmount);
    }

    /**
     * @return void
     */
    public function testCalculatePercentageShouldThrowAnExceptionForNonNumericValues(): void
    {
        $items = $this->getDiscountableItems(
            [
                static::ITEM_GROSS_PRICE_1000,
                static::ITEM_GROSS_PRICE_1000,
                static::ITEM_GROSS_PRICE_1000,
            ],
        );

        $calculator = new PercentageType();
        $this->expectException(CalculatorException::class);
        $discountCalculatorTransfer = (new DiscountTransfer())->setAmount('string');
        $calculator->calculateDiscount($items, $discountCalculatorTransfer);
    }

    /**
     * @return void
     */
    public function testCalculatePercentageShouldNotGiveNegativeDiscountAmounts(): void
    {
        $items = $this->getDiscountableItems(
            [
                -1 * static::ITEM_GROSS_PRICE_1000,
                -1 * static::ITEM_GROSS_PRICE_1000,
                -1 * static::ITEM_GROSS_PRICE_1000,
            ],
        );

        $calculator = new PercentageType();
        $discountCalculatorTransfer = (new DiscountTransfer())->setAmount(static::DISCOUNT_PERCENTAGE_10);
        $discountAmount = $calculator->calculateDiscount($items, $discountCalculatorTransfer);

        $this->assertSame(0, $discountAmount);
    }

    /**
     * @return void
     */
    public function testCalculatePercentageWhenQuantityIsNotSetShouldSetItToOne(): void
    {
        $items = $this->getDiscountableItems(
            [
                 static::ITEM_GROSS_PRICE_1000,
            ],
        );

        $items[0]->setQuantity(0);

        $calculator = new PercentageType();
        $discountTransfer = (new DiscountTransfer())->setAmount(static::DISCOUNT_PERCENTAGE_10);
        $discountAmount = $calculator->calculateDiscount($items, $discountTransfer);

        $this->assertNotEmpty($discountAmount);
    }

    /**
     * @param array $grossPrices
     *
     * @return array<\Generated\Shared\Transfer\DiscountableItemTransfer>
     */
    protected function getDiscountableItems(array $grossPrices): array
    {
        $items = [];

        foreach ($grossPrices as $grossPrice) {
            $discountableItems = new DiscountableItemTransfer();
            $discountableItems->setUnitPrice($grossPrice);
            $discountableItems->setQuantity(1);
            $discountableItems->setOriginalItemCalculatedDiscounts(new ArrayObject());

            $items[] = $discountableItems;
        }

        return $items;
    }
}
