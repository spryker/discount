<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\Model\OrderAmountAggregator;

use Generated\Shared\Transfer\CalculatedDiscountTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Orm\Zed\Sales\Persistence\SpySalesDiscount;
use Propel\Runtime\Collection\ObjectCollection;
use Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface;

class ItemDiscounts implements OrderAmountAggregatorInterface
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
     * @return void
     */
    public function aggregate(OrderTransfer $orderTransfer)
    {
        $salesOrderDiscounts = $this->getSalesOrderItemDiscounts($orderTransfer);

        if (count($salesOrderDiscounts) === 0) {
            $this->setItemGrossPriceWithDiscountsToDefaults($orderTransfer);

            return;
        }

        $this->addDiscountsFromSalesOrderDiscountEntity($orderTransfer, $salesOrderDiscounts);
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesDiscount[]|\Propel\Runtime\Collection\ObjectCollection
     */
    protected function getSalesOrderItemDiscounts(OrderTransfer $orderTransfer)
    {
        $saleOrderItemIds = $this->getSaleOrderItemIds($orderTransfer);

        if (empty($saleOrderItemIds)) {
            return [];
        }

        return $this->discountQueryContainer
            ->querySalesDisount()
            ->filterByFkSalesOrderItem($saleOrderItemIds)
            ->find();
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Propel\Runtime\Collection\ObjectCollection|\Orm\Zed\Sales\Persistence\SpySalesDiscount[] $salesOrderDiscounts
     *
     * @return void
     */
    protected function addDiscountsFromSalesOrderDiscountEntity(
        OrderTransfer $orderTransfer,
        ObjectCollection $salesOrderDiscounts
    ) {
        foreach ($salesOrderDiscounts as $salesOrderDiscountEntity) {
            foreach ($orderTransfer->getItems() as $itemTransfer) {
                $this->assertItemRequirements($itemTransfer);
                $this->addItemCalculatedDiscounts($itemTransfer, $salesOrderDiscountEntity);
            }
        }
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Orm\Zed\Sales\Persistence\SpySalesDiscount $salesOrderDiscountEntity
     *
     * @return void
     */
    protected function addItemCalculatedDiscounts(
        ItemTransfer $itemTransfer,
        SpySalesDiscount $salesOrderDiscountEntity
    ) {
        if ($itemTransfer->getIdSalesOrderItem() !== $salesOrderDiscountEntity->getFkSalesOrderItem() ||
            $salesOrderDiscountEntity->getFkSalesOrderItemOption() !== null
        ) {
            return;
        }

        $calculatedDiscountTransfer = $this->getHydratedCalculatedDiscountTransferFromEntity(
            $salesOrderDiscountEntity,
            $itemTransfer->getQuantity()
        );

        $itemTransfer->setUnitTotalDiscountAmount(
            $itemTransfer->getUnitTotalDiscountAmount() + $calculatedDiscountTransfer->getUnitGrossAmount()
        );

        $itemTransfer->setSumTotalDiscountAmount(
            $itemTransfer->getSumTotalDiscountAmount() + $calculatedDiscountTransfer->getSumGrossAmount()
        );

        $this->updateItemGrossPriceWithDiscounts($itemTransfer, $calculatedDiscountTransfer);

        $itemTransfer->addCalculatedDiscount($calculatedDiscountTransfer);
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesDiscount $salesOrderDiscountEntity
     * @param int $quantity
     *
     * @return \Generated\Shared\Transfer\CalculatedDiscountTransfer
     */
    protected function getHydratedCalculatedDiscountTransferFromEntity(SpySalesDiscount $salesOrderDiscountEntity, $quantity)
    {
        $quantity = !empty($quantity) ? $quantity : 1;

        $calculatedDiscountTransfer = new CalculatedDiscountTransfer();
        $calculatedDiscountTransfer->fromArray($salesOrderDiscountEntity->toArray(), true);
        $calculatedDiscountTransfer->setQuantity($quantity);
        $calculatedDiscountTransfer->setUnitGrossAmount($salesOrderDiscountEntity->getAmount());
        $calculatedDiscountTransfer->setSumGrossAmount($salesOrderDiscountEntity->getAmount() * $quantity);

        foreach ($salesOrderDiscountEntity->getDiscountCodes() as $discountCodeEntity) {
            $calculatedDiscountTransfer->setVoucherCode($discountCodeEntity->getCode());
        }

        return $calculatedDiscountTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     *
     * @return void
     */
    protected function assertItemRequirements(ItemTransfer $itemTransfer)
    {
        $itemTransfer->requireQuantity()->requireIdSalesOrderItem();
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    protected function setItemGrossPriceWithDiscountsToDefaults(OrderTransfer $orderTransfer)
    {
        foreach ($orderTransfer->getItems() as $itemTransfer) {
            $itemTransfer->setUnitGrossPriceWithDiscounts($itemTransfer->getUnitGrossPrice());
            $itemTransfer->setSumGrossPriceWithDiscounts($itemTransfer->getSumGrossPrice());
        }
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\CalculatedDiscountTransfer $calculatedDiscountTransfer
     *
     * @return void
     */
    protected function updateItemGrossPriceWithDiscounts(
        ItemTransfer $itemTransfer,
        CalculatedDiscountTransfer $calculatedDiscountTransfer
    ) {
        $itemTransfer->setUnitGrossPriceWithDiscounts(
            $itemTransfer->getUnitGrossPrice() - $calculatedDiscountTransfer->getUnitGrossAmount()
        );

        $itemTransfer->setSumGrossPriceWithDiscounts(
            $itemTransfer->getSumGrossPrice() - $calculatedDiscountTransfer->getSumGrossAmount()
        );
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return int[]
     */
    protected function getSaleOrderItemIds(OrderTransfer $orderTransfer)
    {
        $saleOrderItemIds = [];
        foreach ($orderTransfer->getItems() as $itemTransfer) {
            $saleOrderItemIds[] = $itemTransfer->getIdSalesOrderItem();
        }

        return $saleOrderItemIds;
    }

}
