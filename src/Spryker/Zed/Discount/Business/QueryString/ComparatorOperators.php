<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\QueryString;

use Generated\Shared\Transfer\ClauseTransfer;
use Spryker\Zed\Discount\Business\Exception\ComparatorException;
use Spryker\Zed\Discount\Business\QueryString\Comparator\ComparatorInterface;

class ComparatorOperators implements ComparatorOperatorsInterface
{
    /**
     * @var string
     */
    public const MATCH_ALL_IDENTIFIER = '*';

    /**
     * @var string
     */
    public const TYPE_NUMBER = 'number';

    /**
     * @var string
     */
    public const TYPE_STRING = 'string';

    /**
     * @var string
     */
    public const TYPE_LIST = 'list';

    /**
     * @var string
     */
    public const NUMBER_REGEXP = '/[0-9\.\,]+/';

    /**
     * @phpstan-var non-empty-string
     *
     * @var string
     */
    public const LIST_DELIMITER = ';';

    /**
     * @var array<\Spryker\Zed\Discount\Business\QueryString\Comparator\ComparatorInterface>
     */
    protected $operators = [];

    /**
     * @param array<\Spryker\Zed\Discount\Business\QueryString\Comparator\ComparatorInterface> $operators
     */
    public function __construct(array $operators)
    {
        $this->operators = $operators;
    }

    /**
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     * @param mixed $withValue
     *
     * @throws \Spryker\Zed\Discount\Business\Exception\ComparatorException
     *
     * @return bool
     */
    public function compare(ClauseTransfer $clauseTransfer, $withValue)
    {
        if ($this->isMatchAllValue($clauseTransfer->getValue())) {
            return (string)$withValue !== '';
        }

        foreach ($this->operators as $operator) {
            if (!$operator->accept($clauseTransfer)) {
                continue;
            }

            $this->assertTypeAccepted($clauseTransfer->getAcceptedTypes(), $operator);

            return $operator->compare($clauseTransfer, $withValue);
        }

        throw new ComparatorException(
            sprintf(
                'Comparison operator "%s" not found.',
                $clauseTransfer->getOperator(),
            ),
        );
    }

    /**
     * @param array<string> $acceptedTypes
     *
     * @return array<string>
     */
    public function getOperatorExpressionsByTypes(array $acceptedTypes)
    {
        $operatorExpressions = [];
        foreach ($this->operators as $operator) {
            if ($this->isTypeSet($acceptedTypes, $operator) === false) {
                continue;
            }
            $operatorExpressions[] = $operator->getExpression();
        }

        return $operatorExpressions;
    }

    /**
     * @return array<string>
     */
    public function getAvailableComparatorExpressions()
    {
        $comparatorExpressions = [];
        foreach ($this->operators as $operator) {
            $comparatorExpressions[] = $operator->getExpression();
        }

        return $comparatorExpressions;
    }

    /**
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return bool
     */
    public function isExistingComparator(ClauseTransfer $clauseTransfer)
    {
        foreach ($this->operators as $operator) {
            if ($operator->accept($clauseTransfer) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string>
     */
    public function getCompoundComparatorExpressions()
    {
        $combinedOperators = [];
        foreach ($this->operators as $comparator) {
            $expression = $comparator->getExpression();
            $parts = explode(' ', trim($expression));
            if (count($parts) <= 1) {
                continue;
            }
            $combinedOperators = array_merge($combinedOperators, $parts);
        }

        return array_unique($combinedOperators);
    }

    /**
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return bool
     */
    public function isValidComparatorValue(ClauseTransfer $clauseTransfer)
    {
        foreach ($this->operators as $operator) {
            if (!$operator->accept($clauseTransfer)) {
                continue;
            }

            $operator->isValidValue($clauseTransfer->getValue());
        }

        return true;
    }

    /**
     * @param array<string> $withTypes
     * @param \Spryker\Zed\Discount\Business\QueryString\Comparator\ComparatorInterface $operator
     *
     * @throws \Spryker\Zed\Discount\Business\Exception\ComparatorException
     *
     * @return bool
     */
    protected function assertTypeAccepted(array $withTypes, ComparatorInterface $operator)
    {
        if ($this->isTypeSet($withTypes, $operator) === true) {
            return true;
        }

        throw new ComparatorException(
            sprintf(
                '"%s" operator does not accept any of "%s" types',
                get_class($operator),
                implode(',', $withTypes),
            ),
        );
    }

    /**
     * @param array $withTypes
     * @param \Spryker\Zed\Discount\Business\QueryString\Comparator\ComparatorInterface $comparator
     *
     * @return bool
     */
    protected function isTypeSet(array $withTypes, ComparatorInterface $comparator)
    {
        foreach ($withTypes as $withType) {
            if (in_array($withType, $comparator->getAcceptedTypes())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $withValue
     *
     * @return bool
     */
    protected function isMatchAllValue($withValue)
    {
        return ($withValue === static::MATCH_ALL_IDENTIFIER);
    }
}
