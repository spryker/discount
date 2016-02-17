<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Discount\Business\Model\OrderAmountAggregator;

use Generated\Shared\Transfer\OrderTransfer;

class OrderDiscounts implements OrderAmountAggregatorInterface
{

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    public function aggregate(OrderTransfer $orderTransfer)
    {
        $orderCalculatedDiscounts = $this->getOrderCalculatedDiscounts($orderTransfer);
        $orderTransfer->setCalculatedDiscounts($orderCalculatedDiscounts);
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \ArrayObject $orderCalculatedDiscounts
     *
     * @return \ArrayObject|\Generated\Shared\Transfer\CalculatedDiscountTransfer[]
     */
    protected function getOrderExpenseCalculatedDiscounts(
        OrderTransfer $orderTransfer,
        \ArrayObject $orderCalculatedDiscounts
    ) {
        foreach ($orderTransfer->getExpenses() as $expenseTransfer) {
            $orderCalculatedDiscounts = $this->getSumOfCalculatedDiscounts(
                $orderCalculatedDiscounts,
                $expenseTransfer->getCalculatedDiscounts()
            );
        }

        return $orderCalculatedDiscounts;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \ArrayObject $orderCalculatedDiscounts
     *
     * @return \ArrayObject|\Generated\Shared\Transfer\CalculatedDiscountTransfer[]
     */
    protected function getItemCalculatedDiscounts(
        OrderTransfer $orderTransfer,
        \ArrayObject $orderCalculatedDiscounts
    ) {
        foreach ($orderTransfer->getItems() as $itemTransfer) {
            $orderCalculatedDiscounts = $this->getSumOfCalculatedDiscounts(
                $orderCalculatedDiscounts,
                $itemTransfer->getCalculatedDiscounts()
            );
        }

        return $orderCalculatedDiscounts;
    }


    /**
     * @param \ArrayObject|\Generated\Shared\Transfer\CalculatedDiscountTransfer[] $orderCalculatedDiscounts
     * @param \ArrayObject|\Generated\Shared\Transfer\CalculatedDiscountTransfer[] $calculatedDiscounts
     *
     * @return \ArrayObject|\Generated\Shared\Transfer\CalculatedDiscountTransfer[]
     */
    public function getSumOfCalculatedDiscounts(\ArrayObject $orderCalculatedDiscounts, \ArrayObject $calculatedDiscounts)
    {
        foreach ($calculatedDiscounts as $calculatedDiscountTransfer) {
            $displayName = $calculatedDiscountTransfer->getDisplayName();
            if ($orderCalculatedDiscounts->offsetExists($displayName) === false) {
                $orderCalculatedDiscounts[$displayName] = clone $calculatedDiscountTransfer;
                continue;
            }

            $orderCalculatedDiscounts[$displayName]->setUnitGrossAmount(
                $orderCalculatedDiscounts[$displayName]->getUnitGrossAmount() + $calculatedDiscountTransfer->getUnitGrossAmount()
            );
            $orderCalculatedDiscounts[$displayName]->setSumGrossAmount(
                $orderCalculatedDiscounts[$displayName]->getSumGrossAmount() + $calculatedDiscountTransfer->getSumGrossAmount()
            );
        }

        return $orderCalculatedDiscounts;

    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \ArrayObject|\Generated\Shared\Transfer\CalculatedDiscountTransfer[]
     */
    protected function getOrderCalculatedDiscounts(OrderTransfer $orderTransfer)
    {
        $orderCalculatedDiscounts = new \ArrayObject();
        $orderCalculatedDiscounts = $this->getItemCalculatedDiscounts($orderTransfer, $orderCalculatedDiscounts);
        $orderCalculatedDiscounts = $this->getOrderExpenseCalculatedDiscounts($orderTransfer, $orderCalculatedDiscounts);

        return $orderCalculatedDiscounts;
    }
}
