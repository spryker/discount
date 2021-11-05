<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\Persistence;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CalculatedDiscountTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucher;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucherPool;
use Spryker\Zed\Discount\Business\Checkout\DiscountOrderSaver;
use Spryker\Zed\Discount\Business\Voucher\VoucherCode;
use Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group Persistence
 * @group DiscountOrderSaverTest
 * Add your own group annotations below this line
 */
class DiscountOrderSaverTest extends Unit
{
    /**
     * @var string
     */
    public const DISCOUNT_DISPLAY_NAME = 'discount';

    /**
     * @var int
     */
    public const DISCOUNT_AMOUNT = 100;

    /**
     * @var string
     */
    public const DISCOUNT_ACTION = 'action';

    /**
     * @var int
     */
    public const ID_SALES_ORDER = 1;

    /**
     * @var string
     */
    public const USED_CODE_1 = 'used code 1';

    /**
     * @var string
     */
    public const USED_CODE_2 = 'used code 2';

    /**
     * @return void
     */
    public function testSaveDiscountMustSaveSalesItemsDiscount(): void
    {
        $discountSaver = $this->getDiscountOrderSaverMock(['persistSalesDiscount']);
        $discountSaver->expects($this->once())
            ->method('persistSalesDiscount');

        $quoteTransfer = new QuoteTransfer();
        $quoteTransfer->setStore($this->getCurrentStore());

        $discountTransfer = new CalculatedDiscountTransfer();
        $discountTransfer->setDisplayName(static::DISCOUNT_DISPLAY_NAME);
        $discountTransfer->setSumAmount(static::DISCOUNT_AMOUNT);

        $orderItemTransfer = new ItemTransfer();
        $orderItemTransfer->addCalculatedDiscount($discountTransfer);

        $quoteTransfer->addItem($orderItemTransfer);

        $saverOrderTransfer = new SaveOrderTransfer();
        $saverOrderTransfer->setIdSalesOrder(static::ID_SALES_ORDER);
        $saverOrderTransfer->setOrderItems($quoteTransfer->getItems());

        $discountSaver->saveOrderDiscounts($quoteTransfer, $saverOrderTransfer);
    }

    /**
     * @return void
     */
    public function testSaveDiscountMustNotSaveSalesDiscountCodeIfUsedCodesCanNotBeFound(): void
    {
        $discountSaver = $this->getDiscountOrderSaverMock(['persistSalesDiscount', 'saveUsedCodes']);
        $discountSaver->expects($this->once())
            ->method('persistSalesDiscount');
        $discountSaver->expects($this->never())
            ->method('saveUsedCodes');

        $quoteTransfer = new QuoteTransfer();

        $calculatedDiscountTransfer = new CalculatedDiscountTransfer();
        $calculatedDiscountTransfer->setSumAmount(static::DISCOUNT_AMOUNT);

        $orderItemTransfer = new ItemTransfer();
        $orderItemTransfer->addCalculatedDiscount($calculatedDiscountTransfer);

        $quoteTransfer->addItem($orderItemTransfer);

        $saverOrderTransfer = new SaveOrderTransfer();
        $saverOrderTransfer->setIdSalesOrder(static::ID_SALES_ORDER);
        $saverOrderTransfer->setOrderItems($quoteTransfer->getItems());

        $discountSaver->saveOrderDiscounts($quoteTransfer, $saverOrderTransfer);
    }

