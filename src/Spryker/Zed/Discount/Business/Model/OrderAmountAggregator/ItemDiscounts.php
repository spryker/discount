<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Discount\Business\Model\OrderAmountAggregator;

use Generated\Shared\Transfer\CalculatedDiscountTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Orm\Zed\Sales\Persistence\SpySalesDiscount;
use Propel\Runtime\Collection\ObjectCollection;
use Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface;

class ItemDiscounts implements OrderAmountAggregatorInterface
{
    /**
     * @var DiscountQueryContainerInterface
     */
    protected $discountQueryContainer;

    /**
     * ItemDiscountAmounts constructor.
     */
    public function __construct(DiscountQueryContainerInterface $discountQueryContainer)
    {
        $this->discountQueryContainer = $discountQueryContainer;
    }

    /**
     * @param OrderTransfer $orderTransfer
     *
     * @return void
     */
    public function aggregate(OrderTransfer $orderTransfer)
    {
        $this->assertItemDiscountsRequirements($orderTransfer);

        $salesOrderDiscounts = $this->getSalesOrderDiscounts($orderTransfer);

        if (count($salesOrderDiscounts) === 0) {
            $this->setGrossPriceWithDiscountsToDefaults($orderTransfer);
            return;
        }

        $this->addDiscountsFromSalesOrderDiscountEntity($orderTransfer, $salesOrderDiscounts);
    }

    /**
     * @param OrderTransfer $orderTransfer
     * @param ObjectCollection|SpySalesDiscount[] $salesOrderDiscounts
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
            $this->addOrderExpenseCalculatedDiscounts($orderTransfer, $salesOrderDiscountEntity);
        }
    }

    /**
     * @param ItemTransfer $itemTransfer
     * @param SpySalesDiscount $salesOrderDiscountEntity
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

        $calculatedDiscountTransfer = $this->hydrateCalculatedDiscountTransferFromEntity(
            $salesOrderDiscountEntity,
            $itemTransfer->getQuantity()
        );
        $itemTransfer->addCalculatedDiscount($calculatedDiscountTransfer);
    }



    /**
     * @param OrderTransfer $orderTransfer
     * @param SpySalesDiscount $salesOrderDiscountEntity
     *
     * @return void
     */
    protected function addOrderExpenseCalculatedDiscounts(
        OrderTransfer $orderTransfer,
        SpySalesDiscount $salesOrderDiscountEntity
    ) {

        if ($salesOrderDiscountEntity->getFkSalesExpense() === null) {
            return;
        }

        foreach ($orderTransfer->getExpenses() as $expenseTransfer) {
            if ($expenseTransfer->getIdSalesExpense() !== $salesOrderDiscountEntity->getFkSalesExpense()) {
                continue;
            }

            $calculatedDiscountTransfer = $this->hydrateCalculatedDiscountTransferFromEntity(
                $salesOrderDiscountEntity,
                $expenseTransfer->getQuantity()
            );
            $expenseTransfer->addCalculatedDiscount($calculatedDiscountTransfer);

            $this->updateExpenseGrossPriceWithDiscounts($expenseTransfer, $calculatedDiscountTransfer);
            $this->setExpenseRefundableAmount($expenseTransfer, $calculatedDiscountTransfer);
        }
    }

    /**
     * @param SpySalesDiscount $salesOrderDiscountEntity
     * @param int $quantity
     *
     * @return CalculatedDiscountTransfer
     */
    protected function hydrateCalculatedDiscountTransferFromEntity(SpySalesDiscount $salesOrderDiscountEntity, $quantity)
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
     * @param ExpenseTransfer $expenseTransfer
     * @param CalculatedDiscountTransfer $calculatedDiscountTransfer
     *
     * @return void
     */
    protected function updateExpenseGrossPriceWithDiscounts(
        ExpenseTransfer $expenseTransfer,
        CalculatedDiscountTransfer $calculatedDiscountTransfer
    ) {
        $expenseTransfer->setUnitGrossPriceWithDiscounts(
            $expenseTransfer->getUnitGrossPrice() - $calculatedDiscountTransfer->getUnitGrossAmount()
        );

        $expenseTransfer->setSumGrossPriceWithDiscounts(
            $expenseTransfer->getSumGrossPrice() - $calculatedDiscountTransfer->getSumGrossAmount()
        );
    }

    /**
     * @param OrderTransfer $orderTransfer
     *
     * @return SpySalesDiscount[]|ObjectCollection
     */
    protected function getSalesOrderDiscounts(OrderTransfer $orderTransfer)
    {
        return $this->discountQueryContainer
            ->querySalesDisount()
            ->findByFkSalesOrder($orderTransfer->getIdSalesOrder());
    }

    /**
     * @param OrderTransfer $orderTransfer
     *
     * @return void
     */
    protected function assertItemDiscountsRequirements(OrderTransfer $orderTransfer)
    {
        $orderTransfer->requireIdSalesOrder();
    }

    /**
     * @param ItemTransfer $itemTransfer
     *
     * @return void
     */
    protected function assertItemRequirements(ItemTransfer $itemTransfer)
    {
        $itemTransfer->requireQuantity()->requireIdSalesOrderItem();
    }

    /**
     * @param ExpenseTransfer $expenseTransfer
     *
     * @return void
     */
    protected function addExpenseDiscountAmountDefaults(ExpenseTransfer $expenseTransfer)
    {
        $expenseTransfer->setUnitGrossPriceWithDiscounts($expenseTransfer->getUnitGrossPrice());
        $expenseTransfer->setSumGrossPriceWithDiscounts($expenseTransfer->getSumGrossPrice());
    }

    /**
     * @param OrderTransfer $orderTransfer
     *
     * @return void
     */
    protected function setGrossPriceWithDiscountsToDefaults($orderTransfer)
    {
        foreach ($orderTransfer->getExpenses() as $expenseTransfer) {
            $expenseTransfer->setSumGrossPriceWithDiscounts($expenseTransfer->getSumGrossPrice());
            $expenseTransfer->setUnitGrossPriceWithDiscounts($expenseTransfer->getUnitGrossPrice());
        }
    }

    /**
     * @param ExpenseTransfer $expenseTransfer
     * @param CalculatedDiscountTransfer $calculatedDiscountTransfer
     *
     * @return void
     */
    protected function setExpenseRefundableAmount(
        ExpenseTransfer $expenseTransfer,
        CalculatedDiscountTransfer $calculatedDiscountTransfer
    ) {
        $expenseTransfer->setRefundableAmount(
            $expenseTransfer->getRefundableAmount() - $calculatedDiscountTransfer->getSumGrossAmount()
        );
    }
}
