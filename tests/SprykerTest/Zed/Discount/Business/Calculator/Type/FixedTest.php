<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\Calculator\Type;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\DiscountTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Spryker\Zed\Discount\Business\Calculator\Type\FixedType;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group Calculator
 * @group Type
 * @group FixedTest
 * Add your own group annotations below this line
 */
class FixedTest extends Unit
{
    /**
     * @var int
     */
    public const ITEM_GROSS_PRICE_1000 = 1000;

    /**
     * @var int
     */
    public const DISCOUNT_AMOUNT_FIXED_100 = 100;

    public const DISCOUNT_AMOUNT_FIXED_MINUS_100 = -100;

    /**
     * @return void
     */
    public function testCalculateFixedShouldReturnTheGivenAmount(): void
    {
        $items = $this->getItems(
            [
                static::ITEM_GROSS_PRICE_1000,
                static::ITEM_GROSS_PRICE_1000,
                static::ITEM_GROSS_PRICE_1000,
            ],
        );

        $calculator = new FixedType();
        $discountTransfer = $this->createDiscountTransfer(static::DISCOUNT_AMOUNT_FIXED_100);
        $discountAmount = $calculator->calculateDiscount($items, $discountTransfer);

        $this->assertSame(static::DISCOUNT_AMOUNT_FIXED_100, $discountAmount);
    }

    /**
     * @return void
     */
    public function testCalculateFixedShouldReturnNullForGivenNegativeAmounts(): void
    {
        $items = $this->getItems(
            [
                static::ITEM_GROSS_PRICE_1000,
                static::ITEM_GROSS_PRICE_1000,
                static::ITEM_GROSS_PRICE_1000,
            ],
        );

        $calculator = new FixedType();
        $discountTransfer = $this->createDiscountTransfer(-1 * static::DISCOUNT_AMOUNT_FIXED_100);
        $discountAmount = $calculator->calculateDiscount($items, $discountTransfer);

        $this->assertSame(0, $discountAmount);
    }

    /**
     * @param int $amount
     *
     * @return \Generated\Shared\Transfer\DiscountTransfer
     */
    protected function createDiscountTransfer(int $amount): DiscountTransfer
    {
        $discountTransfer = new DiscountTransfer();

        $currencyTransfer = new CurrencyTransfer();
        $currencyTransfer->setCode('EUR');
        $discountTransfer->setCurrency($currencyTransfer);

        $moneyValueTransfer = new MoneyValueTransfer();
        $moneyValueTransfer->setCurrency($currencyTransfer);
        $moneyValueTransfer->setGrossAmount($amount);

        $discountTransfer->addMoneyValue($moneyValueTransfer);

        return $discountTransfer;
    }

    /**
     * @param array $grossPrices
     *
     * @return array<\Generated\Shared\Transfer\ItemTransfer>
     */
    protected function getItems(array $grossPrices): array
    {
        $items = [];

        foreach ($grossPrices as $grossPrice) {
            $item = new ItemTransfer();
            $item->setUnitGrossPrice($grossPrice);
            $items[] = $item;
        }

        return $items;
    }
}
