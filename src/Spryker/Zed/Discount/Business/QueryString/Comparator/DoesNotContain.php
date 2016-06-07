<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\QueryString\Comparator;

use Generated\Shared\Transfer\ClauseTransfer;
use Spryker\Zed\Discount\Business\Exception\ComparatorException;
use Spryker\Zed\Discount\Business\QueryString\ComparatorOperators;

class DoesNotContain implements ComparatorInterface
{

    /**
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     * @param string $withValue
     *
     * @return bool
     *
     * @throws \Spryker\Zed\Discount\Business\Exception\ComparatorException
     */
    public function compare(ClauseTransfer $clauseTransfer, $withValue)
    {
        if (!is_scalar($withValue)) {
            throw new ComparatorException('Only scalar value can be used together with "does not contain" comparator.');
        }

        return (stripos($clauseTransfer->getValue(), trim($withValue))  === false);
    }

    /**
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return bool
     */
    public function accept(ClauseTransfer $clauseTransfer)
    {
        return (strcasecmp($clauseTransfer->getOperator(), $this->getExpression()) === 0);
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return 'does not contain';
    }

    /**
     * @return string[]
     */
    public function getAcceptedTypes()
    {
        return [
            ComparatorOperators::TYPE_STRING,
            ComparatorOperators::TYPE_INTEGER
        ];
    }

}