<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\Persistence;

use ArrayObject;
use Generated\Shared\Transfer\CalculatedDiscountTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Orm\Zed\Sales\Persistence\SpySalesDiscount;
use Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface;

class DiscountOrderHydrate implements DiscountOrderHydrateInterface
{
    /**
     * @var \Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface
     */
    protected $discountQueryContainer;

    /**
     * @param \Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface $discountQueryContainer
     */
    public function __construct(DiscountQueryContainerInterface $discountQueryContainer)
    {
        $this->discountQueryContainer = $discountQueryContainer;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\OrderTransfer
     */
    public function hydrate(OrderTransfer $orderTransfer)
    {
        $orderTransfer->requireIdSalesOrder();

        $salesOrderDiscounts = $this->getSalesOrderDiscounts($orderTransfer->getIdSalesOrder());

        $groupedDiscounts = [];
        foreach ($salesOrderDiscounts as $salesOrderDiscountEntity) {
            $calculatedDiscountTransfer = $this->hydrateCalculatedDiscountTransfer($salesOrderDiscountEntity);

            $this->addCalculatedDiscount($orderTransfer, $salesOrderDiscountEntity, $calculatedDiscountTransfer);

            if (!isset($groupedDiscounts[$salesOrderDiscountEntity->getDisplayName()])) {
                $groupedDiscounts[$salesOrderDiscountEntity->getDisplayName()] = $calculatedDiscountTransfer;
            }

            $calculatedDiscountTransfer = $groupedDiscounts[$salesOrderDiscountEntity->getDisplayName()];

            $calculatedDiscountTransfer->setSumAmount(
                $calculatedDiscountTransfer->getSumGrossAmount() + (int)$salesOrderDiscountEntity->getAmount()
            );

            $groupedDiscounts[$salesOrderDiscountEntity->getDisplayName()] = $calculatedDiscountTransfer;
        }

        $orderTransfer->setCalculatedDiscounts(new ArrayObject($groupedDiscounts));

        return $orderTransfer;
    }

    /**
     * @param int $idSalesOrder
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesDiscount[]|\Propel\Runtime\Collection\ObjectCollection
     */
    protected function getSalesOrderDiscounts($idSalesOrder)
    {
        return $this->discountQueryContainer
            ->querySalesDiscount()
            ->leftJoinWithExpense()
            ->joinWithOrder()
            ->filterByFkSalesOrder($idSalesOrder)
            ->find();
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Orm\Zed\Sales\Persistence\SpySalesDiscount $salesOrderDiscountEntity
     * @param \Generated\Shared\Transfer\CalculatedDiscountTransfer $calculatedDiscountTransfer
     *
     * @return void
     */
    protected function addCalculatedDiscount(
        OrderTransfer $orderTransfer,
        SpySalesDiscount $salesOrderDiscountEntity,
        CalculatedDiscountTransfer $calculatedDiscountTransfer
    ) {

        if ($salesOrderDiscountEntity->getFkSalesExpense()) {
            $this->addCalculatedDiscountToExpense($orderTransfer, $calculatedDiscountTransfer, $salesOrderDiscountEntity->getFkSalesExpense());
            return;
        }

        if ($salesOrderDiscountEntity->getFkSalesOrderItemOption()) {
            $this->addCalculatedDiscountToItemProductOption($orderTransfer, $calculatedDiscountTransfer, $salesOrderDiscountEntity->getFkSalesOrderItemOption());
            return;
        }

        if ($salesOrderDiscountEntity->getFkSalesOrderItem()) {
            $this->addCalculatedDiscountToItem($orderTransfer, $calculatedDiscountTransfer, $salesOrderDiscountEntity->getFkSalesOrderItem());
            return;
        }
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\CalculatedDiscountTransfer $calculatedDiscountTransfer
     * @param int $idSalesExpense
     *
     * @return void
     */
    protected function addCalculatedDiscountToExpense(
        OrderTransfer $orderTransfer,
        CalculatedDiscountTransfer $calculatedDiscountTransfer,
        $idSalesExpense
    ) {
        foreach ($orderTransfer->getExpenses() as $expenseTransfer) {
            if ($expenseTransfer->getIdSalesExpense() !== $idSalesExpense) {
                continue;
            }

            $expenseTransfer->addCalculatedDiscount($calculatedDiscountTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\CalculatedDiscountTransfer $calculatedDiscountTransfer
     * @param int $idSalesOrderItemOption
     *
     * @return void
     */
    protected function addCalculatedDiscountToItemProductOption(
        OrderTransfer $orderTransfer,
        CalculatedDiscountTransfer $calculatedDiscountTransfer,
        $idSalesOrderItemOption
    ) {
        foreach ($orderTransfer->getItems() as $itemTransfer) {
            foreach ($itemTransfer->getProductOptions() as $productOptionTransfer) {
                if ($productOptionTransfer->getIdSalesOrderItemOption() !== $idSalesOrderItemOption) {
                    continue;
                }
                $productOptionTransfer->addCalculatedDiscount($calculatedDiscountTransfer);
            }
        }
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\CalculatedDiscountTransfer $calculatedDiscountTransfer
     * @param int $idSalesOrderItem
     *
     * @return void
     */
    protected function addCalculatedDiscountToItem(
        OrderTransfer $orderTransfer,
        CalculatedDiscountTransfer $calculatedDiscountTransfer,
        $idSalesOrderItem
    ) {
        foreach ($orderTransfer->getItems() as $itemTransfer) {
            if ($itemTransfer->getIdSalesOrderItem() !== $idSalesOrderItem) {
                continue;
            }
            $itemTransfer->addCalculatedDiscount($calculatedDiscountTransfer);
        }
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesDiscount $salesOrderDiscountEntity
     *
     * @return \Generated\Shared\Transfer\CalculatedDiscountTransfer
     */
    protected function hydrateCalculatedDiscountTransfer(SpySalesDiscount $salesOrderDiscountEntity)
    {
        $calculatedDiscountTransfer = new CalculatedDiscountTransfer();
        $calculatedDiscountTransfer->setIdDiscount($salesOrderDiscountEntity->getIdSalesDiscount());
        $calculatedDiscountTransfer->fromArray($salesOrderDiscountEntity->toArray(), true);
        $calculatedDiscountTransfer->setUnitAmount($salesOrderDiscountEntity->getAmount());
        $calculatedDiscountTransfer->setQuantity(1);

        foreach ($salesOrderDiscountEntity->getDiscountCodes() as $discountCodeEntity) {
            $calculatedDiscountTransfer->setVoucherCode($discountCodeEntity->getCode());
        }

        return $calculatedDiscountTransfer;
    }
}
