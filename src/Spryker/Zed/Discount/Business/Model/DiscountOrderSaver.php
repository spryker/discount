<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Discount\Business\Model;

use Generated\Shared\Transfer\CalculatedDiscountTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Propel\Runtime\Exception\PropelException;
use Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucher;
use Orm\Zed\Sales\Persistence\SpySalesDiscount;
use Orm\Zed\Sales\Persistence\SpySalesDiscountCode;

class DiscountOrderSaver implements DiscountSaverInterface
{

    /**
     * @var \Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface
     */
    protected $discountQueryContainer;

    /**
     * @var array|string[]
     */
    protected $voucherCodesUsed = [];

    /**
     * @var \Spryker\Zed\Discount\Business\Model\VoucherCode
     */
    protected $voucherCode;

    /**
     * @param \Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface $discountQueryContainer
     * @param \Spryker\Zed\Discount\Business\Model\VoucherCodeInterface $voucherCode
     */
    public function __construct(
        DiscountQueryContainerInterface $discountQueryContainer,
        VoucherCodeInterface $voucherCode
    ) {
        $this->discountQueryContainer = $discountQueryContainer;
        $this->voucherCode = $voucherCode;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\CheckoutResponseTransfer $checkoutResponseTransfer
     *
     * @return void
     */
    public function saveDiscounts(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer)
    {
        $this->saveOrderItemDiscounts($checkoutResponseTransfer);
        $this->saveOrderExpenseDiscounts($quoteTransfer, $checkoutResponseTransfer);
        $this->voucherCode->useCodes($this->voucherCodesUsed);
    }

    /**
     * @param \Generated\Shared\Transfer\CheckoutResponseTransfer $checkoutResponseTransfer
     *
     * @return void
     */
    protected function saveOrderItemDiscounts(CheckoutResponseTransfer $checkoutResponseTransfer)
    {
        $orderItemCollection = $checkoutResponseTransfer->getSaveOrder()->getOrderItems();
        $idSalesOrder = $checkoutResponseTransfer->getSaveOrder()->getIdSalesOrder();
        foreach ($orderItemCollection as $orderItemTransfer) {
            $discountCollection = $orderItemTransfer->getCalculatedDiscounts();
            foreach ($discountCollection as $discountTransfer) {
                $salesDiscountEntity = $this->createSalesDiscountEntity($discountTransfer);
                $salesDiscountEntity->setFkSalesOrder($idSalesOrder);
                $salesDiscountEntity->setFkSalesOrderItem($orderItemTransfer->getIdSalesOrderItem());
                $this->saveDiscount($salesDiscountEntity, $discountTransfer);
            }
            $this->saveOrderItemOptionDiscounts(
                $orderItemTransfer,
                $idSalesOrder,
                $orderItemTransfer->getIdSalesOrderItem()
            );
        }
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $orderItemTransfer
     * @param int $idSalesOrder
     * @param int $idSalesOrderItem
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return void
     */
    protected function saveOrderItemOptionDiscounts(ItemTransfer $orderItemTransfer, $idSalesOrder, $idSalesOrderItem)
    {
        foreach ($orderItemTransfer->getProductOptions() as $productOptionTransfer) {
            foreach ($productOptionTransfer->getCalculatedDiscounts() as $productOptionDiscountTransfer) {
                $salesDiscountEntity = $this->createSalesDiscountEntity($productOptionDiscountTransfer);
                $salesDiscountEntity->setFkSalesOrder($idSalesOrder);
                $salesDiscountEntity->setFkSalesOrderItem($idSalesOrderItem);
                $salesDiscountEntity->setFkSalesOrderItemOption($productOptionTransfer->getIdSalesOrderItemOption());
                $this->saveDiscount($salesDiscountEntity, $productOptionDiscountTransfer);
            }
        }
    }

    /**
     * @param \Generated\Shared\Transfer\CalculatedDiscountTransfer $calculatedDiscountTransfer
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesDiscount
     */
    protected function createSalesDiscountEntity(CalculatedDiscountTransfer $calculatedDiscountTransfer)
    {
        $salesDiscountEntity = $this->getSalesDiscountEntity();
        $salesDiscountEntity->fromArray($calculatedDiscountTransfer->toArray());
        $salesDiscountEntity->setName('');

        return $salesDiscountEntity;
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesDiscount $salesDiscountEntity
     * @param \Generated\Shared\Transfer\CalculatedDiscountTransfer $calculatedDiscountTransfer
     *
     * @return void
     */
    protected function saveDiscount(
        SpySalesDiscount $salesDiscountEntity,
        CalculatedDiscountTransfer $calculatedDiscountTransfer
    ) {
        $salesDiscountEntity->setAmount($calculatedDiscountTransfer->getUnitGrossAmount());
        $this->persistSalesDiscount($salesDiscountEntity);

        if ($this->haveVoucherCode($calculatedDiscountTransfer)) {
            $this->saveUsedCodes($calculatedDiscountTransfer, $salesDiscountEntity);
        }
    }

    /**
     * @return \Orm\Zed\Sales\Persistence\SpySalesDiscount
     */
    protected function getSalesDiscountEntity()
    {
        return new SpySalesDiscount();
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesDiscount $salesDiscountEntity
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return void
     */
    protected function persistSalesDiscount(SpySalesDiscount $salesDiscountEntity)
    {
        $salesDiscountEntity->save();
    }

    /**
     * @param \Generated\Shared\Transfer\CalculatedDiscountTransfer $calculatedDiscountTransfer
     *
     * @return bool
     */
    private function haveVoucherCode(CalculatedDiscountTransfer $calculatedDiscountTransfer)
    {
        $voucherCode = $calculatedDiscountTransfer->getVoucherCode();

        return (!empty($voucherCode));
    }

    /**
     * @param \Generated\Shared\Transfer\CalculatedDiscountTransfer $calculatedDiscountTransfer
     * @param \Orm\Zed\Sales\Persistence\SpySalesDiscount $salesDiscountEntity
     *
     * @return void
     */
    protected function saveUsedCodes(CalculatedDiscountTransfer $calculatedDiscountTransfer, SpySalesDiscount $salesDiscountEntity)
    {
        $voucherCode = $calculatedDiscountTransfer->getVoucherCode();
        $discountVoucherEntity = $this->getDiscountVoucherEntityByCode($voucherCode);
        if ($discountVoucherEntity) {
            $salesDiscountCodeEntity = $this->getSalesDiscountCodeEntity();
            $salesDiscountCodeEntity->fromArray($discountVoucherEntity->toArray());
            $salesDiscountCodeEntity->setCodepoolName(
                $discountVoucherEntity->getVoucherPool()->getName()
            );
            $salesDiscountCodeEntity->setDiscount($salesDiscountEntity);

            if (!isset($this->voucherCodesUsed[$voucherCode])) {
                $this->voucherCodesUsed[$voucherCode] = $voucherCode;
            }

            $this->persistSalesDiscountCode($salesDiscountCodeEntity);
        }
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesDiscountCode $salesDiscountCodeEntity
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return void
     */
    protected function persistSalesDiscountCode(SpySalesDiscountCode $salesDiscountCodeEntity)
    {
        $salesDiscountCodeEntity->save();
    }

    /**
     * @param string $code
     *
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountVoucher
     */
    protected function getDiscountVoucherEntityByCode($code)
    {
        return $this->discountQueryContainer->queryVoucher($code)->findOne();
    }

    /**
     * @return \Orm\Zed\Sales\Persistence\SpySalesDiscountCode
     */
    protected function getSalesDiscountCodeEntity()
    {
        return new SpySalesDiscountCode();
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\CheckoutResponseTransfer $checkoutResponseTransfer
     *
     * @return void
     */
    protected function saveOrderExpenseDiscounts(
        QuoteTransfer $quoteTransfer,
        CheckoutResponseTransfer $checkoutResponseTransfer
    ) {
        $idSalesOrder = $checkoutResponseTransfer->getSaveOrder()->getIdSalesOrder();
        foreach ($quoteTransfer->getExpenses() as $expenseTransfer) {
            foreach ($expenseTransfer->getCalculatedDiscounts() as $calculatedDiscountTransfer) {
                $salesDiscountEntity = $this->createSalesDiscountEntity($calculatedDiscountTransfer);
                $salesDiscountEntity->setFkSalesOrder($idSalesOrder);
                $salesDiscountEntity->setFkSalesExpense($expenseTransfer->getIdSalesExpense());
                $this->saveDiscount($salesDiscountEntity, $calculatedDiscountTransfer);
            }
        }
    }

}

