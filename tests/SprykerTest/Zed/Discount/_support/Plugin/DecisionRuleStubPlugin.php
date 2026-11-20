<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Plugin;

use Generated\Shared\Transfer\ClauseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Discount\Business\QueryString\ComparatorOperators;
use Spryker\Zed\DiscountExtension\Dependency\Plugin\DecisionRulePluginInterface;

class DecisionRuleStubPlugin implements DecisionRulePluginInterface
{
    public function __construct(protected string $fieldName)
    {
    }

    public function isSatisfiedBy(QuoteTransfer $quoteTransfer, ItemTransfer $itemTransfer, ClauseTransfer $clauseTransfer): bool
    {
        return false;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @return array<string>
     */
    public function acceptedDataTypes(): array
    {
        return [ComparatorOperators::TYPE_STRING, ComparatorOperators::TYPE_LIST];
    }
}
