<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Unit\Spryker\Zed\Discount\Business\QueryString\Comparator;

use Generated\Shared\Transfer\ClauseTransfer;
use Spryker\Zed\Discount\Business\Exception\ComparatorException;
use Spryker\Zed\Discount\Business\QueryString\Comparator\IsNotIn;

class IsNotInTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testAcceptShouldReturnTrueWhenIsNotInExpressionProvided()
    {
        $isNotIn = $this->createIsNotIn();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('is not in');

        $isAccepted = $isNotIn->accept($clauseTransfer);

        $this->assertTrue($isAccepted);
    }

    /**
     * @return void
     */
    public function testCompareWhenValueIsNotInClauseShouldReturnTrue()
    {
        $equal = $this->createIsNotIn();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setValue('2,3');

        $isMatching = $equal->compare($clauseTransfer, '1');

        $this->assertTrue($isMatching);
    }

    /**
     * @return void
     */
    public function testCompareWhenValueIsInClauseShouldReturnFalse()
    {
        $contains = $this->createIsNotIn();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setValue('1, 2,3');

        $isMatching = $contains->compare($clauseTransfer, '1');

        $this->assertFalse($isMatching);
    }

    /**
     * @return void
     */
    public function testCompareWhenNonScalarValueUsedShouldThrowException()
    {
        $this->expectException(ComparatorException::class);

        $contains = $this->createIsNotIn();

        $clauseTransfer = new ClauseTransfer();

        $contains->compare($clauseTransfer, []);
    }

    /**
     * @return IsNotIn
     */
    protected function createIsNotIn()
    {
        return new IsNotIn();
    }
}