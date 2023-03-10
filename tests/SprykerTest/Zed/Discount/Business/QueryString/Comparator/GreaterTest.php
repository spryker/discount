<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\QueryString\Comparator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ClauseTransfer;
use Spryker\Zed\Discount\Business\Exception\ComparatorException;
use Spryker\Zed\Discount\Business\QueryString\Comparator\Greater;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group QueryString
 * @group Comparator
 * @group GreaterTest
 * Add your own group annotations below this line
 */
class GreaterTest extends Unit
{
    /**
     * @return void
     */
    public function testAcceptShouldReturnTrueWhenMoreExpressionProvided(): void
    {
        $more = $this->createMore();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('>');

        $isAccepted = $more->accept($clauseTransfer);

        $this->assertTrue($isAccepted);
    }

    /**
     * @return void
     */
    public function testCompareWhenClauseValueIsBiggerThanProvidedShouldReturnTrue(): void
    {
        $more = $this->createMore();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setValue('1');

        $isMatching = $more->compare($clauseTransfer, '2');

        $this->assertTrue($isMatching);
    }

    /**
     * @return void
     */
    public function testCompareWhenClauseValueIsSmallerThanProvidedShouldReturnFalse(): void
    {
        $more = $this->createMore();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setValue('2');

        $isMatching = $more->compare($clauseTransfer, '1');

        $this->assertFalse($isMatching);
    }

    /**
     * @return void
     */
    public function testCompareWhenNonNumericValueUsedShouldThrowException(): void
    {
        $this->expectException(ComparatorException::class);

        $more = $this->createMore();

        $clauseTransfer = new ClauseTransfer();

        $more->compare($clauseTransfer, 'as');
    }

    /**
     * @return void
     */
    public function testCompareShouldReturnFalseWhenEmptyValueIsProvided(): void
    {
        // Arrange
        $clauseTransfer = (new ClauseTransfer())->setValue('1');

        // Act
        $isMatching = $this->createMore()->compare($clauseTransfer, '');

        // Assert
        $this->assertFalse($isMatching);
    }

    /**
     * @return void
     */
    public function testIsValueValidShouldReturnFalseWhenEmptyValueIsProvided(): void
    {
        // Act
        $isValueValid = $this->createMore()->isValidValue('');

        // Assert
        $this->assertFalse($isValueValid);
    }

    /**
     * @return \Spryker\Zed\Discount\Business\QueryString\Comparator\Greater
     */
    protected function createMore(): Greater
    {
        return new Greater();
    }
}
