<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Unit\Spryker\Zed\Discount\Business\Model\OrderAmountAggregator;

use Generated\Shared\Transfer\CalculatedDiscountTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Discount\Business\Model\OrderAmountAggregator\OrderDiscounts;

class OrderDiscountsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return void
     */
    public function testOrderDiscountsGrossSumShouldAggregateAmountsFromItemsAndExpenses()
    {
        $orderDiscountsAggregator = $this->createOrderDiscountsAggregator();
        $orderTransfer = $this->createOrderTransfer();
        $orderDiscountsAggregator->aggregate($orderTransfer);

        $this->assertEquals(60, $orderTransfer->getCalculatedDiscounts()['test']->getSumGrossAmount());
    }

    /**
     * @return void
     */
    public function testOrderDiscountsGrossUnitShouldAggregateAmountsFromItemsAndExpenses()
    {
        $orderDiscountsAggregator = $this->createOrderDiscountsAggregator();
        $orderTransfer = $this->createOrderTransfer();
        $orderDiscountsAggregator->aggregate($orderTransfer);

        $this->assertEquals(30, $orderTransfer->getCalculatedDiscounts()['test']->getUnitGrossAmount());
    }

    /**
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    protected function createOrderTransfer()
    {
        $orderTransfer = new OrderTransfer();

        $orderTransfer->setIdSalesOrder(1);

        $itemTransfer = new ItemTransfer();
        $calculatedDiscountTransfer = new CalculatedDiscountTransfer();
        $calculatedDiscountTransfer->setDisplayName('test');
        $calculatedDiscountTransfer->setUnitGrossAmount(10);
        $calculatedDiscountTransfer->setSumGrossAmount(20);
        $itemTransfer->addCalculatedDiscount($calculatedDiscountTransfer);
        $itemTransfer->addCalculatedDiscount(clone $calculatedDiscountTransfer);

        $orderTransfer->addItem($itemTransfer);

        $expenseTransfer = new ExpenseTransfer();
        $expenseTransfer->addCalculatedDiscount(clone $calculatedDiscountTransfer);

        $orderTransfer->addExpense($expenseTransfer);

        return $orderTransfer;
    }

    /**
     * @return \Spryker\Zed\Discount\Business\Model\OrderAmountAggregator\OrderDiscounts
     */
    protected function createOrderDiscountsAggregator()
    {
        return new OrderDiscounts();
    }

}
