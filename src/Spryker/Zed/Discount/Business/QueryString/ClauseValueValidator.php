<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\QueryString;

use Generated\Shared\Transfer\ClauseTransfer;
use Spryker\Zed\Discount\Business\Exception\QueryStringException;
use Spryker\Zed\Discount\Business\QueryString\Comparator\ComparatorInterface;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaDataProviderInterface;

class ClauseValueValidator implements ClauseValidatorInterface
{
    /**
     * @param list<\Spryker\Zed\Discount\Business\QueryString\Comparator\ComparatorInterface> $exactValueComparators
     */
    public function __construct(
        protected ClauseValidatorInterface $clauseValidator,
        protected MetaDataProviderInterface $metaDataProvider,
        protected array $exactValueComparators
    ) {
    }

    /**
     * @throws \Spryker\Zed\Discount\Business\Exception\QueryStringException
     */
    public function validateClause(ClauseTransfer $clauseTransfer): void
    {
        $this->clauseValidator->validateClause($clauseTransfer);

        $exactValueComparator = $this->findExactValueComparator($clauseTransfer);

        if ($exactValueComparator === null || $clauseTransfer->getValue() === ComparatorOperators::MATCH_ALL_IDENTIFIER) {
            return;
        }

        $invalidValues = $this->metaDataProvider->getInvalidQueryStringValues(
            (string)$clauseTransfer->getField(),
            $this->getClauseValues($clauseTransfer, $exactValueComparator),
        );

        if ($invalidValues === []) {
            return;
        }

        throw new QueryStringException(sprintf(
            'Could not find "%s" for field "%s".',
            implode('", "', $invalidValues),
            $clauseTransfer->getField(),
        ));
    }

    protected function findExactValueComparator(ClauseTransfer $clauseTransfer): ?ComparatorInterface
    {
        foreach ($this->exactValueComparators as $exactValueComparator) {
            if ($exactValueComparator->accept($clauseTransfer)) {
                return $exactValueComparator;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    protected function getClauseValues(ClauseTransfer $clauseTransfer, ComparatorInterface $exactValueComparator): array
    {
        $value = (string)$clauseTransfer->getValue();

        if (!in_array(ComparatorOperators::TYPE_LIST, $exactValueComparator->getAcceptedTypes(), true)) {
            return [$value];
        }

        $values = array_map('trim', explode(ComparatorOperators::LIST_DELIMITER, $value));

        return array_values(array_filter($values, fn (string $listValue): bool => $listValue !== ''));
    }
}
