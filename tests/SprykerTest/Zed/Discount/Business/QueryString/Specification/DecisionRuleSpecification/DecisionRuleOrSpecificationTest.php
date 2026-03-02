<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\QueryString\Specification\DecisionRuleSpecification;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Discount\Business\QueryString\Specification\DecisionRuleSpecification\DecisionRuleOrSpecification;
use Spryker\Zed\Discount\Business\QueryString\Specification\DecisionRuleSpecification\DecisionRuleSpecificationInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group QueryString
 * @group Specification
 * @group DecisionRuleSpecification
 * @group DecisionRuleOrSpecificationTest
 * Add your own group annotations below this line
 */
class DecisionRuleOrSpecificationTest extends Unit
{
    public function testIsSatisfiedWhenAnyReturnTrueShouldEvaluateToTrue(): void
    {
        $leftSpecificationMock = $this->createDecisionRuleSpecificationMock();
        $leftSpecificationMock->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $rightSpecificationMock = $this->createDecisionRuleSpecificationMock();
        $rightSpecificationMock->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(true);

        $decisionRuleOrSpecification = $this->createDecisionRuleOrSpecification($leftSpecificationMock, $rightSpecificationMock);

        $isSatisfied = $decisionRuleOrSpecification->isSatisfiedBy(new QuoteTransfer(), new ItemTransfer());

        $this->assertTrue($isSatisfied);
    }

    public function testIsSatisfiedWhenAllOfSpecificationReturnsFalseShouldEvaluateToFalse(): void
    {
        $leftSpecificationMock = $this->createDecisionRuleSpecificationMock();
        $leftSpecificationMock->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $rightSpecificationMock = $this->createDecisionRuleSpecificationMock();
        $rightSpecificationMock->expects($this->once())
            ->method('isSatisfiedBy')
            ->willReturn(false);

        $decisionRuleAndSpecification = $this->createDecisionRuleOrSpecification($leftSpecificationMock, $rightSpecificationMock);

        $isSatisfied = $decisionRuleAndSpecification->isSatisfiedBy(new QuoteTransfer(), new ItemTransfer());

        $this->assertFalse($isSatisfied);
    }

    protected function createDecisionRuleOrSpecification(
        DecisionRuleSpecificationInterface $leftMock,
        DecisionRuleSpecificationInterface $rightMock
    ): DecisionRuleOrSpecification {
        return new DecisionRuleOrSpecification($leftMock, $rightMock);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Discount\Business\QueryString\Specification\DecisionRuleSpecification\DecisionRuleSpecificationInterface
     */
    protected function createDecisionRuleSpecificationMock(): DecisionRuleSpecificationInterface
    {
        return $this->getMockBuilder(DecisionRuleSpecificationInterface::class)->getMock();
    }
}
