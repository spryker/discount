<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\DecisionRule;

use DateTime;
use Generated\Shared\Transfer\ClauseTransfer;
use Spryker\Zed\Discount\Business\DecisionRule\DayOfWeekDecisionRule;
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
 * @group DayOfTheWeekDecisionRuleTest
 * Add your own group annotations below this line
 */
class DayOfTheWeekDecisionRuleTest extends BaseRuleTester
{
    /**
     * @return void
     */
    public function testDecisionRuleShouldReturnTrueIfGivenDateMatchesClause(): void
    {
        $dateTime = new DateTime();

        $comparatorMock = $this->createComparatorMock();
        $comparatorMock->method('compare')->willReturnCallback(function (ClauseTransfer $clauseTransfer, $calendarWeek) {
            return $clauseTransfer->getValue() === $calendarWeek;
        });

        $calendarWeekDecisionRule = $this->createDateOfTheWeekDecisionRule($comparatorMock, $dateTime);
        $isSatisfied = $calendarWeekDecisionRule->isSatisfiedBy(
            $this->createQuoteTransfer(),
            $this->createItemTransfer(),
            $this->createClauseTransfer($dateTime->format(DayOfWeekDecisionRule::DATE_FORMAT)),
        );

        $this->assertTrue($isSatisfied);
    }

    /**
     * @param \Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface $comparatorMock
     * @param \DateTime $currentDateTime
     *
     * @return \Spryker\Zed\Discount\Business\DecisionRule\DayOfWeekDecisionRule|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createDateOfTheWeekDecisionRule(
        ComparatorOperatorsInterface $comparatorMock,
        DateTime $currentDateTime
    ): DayOfWeekDecisionRule {
        /** @var \Spryker\Zed\Discount\Business\DecisionRule\DayOfWeekDecisionRule|\PHPUnit\Framework\MockObject\MockObject $dayOfWeekDecisionRule */
        $dayOfWeekDecisionRule = $this->getMockBuilder(DayOfWeekDecisionRule::class)
            ->setMethods(['getCurrentDateTime'])
            ->setConstructorArgs([$comparatorMock])
            ->getMock();
        $dayOfWeekDecisionRule->method('getCurrentDateTime')->willReturn($currentDateTime);

        return $dayOfWeekDecisionRule;
    }
}
