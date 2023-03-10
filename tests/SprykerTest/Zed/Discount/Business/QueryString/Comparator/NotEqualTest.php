<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\QueryString\Comparator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ClauseTransfer;
use Spryker\Zed\Discount\Business\Exception\ComparatorException;
use Spryker\Zed\Discount\Business\QueryString\Comparator\NotEqual;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group QueryString
 * @group Comparator
 * @group NotEqualTest
 * Add your own group annotations below this line
 */
class NotEqualTest extends Unit
{
    /**
     * @return void
     */
    public function testAcceptShouldReturnTrueWhenNotEaualExpressionProvided(): void
    {
        $notEqual = $this->createNotEqual();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('!=');

        $isAccepted = $notEqual->accept($clauseTransfer);

        $this->assertTrue($isAccepted);
    }

    /**
     * @return void
     */
    public function testCompareWhenClauseValueIsNotEqualToProvidedShouldReturnTrue(): void
    {
        $notEqual = $this->createNotEqual();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setValue('2');

        $isMatching = $notEqual->compare($clauseTransfer, '1');

        $this->assertTrue($isMatching);
    }

    /**
     * @return void
     */
    public function testCompareWhenClauseValueIsEqualToProvidedProvidedShouldReturnFalse(): void
    {
        $more = $this->createNotEqual();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setValue('1');

        $isMatching = $more->compare($clauseTransfer, '1');

        $this->assertFalse($isMatching);
    }

    /**
     * @return void
     */
    public function testCompareWhenNonNumericValueUsedShouldThrowException(): void
    {
        $this->expectException(ComparatorException::class);

        $notEqual = $this->createNotEqual();

        $clauseTransfer = new ClauseTransfer();

        $notEqual->compare($clauseTransfer, []);
    }

    /**
     * @return void
     */
    public function testCompareShouldReturnFalseWhenEmptyValueIsProvided(): void
    {
        // Arrange
        $clauseTransfer = (new ClauseTransfer())->setValue('value');

        // Act
        $isMatching = $this->createNotEqual()->compare($clauseTransfer, '');

        // Assert
        $this->assertFalse($isMatching);
    }

    /**
     * @return void
     */
    public function testIsValueValidShouldReturnFalseWhenEmptyValueIsProvided(): void
    {
        // Act
        $isValueValid = $this->createNotEqual()->isValidValue('');

        // Assert
        $this->assertFalse($isValueValid);
    }

    /**
     * @return \Spryker\Zed\Discount\Business\QueryString\Comparator\NotEqual
     */
    protected function createNotEqual(): NotEqual
    {
        return new NotEqual();
    }
}
