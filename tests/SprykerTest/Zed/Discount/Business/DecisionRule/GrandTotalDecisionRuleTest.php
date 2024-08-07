<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\DecisionRule;

use Generated\Shared\Transfer\ClauseTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use Spryker\Zed\Discount\Business\DecisionRule\GrandTotalDecisionRule;
use Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface;
use SprykerTest\Zed\Discount\Business\BaseRuleTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group DecisionRule
 * @group GrandTotalDecisionRuleTest
 * Add your own group annotations below this line
 */
class GrandTotalDecisionRuleTest extends BaseRuleTester
{
    /**
     * @return void
     */
    public function testWhenGrandTotalMatchesShouldReturnTrue(): void
    {
        $comparatorMock = $this->createComparatorMock();
        $comparatorMock->method('compare')->willReturnCallback(function (ClauseTransfer $clauseTransfer, $grandTotal) {
            return $clauseTransfer->getValue() === $grandTotal;
        });

        $grandTotalDecisionRule = $this->createGrandTotalDecisionRule($comparatorMock);

        $quoteTransfer = $this->createQuoteTransfer();
        $totalTransfer = new TotalsTransfer();
        $totalTransfer->setGrandTotal(1000);
        $quoteTransfer->setTotals($totalTransfer);

        $isSatisfied = $grandTotalDecisionRule->isSatisfiedBy(
            $quoteTransfer,
            $this->createItemTransfer(),
            $this->createClauseTransfer(10),
        );

        $this->assertTrue($isSatisfied);
    }

    /**
     * @dataProvider grandTotalDataProvider
     *
     * @param \Generated\Shared\Transfer\TotalsTransfer $totalsTransfer
     * @param int $clauseValue
     * @param bool $expectedResult
     *
     * @return void
     */
    public function testGrandTotalDecisionRuleIsSatisfiedBy(TotalsTransfer $totalsTransfer, int $clauseValue, bool $expectedResult): void
    {
        $comparatorMock = $this->createComparatorMock();
        $comparatorMock->method('compare')->willReturnCallback(function (ClauseTransfer $clauseTransfer, $grandTotal) {
            return $clauseTransfer->getValue() <= $grandTotal;
        });

        $grandTotalDecisionRule = $this->createGrandTotalDecisionRule($comparatorMock);

        $quoteTransfer = $this->createQuoteTransfer();
        $quoteTransfer->setTotals($totalsTransfer);

        $isSatisfied = $grandTotalDecisionRule->isSatisfiedBy(
            $quoteTransfer,
            $this->createItemTransfer(),
            $this->createClauseTransfer(500),
        );

        $this->assertTrue($isSatisfied);
    }

    /**
     * @return array<string, array<\Generated\Shared\Transfer\TotalsTransfer|bool|int>>
     */
    protected function grandTotalDataProvider(): array
    {
        $totalsTransfer = (new TotalsTransfer())->setGrandTotal(10000)->setDiscountTotal(50000);

        return [
            'sum of grand total and discount total greater then clause value' => [$totalsTransfer, 500, true],
            'sum of grand total and discount total less then clause value' => [$totalsTransfer, 700, false],
        ];
    }

    /**
     * @return void
     */
    public function testWhenGrandTotalNotMatchingShouldReturnFalse(): void
    {
        $comparatorMock = $this->createComparatorMock();
        $comparatorMock->method('compare')->willReturnCallback(function (ClauseTransfer $clauseTransfer, $grandTotal) {
            return $clauseTransfer->getValue() === $grandTotal;
        });

        $grandTotalDecisionRule = $this->createGrandTotalDecisionRule($comparatorMock);

        $quoteTransfer = $this->createQuoteTransfer();
        $totalTransfer = new TotalsTransfer();
        $totalTransfer->setGrandTotal(1200);
        $quoteTransfer->setTotals($totalTransfer);

        $isSatisfied = $grandTotalDecisionRule->isSatisfiedBy(
            $quoteTransfer,
            $this->createItemTransfer(),
            $this->createClauseTransfer(10),
        );

        $this->assertFalse($isSatisfied);
    }

    /**
     * @return void
     */
    public function testWhenGrandTotalIsNotSetShouldReturnFalse(): void
    {
        $grandTotalDecisionRule = $this->createGrandTotalDecisionRule();

        $isSatisfied = $grandTotalDecisionRule->isSatisfiedBy(
            $this->createQuoteTransfer(),
            $this->createItemTransfer(),
            $this->createClauseTransfer(10),
        );

        $this->assertFalse($isSatisfied);
    }

    /**
     * @param \Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface|null $comparatorMock
     *
     * @return \Spryker\Zed\Discount\Business\DecisionRule\GrandTotalDecisionRule
     */
    protected function createGrandTotalDecisionRule(?ComparatorOperatorsInterface $comparatorMock = null): GrandTotalDecisionRule
    {
        if ($comparatorMock === null) {
            $comparatorMock = $this->createComparatorMock();
        }

        $currencyConverterMock = $this->createCurrencyConverterMock();
        $currencyConverterMock->method('convertDecimalToCent')->willReturnCallback(function (ClauseTransfer $clauseTransfer) {
            return $clauseTransfer->setValue($clauseTransfer->getValue() * 100);
        });

        return new GrandTotalDecisionRule($comparatorMock, $currencyConverterMock);
    }
}
