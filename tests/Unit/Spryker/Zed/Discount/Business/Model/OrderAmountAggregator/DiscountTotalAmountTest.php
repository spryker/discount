<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Unit\Spryker\Zed\Discount\Business\Model\OrderAmountAggregator;

use Generated\Shared\Transfer\CalculatedDiscountTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use Spryker\Zed\Discount\Business\Model\OrderAmountAggregator\DiscountTotalAmount;

class DiscountTotalAmountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testDiscountCalculatedAmountShouldApplyTotalsFromItemAndExpenses()
    {
        $discountTotalsAggregator = $this->createDiscountTotalAmountAggregator();
        $orderTransfer = $this->createOrderTransfer();
        $discountTotalsAggregator->aggregate($orderTransfer);

        $this->assertEquals(200, $orderTransfer->getTotals()->getDiscountTotal());
    }

    /**
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    protected function createOrderTransfer()
    {
        $orderTransfer = new OrderTransfer();

        $itemTransfer = new ItemTransfer();

        $calculatedDiscount = new CalculatedDiscountTransfer();
        $calculatedDiscount->setSumGrossAmount(100);
        $itemTransfer->addCalculatedDiscount($calculatedDiscount);

        $orderTransfer->addItem($itemTransfer);

        $totalsTransfer = new TotalsTransfer();
        $orderTransfer->setTotals($totalsTransfer);

        $expenseTransfer = new ExpenseTransfer();

        $calculatedDiscount = new CalculatedDiscountTransfer();
        $calculatedDiscount->setSumGrossAmount(100);
        $expenseTransfer->addCalculatedDiscount($calculatedDiscount);

        $orderTransfer->addExpense($expenseTransfer);

        return $orderTransfer;
    }

    /**
     * @return \Spryker\Zed\Discount\Business\Model\OrderAmountAggregator\DiscountTotalAmount
     */
    protected function createDiscountTotalAmountAggregator()
    {
        return new DiscountTotalAmount();
    }
}
