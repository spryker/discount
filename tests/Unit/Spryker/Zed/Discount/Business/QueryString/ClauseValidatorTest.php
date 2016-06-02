<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Unit\Spryker\Zed\Discount\Business\QueryString;

use Generated\Shared\Transfer\ClauseTransfer;
use Spryker\Zed\Discount\Business\Exception\QueryStringException;
use Spryker\Zed\Discount\Business\QueryString\ClauseValidator;
use Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaDataProviderInterface;

class ClauseValidatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return void
     */
    public function testValidateWhenInvalidComparatorUsedShouldThrowException()
    {
        $this->expectException(QueryStringException::class);

        $comparatorOperatorsMock = $this->createComparatorOperatorsMock();
        $comparatorOperatorsMock->method('isValidComparator')
            ->willReturn(false);

        $clauseValidator = $this->createClauseValidator($comparatorOperatorsMock);
        $clauseValidator->validateClause($this->createClauseTransfer());

    }

    /**
     * @return void
     */
    public function testValidateWhenFieldNameContainsInvalidCharactersShouldThrowExpeption()
    {
        $this->expectException(QueryStringException::class);

        $comparatorOperatorsMock = $this->createComparatorOperatorsMock();
        $comparatorOperatorsMock->method('isValidComparator')
            ->willReturn(true);

        $clauseValidator = $this->createClauseValidator($comparatorOperatorsMock);
        $clauseTransfer = $this->createClauseTransfer();
        $clauseTransfer->setField('as$as');
        $clauseValidator->validateClause($clauseTransfer);

    }

    /**
     * @return void
     */
    public function testValidateWhenFieldIsNotWithingRegisteredRulePlugins()
    {
        $this->expectException(QueryStringException::class);

        $comparatorOperatorsMock = $this->createComparatorOperatorsMock();
        $comparatorOperatorsMock->method('isValidComparator')
            ->willReturn(true);

        $metaDataProviderMock = $this->createMetaDataProviderMock();
        $metaDataProviderMock->method('getAvailableFields')->willReturn(['undefined']);

        $clauseValidator = $this->createClauseValidator($comparatorOperatorsMock, $metaDataProviderMock);
        $clauseTransfer = $this->createClauseTransfer();
        $clauseTransfer->setField('field');
        $clauseValidator->validateClause($clauseTransfer);

    }

    /**
     * @return void
     */
    public function testValidateWhenFieldIsValidShouldNotThrowExceptions()
    {
        $comparatorOperatorsMock = $this->createComparatorOperatorsMock();
        $comparatorOperatorsMock->method('isValidComparator')
            ->willReturn(true);

        $metaDataProviderMock = $this->createMetaDataProviderMock();
        $metaDataProviderMock->method('getAvailableFields')->willReturn(['field']);

        $clauseValidator = $this->createClauseValidator($comparatorOperatorsMock, $metaDataProviderMock);
        $clauseTransfer = $this->createClauseTransfer();
        $clauseTransfer->setField('field');
        $clauseValidator->validateClause($clauseTransfer);

    }

    /**
     * @param \Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface $comparatorOperatorsMock
     * @param \Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaDataProviderInterface $metaDataProviderMock
     *
     * @return \Spryker\Zed\Discount\Business\QueryString\ClauseValidator
     */
    protected function createClauseValidator(
        ComparatorOperatorsInterface $comparatorOperatorsMock = null,
        MetaDataProviderInterface $metaDataProviderMock = null
    ) {

        if (!$comparatorOperatorsMock) {
            $comparatorOperatorsMock = $this->createComparatorOperatorsMock();
        }

        if (!$metaDataProviderMock) {
            $metaDataProviderMock = $this->createMetaDataProviderMock();
        }

        return new ClauseValidator($comparatorOperatorsMock, $metaDataProviderMock);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface
     */
    protected function createComparatorOperatorsMock()
    {
        return $this->getMock(ComparatorOperatorsInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaDataProviderInterface
     */
    protected function createMetaDataProviderMock()
    {
        return $this->getMock(MetaDataProviderInterface::class);
    }

    /**
     * @return \Generated\Shared\Transfer\ClauseTransfer
     */
    protected function createClauseTransfer()
    {
        return new ClauseTransfer();
    }

}