    /**
     * @return void
     */
    public function testSaveDiscountMustSaveSalesDiscountCodesIfUsedCodesPresent(): void
    {
        $discountSaver = $this->getDiscountOrderSaverMock(['persistSalesDiscount', 'persistSalesDiscountCode', 'getDiscountVoucherEntityByCode']);
        $discountSaver->expects($this->once())
            ->method('persistSalesDiscount');
        $discountSaver->expects($this->exactly(1))
            ->method('persistSalesDiscountCode');
        $discountSaver->expects($this->exactly(1))
            ->method('getDiscountVoucherEntityByCode')
            ->will($this->returnCallback([$this, 'getDiscountVoucherEntityByCode']));

        $calculatedDiscountTransfer = new CalculatedDiscountTransfer();
        $calculatedDiscountTransfer->setVoucherCode(static::USED_CODE_1);
        $calculatedDiscountTransfer->setSumAmount(static::DISCOUNT_AMOUNT);

        $quoteTransfer = new QuoteTransfer();

        $orderItemTransfer = new ItemTransfer();
        $orderItemTransfer->addCalculatedDiscount($calculatedDiscountTransfer);
        $quoteTransfer->addItem($orderItemTransfer);

        $saverOrderTransfer = new SaveOrderTransfer();
        $saverOrderTransfer->setIdSalesOrder(static::ID_SALES_ORDER);
        $saverOrderTransfer->setOrderItems($quoteTransfer->getItems());

        $discountSaver->saveOrderDiscounts($quoteTransfer, $saverOrderTransfer);
    }

    /**
     * @return void
     */
    public function testSaveDiscountMustNotSaveSalesDiscountCodesIfUsedCodeCanNotBeFound(): void
    {
        $discountSaver = $this->getDiscountOrderSaverMock(['persistSalesDiscount', 'persistSalesDiscountCode', 'getDiscountVoucherEntityByCode']);
        $discountSaver->expects($this->once())
            ->method('persistSalesDiscount');
        $discountSaver->expects($this->never())
            ->method('persistSalesDiscountCode');
        $discountSaver->expects($this->once())
            ->method('getDiscountVoucherEntityByCode')
            ->will($this->returnValue(false));

        $calculatedDiscountTransfer = new CalculatedDiscountTransfer();
        $calculatedDiscountTransfer->setVoucherCode(static::USED_CODE_1);
        $calculatedDiscountTransfer->setSumAmount(static::DISCOUNT_AMOUNT);

        $quoteTransfer = new QuoteTransfer();

        $orderItemTransfer = new ItemTransfer();
        $orderItemTransfer->addCalculatedDiscount($calculatedDiscountTransfer);
        $quoteTransfer->addItem($orderItemTransfer);

        $saverOrderTransfer = new SaveOrderTransfer();
        $saverOrderTransfer->setIdSalesOrder(static::ID_SALES_ORDER);
        $saverOrderTransfer->setOrderItems($quoteTransfer->getItems());

        $discountSaver->saveOrderDiscounts($quoteTransfer, $saverOrderTransfer);
    }

    /**
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountVoucher
     */
    public function getDiscountVoucherEntityByCode(): SpyDiscountVoucher
    {
        $discountVoucherEntity = new SpyDiscountVoucher();
        $discountVoucherPoolEntity = new SpyDiscountVoucherPool();
        $discountVoucherEntity->setVoucherPool($discountVoucherPoolEntity);

        return $discountVoucherEntity;
    }

    /**
     * @param array $methods
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface
     */
    private function getDiscountQueryContainerMock(array $methods = []): DiscountQueryContainerInterface
    {
        $discountQueryContainerMock = $this->getMockBuilder(DiscountQueryContainerInterface::class)->setMethods($methods)->getMock();

        return $discountQueryContainerMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Discount\Business\Voucher\VoucherCode
     */
    private function getVoucherCodeMock(): VoucherCode
    {
        $discountQueryContainerMock = $this->getMockBuilder(VoucherCode::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $discountQueryContainerMock;
    }

    /**
     * @param array $discountSaverMethods
     * @param array $queryContainerMethods
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Discount\Business\Checkout\DiscountOrderSaver
     */
    private function getDiscountOrderSaverMock(array $discountSaverMethods = [], array $queryContainerMethods = []): DiscountOrderSaver
    {
        $discountSaverMock = $this->getMockBuilder(DiscountOrderSaver::class)->setMethods($discountSaverMethods)
            ->setConstructorArgs([$this->getDiscountQueryContainerMock($queryContainerMethods), $this->getVoucherCodeMock()])
            ->getMock();

        return $discountSaverMock;
    }

    /**
     * @return \Generated\Shared\Transfer\StoreTransfer
     */
    protected function getCurrentStore(): StoreTransfer
    {
        return (new StoreTransfer())
            ->setIdStore(1)
            ->setName('DE');
    }
}
