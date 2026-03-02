<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\DecisionRule;

use DateTime;
use Generated\Shared\Transfer\ClauseTransfer;
use Spryker\Zed\Discount\Business\DecisionRule\MonthDecisionRule;
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
 * @group MonthDecisionRuleTest
 * Add your own group annotations below this line
 */
class MonthDecisionRuleTest extends BaseRuleTester
{
    public function testDecisionRuleShouldReturnTrueIfGivenDateMatchesClause(): void
    {
        $dateTime = new DateTime();

        $comparatorMock = $this->createComparatorMock();
        $comparatorMock->method('compare')->willReturnCallback(function (ClauseTransfer $clauseTransfer, $currentMonth) {
            return $clauseTransfer->getValue() === $currentMonth;
        });

        $monthDecisionRule = $this->createMonthDecisionRule($comparatorMock, $dateTime);
        $isSatisfied = $monthDecisionRule->isSatisfiedBy(
            $this->createQuoteTransfer(),
            $this->createItemTransfer(),
            $this->createClauseTransfer($dateTime->format(MonthDecisionRule::DATE_FORMAT)),
        );

        $this->assertTrue($isSatisfied);
    }

    /**
     * @param \Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface $comparatorMock
     * @param \DateTime $currentDateTime
     *
     * @return \Spryker\Zed\Discount\Business\DecisionRule\MonthDecisionRule|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createMonthDecisionRule(
        ComparatorOperatorsInterface $comparatorMock,
        DateTime $currentDateTime
    ): MonthDecisionRule {
        // PHPUnit 12 removed addMethods(), so use an anonymous concrete subclass that
        // defines getCurrentDateTime() to return the provided DateTime. This avoids
        // needing to mock non-existing methods and works across PHPUnit versions.
        $monthDecisionRule = new class ($comparatorMock, $currentDateTime) extends MonthDecisionRule {
            private DateTime $currentDateTime;

            public function __construct(ComparatorOperatorsInterface $comparator, DateTime $currentDateTime)
            {
                parent::__construct($comparator);
                $this->currentDateTime = $currentDateTime;
            }

            public function getCurrentDateTime(): DateTime
            {
                return $this->currentDateTime;
            }
        };

        return $monthDecisionRule;
    }
}
