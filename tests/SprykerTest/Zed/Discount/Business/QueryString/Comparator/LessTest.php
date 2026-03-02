<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\QueryString\Comparator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ClauseTransfer;
use Spryker\Zed\Discount\Business\Exception\ComparatorException;
use Spryker\Zed\Discount\Business\QueryString\Comparator\Less;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group QueryString
 * @group Comparator
 * @group LessTest
 * Add your own group annotations below this line
 */
class LessTest extends Unit
{
    public function testAcceptShouldReturnTrueWhenLessExpressionProvided(): void
    {
        $less = $this->createLess();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('<');

        $isAccepted = $less->accept($clauseTransfer);

        $this->assertTrue($isAccepted);
    }

    public function testCompareWhenValueLessThanClauseShouldReturnTrue(): void
    {
        $less = $this->createLess();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setValue('2');

        $isMatching = $less->compare($clauseTransfer, '1');

        $this->assertTrue($isMatching);
    }

    public function testCompareWhenValueNotLessThanClauseShouldReturnFalse(): void
    {
        $less = $this->createLess();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setValue('1');

        $isMatching = $less->compare($clauseTransfer, '2');

        $this->assertFalse($isMatching);
    }

    public function testCompareWhenNonNumericValueUsedShouldThrowException(): void
    {
        $this->expectException(ComparatorException::class);

        $less = $this->createLess();

        $clauseTransfer = new ClauseTransfer();

        $less->compare($clauseTransfer, 'as');
    }

    public function testCompareShouldReturnFalseWhenEmptyValueIsProvided(): void
    {
        // Arrange
        $clauseTransfer = (new ClauseTransfer())->setValue('1');

        // Act
        $isMatching = $this->createLess()->compare($clauseTransfer, '');

        // Assert
        $this->assertFalse($isMatching);
    }

    public function testIsValueValidShouldReturnFalseWhenEmptyValueIsProvided(): void
    {
        // Act
        $isValueValid = $this->createLess()->isValidValue('');

        // Assert
        $this->assertFalse($isValueValid);
    }

    protected function createLess(): Less
    {
        return new Less();
    }
}
