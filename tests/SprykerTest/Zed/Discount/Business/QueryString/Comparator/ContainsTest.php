<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\QueryString\Comparator;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ClauseTransfer;
use Spryker\Zed\Discount\Business\Exception\ComparatorException;
use Spryker\Zed\Discount\Business\QueryString\Comparator\Contains;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group QueryString
 * @group Comparator
 * @group ContainsTest
 * Add your own group annotations below this line
 */
class ContainsTest extends Unit
{
    /**
     * @return void
     */
    public function testAcceptShouldReturnTrueWhenContainsExpressionProvided(): void
    {
        $contains = $this->createContains();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('contains');

        $isAccepted = $contains->accept($clauseTransfer);

        $this->assertTrue($isAccepted);
    }

    /**
     * @return void
     */
    public function testCompareWhenValueExistsInClauseShouldReturnTrue(): void
    {
        $contains = $this->createContains();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setValue('TwO');

        $isMatching = $contains->compare($clauseTransfer, 'oNe TwO');

        $this->assertTrue($isMatching);
    }

    /**
     * @return void
     */
    public function testCompareWhenValueNotExistingInClauseShouldReturnFalse(): void
    {
        $contains = $this->createContains();

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setValue('zero');

        $isMatching = $contains->compare($clauseTransfer, ' oNe TwO ');

        $this->assertFalse($isMatching);
    }

    /**
     * @return void
     */
    public function testCompareWhenNonScalarValueUsedShouldThrowException(): void
    {
        $this->expectException(ComparatorException::class);

        $contains = $this->createContains();

        $clauseTransfer = new ClauseTransfer();

        $contains->compare($clauseTransfer, []);
    }

    /**
     * @return void
     */
    public function testCompareShouldReturnFalseWhenEmptyValueIsProvided(): void
    {
        // Arrange
        $clauseTransfer = (new ClauseTransfer())->setValue('value');

        // Act
        $isMatching = $this->createContains()->compare($clauseTransfer, '');

        // Assert
        $this->assertFalse($isMatching);
    }

    /**
     * @return void
     */
    public function testIsValueValidShouldReturnFalseWhenEmptyValueIsProvided(): void
    {
        // Act
        $isValueValid = $this->createContains()->isValidValue('');

        // Assert
        $this->assertFalse($isValueValid);
    }

    /**
     * @return \Spryker\Zed\Discount\Business\QueryString\Comparator\Contains
     */
    protected function createContains(): Contains
    {
        return new Contains();
    }
}
