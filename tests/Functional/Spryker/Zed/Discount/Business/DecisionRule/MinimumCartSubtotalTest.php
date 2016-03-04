<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Functional\Spryker\Zed\Discount\Business\DecisionRule;

use Codeception\TestCase\Test;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use Spryker\Zed\Discount\Business\DecisionRule\MinimumCartSubtotal;
use Spryker\Zed\Kernel\Locator;
use Orm\Zed\Discount\Persistence\SpyDiscountDecisionRule;

/**
 * @group DiscountDecisionRuleMinimumCartSubtotalTest
 * @group Discount
 */
class MinimumCartSubtotalTest extends Test
{

    const MINIMUM_CART_SUBTOTAL_TEST_500 = 500;
    const CART_SUBTOTAL_400 = 400;
    const CART_SUBTOTAL_500 = 500;
    const CART_SUBTOTAL_1000 = 1000;

    /**
     * @return void
     */
    public function testShouldReturnTrueForAnOrderWithAHighEnoughSubtotal()
    {
        $quoteTransfer = $this->createQuoteTransferWithSubtotal(self::CART_SUBTOTAL_1000);
        $decisionRuleEntity = $this->getDecisionRuleEntity(self::MINIMUM_CART_SUBTOTAL_TEST_500);

        $decisionRule = $this->createMinimumCartSubtotal();
        $result = $decisionRule->isMinimumCartSubtotalReached($quoteTransfer, $decisionRuleEntity);

        $this->assertTrue($result->isSuccess());
    }

    /**
     * @return void
     */
    public function testShouldReturnFalseForAnOrderWithATooLowSubtotal()
    {
        $quoteTransfer = $this->createQuoteTransferWithSubtotal(self::CART_SUBTOTAL_400);
        $decisionRuleEntity = $this->getDecisionRuleEntity(self::MINIMUM_CART_SUBTOTAL_TEST_500);

        $decisionRule = $this->createMinimumCartSubtotal();
        $result = $decisionRule->isMinimumCartSubtotalReached($quoteTransfer, $decisionRuleEntity);

        $this->assertFalse($result->isSuccess());
    }

    /**
     * @return void
     */
    public function testShouldReturnTrueForAnOrderWithAExactlyMatchingSubtotal()
    {
        $quoteTransfer = $this->createQuoteTransferWithSubtotal(self::CART_SUBTOTAL_500);

        $decisionRuleEntity = $this->getDecisionRuleEntity(self::MINIMUM_CART_SUBTOTAL_TEST_500);

        $decisionRule = $this->createMinimumCartSubtotal();
        $result = $decisionRule->isMinimumCartSubtotalReached($quoteTransfer, $decisionRuleEntity);

        $this->assertTrue($result->isSuccess());
    }

    /**
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function createQuoteTransferWithSubtotal($subtotal)
    {
        $quoteTransfer = new QuoteTransfer();
        $totals = new TotalsTransfer();
        $totals->setSubtotal($subtotal);
        $quoteTransfer->setTotals($totals);

        return $quoteTransfer;
    }

    /**
     * @param int $value
     *
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountDecisionRule
     */
    protected function getDecisionRuleEntity($value)
    {
        $decisionRule = new SpyDiscountDecisionRule();
        $decisionRule->setValue($value);

        return $decisionRule;
    }

    /**
     * @return \Spryker\Shared\Kernel\AbstractLocatorLocator|\Generated\Zed\Ide\AutoCompletion
     */
    protected function getLocator()
    {
        return Locator::getInstance();
    }

    /**
     * @return \Spryker\Zed\Discount\Business\DecisionRule\MinimumCartSubtotal
     */
    protected function createMinimumCartSubtotal()
    {
        return new MinimumCartSubtotal();
    }

}
