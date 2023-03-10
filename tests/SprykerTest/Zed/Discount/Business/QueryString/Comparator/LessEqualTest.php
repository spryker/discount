<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\QueryString\Comparator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ClauseTransfer;
use Spryker\Zed\Discount\Business\Exception\ComparatorException;
use Spryker\Zed\Discount\Business\QueryString\Comparator\LessEqual;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group QueryString
 * @group Comparator
 * @group LessEqualTest
 * Add your own group annotations below this line
 */
class LessEqualTest extends Unit
{
    /**
     * @return void
     */
    public function testAcceptShouldReturnTrueWhenLessEqualExpressionProvided(): void
    {
        $lessEqual = $this->createLessEqual();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('<=');

        $isAccepted = $lessEqual->accept($clauseTransfer);

        $this->assertTrue($isAccepted);
    }

    /**
     * @return void
     */
    public function testCompareWhenValueLessClauseShouldReturnTrue(): void
    {
        $lessEqual = $this->createLessEqual();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setValue('2');

        $isMatching = $lessEqual->compare($clauseTransfer, '1');

        $this->assertTrue($isMatching);
    }

    /**
     * @return void
     */
    public function testCompareWhenValueEqualClauseShouldReturnTrue(): void
    {
        $lessEqual = $this->createLessEqual();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setValue('1');

        $isMatching = $lessEqual->compare($clauseTransfer, '1');

        $this->assertTrue($isMatching);
    }

    /**
     * @return void
     */
    public function testCompareWhenValueNotLessThanClauseShouldReturnFalse(): void
    {
        $lessEqual = $this->createLessEqual();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setValue('1');

        $isMatching = $lessEqual->compare($clauseTransfer, '2');

        $this->assertFalse($isMatching);
    }

    /**
     * @return void
     */
    public function testCompareWhenNonNumericValueUsedShouldThrowException(): void
    {
        $this->expectException(ComparatorException::class);

        $lessEqual = $this->createLessEqual();

        $clauseTransfer = new ClauseTransfer();

        $lessEqual->compare($clauseTransfer, 'as');
    }

    /**
     * @return void
     */
    public function testCompareShouldReturnFalseWhenEmptyValueIsProvided(): void
    {
        // Arrange
        $clauseTransfer = (new ClauseTransfer())->setValue('1');

        // Act
        $isMatching = $this->createLessEqual()->compare($clauseTransfer, '');

        // Assert
        $this->assertFalse($isMatching);
    }

    /**
     * @return void
     */
    public function testIsValueValidShouldReturnFalseWhenEmptyValueIsProvided(): void
    {
        // Act
        $isValueValid = $this->createLessEqual()->isValidValue('');

        // Assert
        $this->assertFalse($isValueValid);
    }

    /**
     * @return \Spryker\Zed\Discount\Business\QueryString\Comparator\LessEqual
     */
    protected function createLessEqual(): LessEqual
    {
        return new LessEqual();
    }
}
