<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Unit\Spryker\Zed\Discount\Business\Model\OrderAmountAggregator;

use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use Spryker\Zed\Discount\Business\Model\OrderAmountAggregator\GrandTotalWithDiscounts;

class GrandTotalWithDiscountsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return void
     */
    public function testGrandTotalWithDiscountsShouldSubtractDiscountAmountFromGrandTotal()
    {
        $grandTotalWithDiscountsAggregator = $this->createGrandTotalWithDiscountsAggregator();
        $orderTransfer = $this->createOrderTransfer();
        $grandTotalWithDiscountsAggregator->aggregate($orderTransfer);

        $this->assertEquals(400, $orderTransfer->getTotals()->getGrandTotal());
    }

    /**
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    protected function createOrderTransfer()
    {
        $orderTransfer = new OrderTransfer();

        $totalTransfer = new TotalsTransfer();
        $totalTransfer->setGrandTotal(500);
        $totalTransfer->setDiscountTotal(100);

        $orderTransfer->setTotals($totalTransfer);

        return $orderTransfer;
    }

    /**
     * @return \Spryker\Zed\Discount\Business\Model\OrderAmountAggregator\GrandTotalWithDiscounts
     */
    protected function createGrandTotalWithDiscountsAggregator()
    {
        return new GrandTotalWithDiscounts();
    }

}
