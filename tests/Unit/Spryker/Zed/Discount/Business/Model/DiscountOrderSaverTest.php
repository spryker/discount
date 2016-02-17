<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Unit\Spryker\Zed\Discount\Business\Model;

use Generated\Shared\Transfer\CalculatedDiscountTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Spryker\Zed\Discount\Business\DiscountFacade;
use Spryker\Zed\Discount\Business\Model\DiscountOrderSaver;
use Spryker\Zed\Discount\Business\Model\VoucherCode;
use Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucher;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucherPool;

/**
 * @group Spryker
 * @group Zed
 * @group DiscountCheckoutConnector
 * @group Business
 * @group DiscountOrderSaver
 */
class DiscountOrderSaverTest extends \PHPUnit_Framework_TestCase
{

    const DISCOUNT_DISPLAY_NAME = 'discount';
    const DISCOUNT_AMOUNT = 100;
    const DISCOUNT_ACTION = 'action';

    const ID_SALES_ORDER = 1;
    const USED_CODE_1 = 'used code 1';
    const USED_CODE_2 = 'used code 2';

    /**
     * @return void
     */
    public function testSaveDiscountMustSaveSalesItemsDiscount()
    {
        $discountSaver = $this->getDiscountOrderSaverMock(['persistSalesDiscount']);
        $discountSaver->expects($this->once())
            ->method('persistSalesDiscount');

        $quoteTransfer = new QuoteTransfer();

        $discountTransfer = new CalculatedDiscountTransfer();
        $discountTransfer->setDisplayName(self::DISCOUNT_DISPLAY_NAME);
        $discountTransfer->setUnitGrossAmount(self::DISCOUNT_AMOUNT);

        $orderItemTransfer = new ItemTransfer();
        $orderItemTransfer->addCalculatedDiscount($discountTransfer);

        $quoteTransfer->addItem($orderItemTransfer);

        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        $saverOrderTransfer = new SaveOrderTransfer();
        $saverOrderTransfer->setIdSalesOrder(self::ID_SALES_ORDER);
        $checkoutResponseTransfer->setSaveOrder($saverOrderTransfer);

        $discountSaver->saveDiscounts($quoteTransfer, $checkoutResponseTransfer);
    }

    /**
     * @return void
     */
    public function testSaveDiscountMustNotSaveSalesDiscountCodeIfUsedCodesCanNotBeFound()
    {
        $discountSaver = $this->getDiscountOrderSaverMock(['persistSalesDiscount', 'saveUsedCodes']);
        $discountSaver->expects($this->once())
            ->method('persistSalesDiscount');
        $discountSaver->expects($this->never())
            ->method('saveUsedCodes');

        $quoteTransfer = new QuoteTransfer();

        $calculatedDiscountTransfer = new CalculatedDiscountTransfer();

        $orderItemTransfer = new ItemTransfer();
        $orderItemTransfer->addCalculatedDiscount($calculatedDiscountTransfer);

        $quoteTransfer->addItem($orderItemTransfer);

        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        $saverOrderTransfer = new SaveOrderTransfer();
        $saverOrderTransfer->setIdSalesOrder(self::ID_SALES_ORDER);
        $checkoutResponseTransfer->setSaveOrder($saverOrderTransfer);

        $discountSaver->saveDiscounts($quoteTransfer, $checkoutResponseTransfer);
    }

    /**
     * @return void
     */
    public function testSaveDiscountMustSaveSalesDiscountCodesIfUsedCodesPresent()
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
        $calculatedDiscountTransfer->setVoucherCode(self::USED_CODE_1);

        $quoteTransfer = new QuoteTransfer();

        $orderItemTransfer = new ItemTransfer();
        $orderItemTransfer->addCalculatedDiscount($calculatedDiscountTransfer);
        $quoteTransfer->addItem($orderItemTransfer);

        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        $saverOrderTransfer = new SaveOrderTransfer();
        $saverOrderTransfer->setIdSalesOrder(self::ID_SALES_ORDER);
        $checkoutResponseTransfer->setSaveOrder($saverOrderTransfer);

        $discountSaver->saveDiscounts($quoteTransfer, $checkoutResponseTransfer);
    }

    /**
     * @return void
     */
    public function testSaveDiscountMustNotSaveSalesDiscountCodesIfUsedCodeCanNotBeFound()
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
        $calculatedDiscountTransfer->setVoucherCode(self::USED_CODE_1);

        $quoteTransfer = new QuoteTransfer();

        $orderItemTransfer = new ItemTransfer();
        $orderItemTransfer->addCalculatedDiscount($calculatedDiscountTransfer);
        $quoteTransfer->addItem($orderItemTransfer);

        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        $saverOrderTransfer = new SaveOrderTransfer();
        $saverOrderTransfer->setIdSalesOrder(self::ID_SALES_ORDER);
        $checkoutResponseTransfer->setSaveOrder($saverOrderTransfer);

        $discountSaver->saveDiscounts($quoteTransfer, $checkoutResponseTransfer);
    }

    /**
     * @return SpyDiscountVoucher
     */
    public function getDiscountVoucherEntityByCode()
    {
        $discountVoucherEntity = new SpyDiscountVoucher();
        $discountVoucherPoolEntity = new SpyDiscountVoucherPool();
        $discountVoucherEntity->setVoucherPool($discountVoucherPoolEntity);

        return $discountVoucherEntity;
    }

    /**
     * @param array $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|DiscountQueryContainerInterface
     */
    private function getDiscountQueryContainerMock(array $methods = [])
    {
        $discountQueryContainerMock = $this->getMock('Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface', $methods);

        return $discountQueryContainerMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|VoucherCode
     */
    private function getVoucherCodeMock()
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
     * @return \PHPUnit_Framework_MockObject_MockObject|DiscountOrderSaver
     */
    private function getDiscountOrderSaverMock(array $discountSaverMethods = [], array $queryContainerMethods = [])
    {
        $discountSaverMock = $this->getMock(
            DiscountOrderSaver::class,
            $discountSaverMethods,
            [$this->getDiscountQueryContainerMock($queryContainerMethods), $this->getVoucherCodeMock()]
        );

        return $discountSaverMock;
    }

}
