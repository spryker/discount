<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Unit\Spryker\Zed\Discount\Business\SalesAggregator;

use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Orm\Zed\Sales\Persistence\SpySalesDiscount;
use Propel\Runtime\Collection\ObjectCollection;
use Spryker\Zed\Discount\Business\SalesAggregator\OrderExpensesWithDiscounts;
use Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface;

class OrderExpenseWithDiscountsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return void
     */
    public function testAggregateShouldAddCalculatedDiscountsToExpenses()
    {
        $salesDiscountEntity = new SpySalesDiscount();
        $salesDiscountEntity->setAmount(10);
        $salesDiscountEntity->setFkSalesExpense(1);

        $salesDiscounts[] = $salesDiscountEntity;

        $orderExpensesWithDiscounts = $this->createOrderExpensesWithDiscounts($salesDiscounts);

        $orderTransfer = $this->createOrderTransfer();

        $orderExpensesWithDiscounts->aggregate($orderTransfer);

        $this->assertEquals(90, $orderTransfer->getExpenses()[0]->getUnitGrossPriceWithDiscounts());
        $this->assertEquals(190, $orderTransfer->getExpenses()[0]->getSumGrossPriceWithDiscounts());
    }

    /**
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    protected function createOrderTransfer()
    {
        $orderTransfer = new OrderTransfer();
        $expenseTransfer = new ExpenseTransfer();
        $expenseTransfer->setUnitGrossPrice(100);
        $expenseTransfer->setSumGrossPrice(200);
        $expenseTransfer->setIdSalesExpense(1);
        $orderTransfer->addExpense($expenseTransfer);

        return $orderTransfer;
    }

    /**
     * @param array $salesDiscounts
     * @param \Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface $discountQueryContainerMock
     *
     * @return \Spryker\Zed\Discount\Business\SalesAggregator\OrderExpenseTaxWithDiscounts
     */
    protected function createOrderExpensesWithDiscounts(
        array $salesDiscounts,
        DiscountQueryContainerInterface $discountQueryContainerMock = null
    ) {

        if ($discountQueryContainerMock === null) {
            $discountQueryContainerMock = $this->createDiscountQueryContainerMock();
        }

        $orderExpensesWithDiscountsMock = $this->getMock(
            OrderExpensesWithDiscounts::class,
            ['getSalesOrderDiscounts'],
            [$discountQueryContainerMock]
        );

        $objectCollection = new ObjectCollection($salesDiscounts);

        $orderExpensesWithDiscountsMock->method('getSalesOrderDiscounts')
            ->willReturn($objectCollection);

        return $orderExpensesWithDiscountsMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DiscountQueryContainerInterface
     */
    protected function createDiscountQueryContainerMock()
    {
        return $this->getMock(DiscountQueryContainerInterface::class);
    }
}
