<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Plugin;

use Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountRuleWithValueOptionsPluginInterface;

class DecisionRuleWithValueOptionsStubPlugin extends DecisionRuleStubPlugin implements DiscountRuleWithValueOptionsPluginInterface
{
    /**
     * @param array<string, string> $valueOptions
     */
    public function __construct(string $fieldName, protected array $valueOptions)
    {
        parent::__construct($fieldName);
    }

    /**
     * @return array<string, string>
     */
    public function getQueryStringValueOptions(): array
    {
        return $this->valueOptions;
    }
}
