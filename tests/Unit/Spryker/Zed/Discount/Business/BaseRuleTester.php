<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Unit\Spryker\Zed\Discount\Business;

use Generated\Shared\Transfer\ClauseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface;
use Spryker\Zed\Discount\Business\QueryString\Converter\CurrencyConverterInterface;

class BaseRuleTester extends \PHPUnit_Framework_TestCase
{

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface
     */
    protected function createComparatorMock()
    {
        return $this->getMock(ComparatorOperatorsInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\Discount\Business\QueryString\Converter\CurrencyConverterInterface
     */
    protected function createCurrencyCoverterMock()
    {
        return $this->getMock(CurrencyConverterInterface::class);
    }

    /**
     * @param string $value
     *
     * @return \Generated\Shared\Transfer\ClauseTransfer
     */
    protected function createClauseTransfer($value)
    {
        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setValue($value);

        return $clauseTransfer;
    }

    /**
     * @param array $items
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function createQuoteTransfer(array $items = [])
    {
        $quoteTransfer = new QuoteTransfer();
        $quoteTransfer->setItems(new \ArrayObject($items));

        return $quoteTransfer;

    }

    /**
     * @param int $price
     * @param int $quantity
     * @param string $sku
     *
     * @return \Generated\Shared\Transfer\ItemTransfer
     */
    protected function createItemTransfer($price = 0, $quantity = 0, $sku = '')
    {
        $itemTransfer = new ItemTransfer();
        $itemTransfer->setUnitGrossPrice($price);
        $itemTransfer->setQuantity($quantity);
        $itemTransfer->setSku($sku);

        return $itemTransfer;
    }

}
