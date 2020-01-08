<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\QueryString;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ClauseTransfer;
use Spryker\Zed\Discount\Business\Exception\QueryStringException;
use Spryker\Zed\Discount\Business\QueryString\ClauseValidator;
use Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaDataProviderInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group QueryString
 * @group ClauseValidatorTest
 * Add your own group annotations below this line
 */
class ClauseValidatorTest extends Unit
{
    /**
     * @return void
     */
    public function testValidateWhenInvalidComparatorUsedShouldThrowException(): void
    {
        $this->expectException(QueryStringException::class);

        $comparatorOperatorsMock = $this->createComparatorOperatorsMock();
        $comparatorOperatorsMock->method('isExistingComparator')
            ->willReturn(false);

        $clauseValidator = $this->createClauseValidator($comparatorOperatorsMock);
        $clauseValidator->validateClause($this->createClauseTransfer());
    }

    /**
     * @return void
     */
    public function testValidateWhenFieldNameContainsInvalidCharactersShouldThrowExpeption(): void
    {
        $this->expectException(QueryStringException::class);

        $comparatorOperatorsMock = $this->createComparatorOperatorsMock();
        $comparatorOperatorsMock->method('isExistingComparator')
            ->willReturn(true);

        $clauseValidator = $this->createClauseValidator($comparatorOperatorsMock);
        $clauseTransfer = $this->createClauseTransfer();
        $clauseTransfer->setField('as$as');
        $clauseValidator->validateClause($clauseTransfer);
    }

    /**
     * @uses MetaDataProviderInterface::isFieldAvailable()
     *
     * @return void
     */
    public function testValidateWhenFieldIsNotWithingRegisteredRulePlugins(): void
    {
        $this->expectException(QueryStringException::class);

        $comparatorOperatorsMock = $this->createComparatorOperatorsMock();
        $comparatorOperatorsMock->method('isExistingComparator')
            ->willReturn(true);

        $metaDataProviderMock = $this->createMetaDataProviderMock();
        $metaDataProviderMock
            ->expects($this->any())
            ->method('isFieldAvailable')
            ->will($this->returnValueMap([
                ['undefined', true],
                ['field', false],
            ]));

        $clauseValidator = $this->createClauseValidator($comparatorOperatorsMock, $metaDataProviderMock);
        $clauseTransfer = $this->createClauseTransfer();
        $clauseTransfer->setField('field');
        $clauseValidator->validateClause($clauseTransfer);
    }

    /**
     * @uses MetaDataProviderInterface::isFieldAvailable()
     *
     * @return void
     */
    public function testValidateWhenFieldIsValidShouldNotThrowExceptions(): void
    {
        $comparatorOperatorsMock = $this->createComparatorOperatorsMock();
        $comparatorOperatorsMock->method('isExistingComparator')
            ->willReturn(true);

        $metaDataProviderMock = $this->createMetaDataProviderMock();
        $metaDataProviderMock
            ->expects($this->any())
            ->method('isFieldAvailable')
            ->will($this->returnValueMap([['field', true]]));

        $clauseValidator = $this->createClauseValidator($comparatorOperatorsMock, $metaDataProviderMock);
        $clauseTransfer = $this->createClauseTransfer();
        $clauseTransfer->setField('field');
        $clauseValidator->validateClause($clauseTransfer);
    }

    /**
     * @param \Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface|null $comparatorOperatorsMock
     * @param \Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaDataProviderInterface|null $metaDataProviderMock
     *
     * @return \Spryker\Zed\Discount\Business\QueryString\ClauseValidator
     */
    protected function createClauseValidator(
        ?ComparatorOperatorsInterface $comparatorOperatorsMock = null,
        ?MetaDataProviderInterface $metaDataProviderMock = null
    ): ClauseValidator {
        if (!$comparatorOperatorsMock) {
            $comparatorOperatorsMock = $this->createComparatorOperatorsMock();
        }

        if (!$metaDataProviderMock) {
            $metaDataProviderMock = $this->createMetaDataProviderMock();
        }

        return new ClauseValidator($comparatorOperatorsMock, $metaDataProviderMock);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface
     */
    protected function createComparatorOperatorsMock(): ComparatorOperatorsInterface
    {
        return $this->getMockBuilder(ComparatorOperatorsInterface::class)->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaDataProviderInterface
     */
    protected function createMetaDataProviderMock(): MetaDataProviderInterface
    {
        return $this->getMockBuilder(MetaDataProviderInterface::class)->getMock();
    }

    /**
     * @return \Generated\Shared\Transfer\ClauseTransfer
     */
    protected function createClauseTransfer(): ClauseTransfer
    {
        return new ClauseTransfer();
    }
}
