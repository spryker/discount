<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Unit\Spryker\Zed\Discount\Business\Calculator;

use Generated\Shared\Transfer\CollectedDiscountTransfer;
use Generated\Shared\Transfer\DiscountTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Orm\Zed\Discount\Persistence\SpyDiscount;
use Orm\Zed\Discount\Persistence\SpyDiscountQuery;
use Spryker\Zed\Discount\Business\Calculator\CalculatorInterface;
use Spryker\Zed\Discount\Business\Calculator\Discount;
use Spryker\Zed\Discount\Business\Exception\QueryStringException;
use Spryker\Zed\Discount\Business\QueryString\SpecificationBuilderInterface;
use Spryker\Zed\Discount\Business\QueryString\Specification\DecisionRuleSpecification\DecisionRuleSpecificationInterface;
use Spryker\Zed\Discount\Business\Voucher\VoucherValidatorInterface;
use Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface;
use Spryker\Zed\SalesSplit\Business\Model\Validation\ValidatorInterface;

class DiscountTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return void
     */
    public function testCalculateDiscountWhenThereIsNoDiscountApplicableShouldReturnUnmodifiedQuote()
    {
        $queryContainerMock = $this->createDiscountQueryContainerMock();

        $discountQueryMock = $this->createDiscountQueryMock();
        $discountQueryMock->method('find')->willReturn([]);

        $queryContainerMock->expects($this->once())
            ->method('queryCartRulesIncludingSpecifiedVouchers')
            ->willReturn($discountQueryMock);

        $discount = $this->createDiscount($queryContainerMock);

        $quoteTransfer = $this->createQuoteTransfer();

        $discount->calculate($quoteTransfer);
    }

    /**
     * @return void
     */
    public function testCalculateWhenDiscountApplicableAndIsCartRuleShouldUpdateQuoteWithCartRules()
    {
        $queryContainerMock = $this->createDiscountQueryContainerMock();

        $discounts[] = $this->createDiscountEntity(100);

        $discountQueryMock = $this->createDiscountQueryMock();
        $discountQueryMock->method('find')->willReturn($discounts);

        $queryContainerMock->expects($this->once())
            ->method('queryCartRulesIncludingSpecifiedVouchers')
            ->willReturn($discountQueryMock);

        $decisionRuleSpecificationMock = $this->createDecisionRuleSpecificationMock();
        $decisionRuleSpecificationMock->method('isSatisfiedBy')
            ->willReturn(true);

        $specificationBuilderMock = $this->createSpecificationBuilderMock();
        $specificationBuilderMock->method('buildFromQueryString')
            ->willReturn($decisionRuleSpecificationMock);

        $calculatorMock = $this->createCalculatorMock();

        $collectedDiscounts = $this->createCollectedDiscounts();

        $calculatorMock->method('calculate')->willReturn($collectedDiscounts);

        $discount = $this->createDiscount(
            $queryContainerMock,
            $calculatorMock,
            $specificationBuilderMock
        );

        $quoteTransfer = $this->createQuoteTransfer();

        $updatedQuoteTransfer = $discount->calculate($quoteTransfer);

        $this->assertCount(1, $updatedQuoteTransfer->getCartRuleDiscounts());
    }

    /**
     * @return void
     */
    public function testCalculateWhenDiscountApplicableAndIsVoucherCodeShouldUpdateQuoteWithVouchers()
    {
        $queryContainerMock = $this->createDiscountQueryContainerMock();

        $discounts[] = $this->createDiscountEntity(100, 123);

        $discountQueryMock = $this->createDiscountQueryMock();
        $discountQueryMock->method('find')->willReturn($discounts);

        $queryContainerMock->expects($this->once())
            ->method('queryCartRulesIncludingSpecifiedVouchers')
            ->willReturn($discountQueryMock);

        $decisionRuleSpecificationMock = $this->createDecisionRuleSpecificationMock();
        $decisionRuleSpecificationMock->method('isSatisfiedBy')
            ->willReturn(true);

        $specificationBuilderMock = $this->createSpecificationBuilderMock();
        $specificationBuilderMock->method('buildFromQueryString')
            ->willReturn($decisionRuleSpecificationMock);

        $calculatorMock = $this->createCalculatorMock();

        $collectedDiscounts = [];
        $collectedDiscountTransfer = new CollectedDiscountTransfer();
        $discountTransfer = new DiscountTransfer();
        $discountTransfer->setAmount(250);
        $discountTransfer->setVoucherCode(123);
        $collectedDiscountTransfer->setDiscount($discountTransfer);
        $collectedDiscounts[] = $collectedDiscountTransfer;

        $calculatorMock->method('calculate')->willReturn($collectedDiscounts);

        $discount = $this->createDiscount(
            $queryContainerMock,
            $calculatorMock,
            $specificationBuilderMock
        );

        $quoteTransfer = $this->createQuoteTransfer();
        $discountTransfer = new DiscountTransfer();
        $discountTransfer->setVoucherCode(123);
        $quoteTransfer->setVoucherDiscounts(new \ArrayObject([$discountTransfer]));

        $updatedQuoteTransfer = $discount->calculate($quoteTransfer);

        $this->assertCount(1, $updatedQuoteTransfer->getVoucherDiscounts());
    }

    /**
     * @return void
     */
    public function testCalculateWhenDecisionRuleQueryStringDoesNotMatchShouldSkipDiscount()
    {
        $queryContainerMock = $this->createDiscountQueryContainerMock();

        $discounts[] = $this->createDiscountEntity(100, 123);

        $discountQueryMock = $this->createDiscountQueryMock();
        $discountQueryMock->method('find')->willReturn($discounts);

        $queryContainerMock->expects($this->once())
            ->method('queryCartRulesIncludingSpecifiedVouchers')
            ->willReturn($discountQueryMock);

        $decisionRuleSpecificationMock = $this->createDecisionRuleSpecificationMock();
        $decisionRuleSpecificationMock->method('isSatisfiedBy')
            ->willReturn(false);

        $specificationBuilderMock = $this->createSpecificationBuilderMock();
        $specificationBuilderMock->method('buildFromQueryString')
            ->willReturn($decisionRuleSpecificationMock);

        $discount = $this->createDiscount(
            $queryContainerMock,
            null,
            $specificationBuilderMock
        );

        $quoteTransfer = $this->createQuoteTransfer();

        $updatedQuoteTransfer = $discount->calculate($quoteTransfer);

        $this->assertCount(0, $updatedQuoteTransfer->getVoucherDiscounts());
        $this->assertCount(0, $updatedQuoteTransfer->getCartRuleDiscounts());
    }

    /**
     * @return void
     */
    public function testCalculateWhenDecisionRuleQueryStringThrowsExceptionShouldSkipItAndLogError()
    {
        $queryContainerMock = $this->createDiscountQueryContainerMock();

        $discounts[] = $this->createDiscountEntity(100, 123);

        $discountQueryMock = $this->createDiscountQueryMock();
        $discountQueryMock->method('find')->willReturn($discounts);

        $queryContainerMock->expects($this->once())
            ->method('queryCartRulesIncludingSpecifiedVouchers')
            ->willReturn($discountQueryMock);

        $specificationBuilderMock = $this->createSpecificationBuilderMock();
        $specificationBuilderMock->method('buildFromQueryString')
            ->willThrowException(new QueryStringException());

        $discount = $this->createDiscount(
            $queryContainerMock,
            null,
            $specificationBuilderMock
        );

        $quoteTransfer = $this->createQuoteTransfer();

        $updatedQuoteTransfer = $discount->calculate($quoteTransfer);

        $this->assertCount(0, $updatedQuoteTransfer->getVoucherDiscounts());
        $this->assertCount(0, $updatedQuoteTransfer->getCartRuleDiscounts());
    }

    /**
     * @return void
     */
    public function testCalculateWhenVoucherDiscountIsUsedButValidationFailsShouldSkipDiscount()
    {
        $queryContainerMock = $this->createDiscountQueryContainerMock();

        $discounts[] = $this->createDiscountEntity(100, 123);

        $discountQueryMock = $this->createDiscountQueryMock();
        $discountQueryMock->method('find')->willReturn($discounts);

        $queryContainerMock->expects($this->once())
            ->method('queryCartRulesIncludingSpecifiedVouchers')
            ->willReturn($discountQueryMock);

        $decisionRuleSpecificationMock = $this->createDecisionRuleSpecificationMock();
        $decisionRuleSpecificationMock->method('isSatisfiedBy')
            ->willReturn(true);

        $specificationBuilderMock = $this->createSpecificationBuilderMock();
        $specificationBuilderMock->method('buildFromQueryString')
            ->willReturn($decisionRuleSpecificationMock);

        $calculatorMock = $this->createCalculatorMock();

        $collectedDiscounts = [];
        $collectedDiscountTransfer = new CollectedDiscountTransfer();
        $discountTransfer = new DiscountTransfer();
        $discountTransfer->setAmount(250);
        $discountTransfer->setVoucherCode(123);
        $collectedDiscountTransfer->setDiscount($discountTransfer);
        $collectedDiscounts[] = $collectedDiscountTransfer;

        $calculatorMock->method('calculate')->willReturn($collectedDiscounts);

        $voucherValidatorMock = $this->createVoucherValidatorMock();
        $voucherValidatorMock->method('isUsable')->willReturn(false);

        $discount = $this->createDiscount(
            $queryContainerMock,
            null,
            $specificationBuilderMock,
            $voucherValidatorMock
        );

        $quoteTransfer = $this->createQuoteTransfer();

        $updatedQuoteTransfer = $discount->calculate($quoteTransfer);

        $this->assertCount(0, $updatedQuoteTransfer->getVoucherDiscounts());
        $this->assertCount(0, $updatedQuoteTransfer->getCartRuleDiscounts());
    }

    /**
     * @return void
     */
    public function testCalculateWhenDecisionRuleNotProvidedShouldTakeDiscount()
    {
        $queryContainerMock = $this->createDiscountQueryContainerMock();

        $discounts[] = $this->createDiscountEntity(100)->setDecisionRuleQueryString('');

        $discountQueryMock = $this->createDiscountQueryMock();
        $discountQueryMock->method('find')->willReturn($discounts);

        $queryContainerMock->expects($this->once())
            ->method('queryCartRulesIncludingSpecifiedVouchers')
            ->willReturn($discountQueryMock);

        $calculatorMock = $this->createCalculatorMock();

        $collectedDiscounts = $this->createCollectedDiscounts();

        $calculatorMock->method('calculate')->willReturn($collectedDiscounts);

        $discount = $this->createDiscount(
            $queryContainerMock,
            $calculatorMock
        );

        $quoteTransfer = $this->createQuoteTransfer();

        $updatedQuoteTransfer = $discount->calculate($quoteTransfer);

        $this->assertCount(1, $updatedQuoteTransfer->getCartRuleDiscounts());
    }

    /**
     * @param \Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface|null $discountQueryContainerMock
     * @param \Spryker\Zed\Discount\Business\Calculator\CalculatorInterface|null $calculatorMock
     * @param \Spryker\Zed\Discount\Business\QueryString\SpecificationBuilderInterface|null $specificationBuilderMock
     * @param \Spryker\Zed\Discount\Business\Voucher\VoucherValidatorInterface|null $voucherValidatorMock
     *
     * @return \Spryker\Zed\Discount\Business\Calculator\Discount
     */
    protected function createDiscount(
        DiscountQueryContainerInterface $discountQueryContainerMock = null,
        CalculatorInterface $calculatorMock = null,
        SpecificationBuilderInterface $specificationBuilderMock = null,
        VoucherValidatorInterface $voucherValidatorMock = null
    ) {
        if (!$discountQueryContainerMock) {
            $discountQueryContainerMock = $this->createDiscountQueryContainerMock();
        }

        if (!$calculatorMock) {
            $calculatorMock = $this->createCalculatorMock();
            $calculatorMock->method('calculate')->willReturn([]);
        }

        if (!$specificationBuilderMock) {
            $specificationBuilderMock = $this->createSpecificationBuilderMock();
        }

        if (!$voucherValidatorMock) {
            $voucherValidatorMock = $this->createVoucherValidatorMock();
        }

        return new Discount(
            $discountQueryContainerMock,
            $calculatorMock,
            $specificationBuilderMock,
            $voucherValidatorMock
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface
     */
    protected function createDiscountQueryContainerMock()
    {
        return $this->getMock(DiscountQueryContainerInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\Discount\Business\Calculator\CalculatorInterface
     */
    protected function createCalculatorMock()
    {
        return $this->getMock(CalculatorInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\Discount\Business\QueryString\SpecificationBuilderInterface
     */
    protected function createSpecificationBuilderMock()
    {
        return $this->getMock(SpecificationBuilderInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\Discount\Business\Voucher\VoucherValidatorInterface
     */
    protected function createVoucherValidatorMock()
    {
        return $this->getMock(VoucherValidatorInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Orm\Zed\Discount\Persistence\SpyDiscountQuery
     */
    protected function createDiscountQueryMock()
    {
        return $this->getMock(SpyDiscountQuery::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\Discount\Business\QueryString\Specification\DecisionRuleSpecification\DecisionRuleSpecificationInterface
     */
    protected function createDecisionRuleSpecificationMock()
    {
        return $this->getMock(DecisionRuleSpecificationInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\SalesSplit\Business\Model\Validation\ValidatorInterface
     */
    protected function createValidatorMock()
    {
        return $this->getMock(ValidatorInterface::class);
    }

    /**
     * @param int $amount
     * @param string $voucherCoder
     *
     * @return \Orm\Zed\Discount\Persistence\SpyDiscount
     */
    protected function createDiscountEntity($amount, $voucherCoder = null)
    {
        $discountEntity = new SpyDiscount();
        $discountEntity->setVirtualColumn('VoucherCode', $voucherCoder);
        $discountEntity->setDecisionRuleQueryString('query string');
        $discountEntity->setAmount($amount);

        return $discountEntity;
    }

    /**
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function createQuoteTransfer()
    {
        $quoteTransfer = new QuoteTransfer();

        $itemTransfer = new ItemTransfer();
        $quoteTransfer->addItem($itemTransfer);

        return $quoteTransfer;
    }

    /**
     * @return array
     */
    protected function createCollectedDiscounts()
    {
        $collectedDiscounts = [];
        $collectedDiscountTransfer = new CollectedDiscountTransfer();
        $discountTransfer = new DiscountTransfer();
        $discountTransfer->setAmount(250);
        $collectedDiscountTransfer->setDiscount($discountTransfer);
        $collectedDiscounts[] = $collectedDiscountTransfer;

        return $collectedDiscounts;
    }

}
