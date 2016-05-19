<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\QueryString\Specification\DecisionRuleSpecification;

use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;

class DecisionRuleAndSpecification implements DecisionRuleSpecificationInterface
{
    /**
     * @var DecisionRuleSpecificationInterface
     */
    protected $left;

    /**
     * @var DecisionRuleSpecificationInterface
     */
    protected $right;

    /**
     * @param DecisionRuleSpecificationInterface $left
     * @param DecisionRuleSpecificationInterface $right
     */
    public function __construct(
        DecisionRuleSpecificationInterface $left,
        DecisionRuleSpecificationInterface $right
    ) {
        $this->left = $left;
        $this->right = $right;
    }

    /**
     *
     * @param QuoteTransfer $quoteTransfer
     * @param ItemTransfer $itemTransfer
     *
     * @return bool
     */
    public function isSatisfiedBy(QuoteTransfer $quoteTransfer, ItemTransfer $itemTransfer)
    {
        return $this->left->isSatisfiedBy($quoteTransfer, $itemTransfer) && $this->right->isSatisfiedBy($quoteTransfer, $itemTransfer);
    }
}
