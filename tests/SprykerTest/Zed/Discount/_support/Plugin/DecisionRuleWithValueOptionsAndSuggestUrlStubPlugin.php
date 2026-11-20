<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Plugin;

use Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountRuleWithValueSuggestUrlPluginInterface;

class DecisionRuleWithValueOptionsAndSuggestUrlStubPlugin extends DecisionRuleWithValueOptionsStubPlugin implements DiscountRuleWithValueSuggestUrlPluginInterface
{
    /**
     * @param array<string, string> $valueOptions
     */
    public function __construct(string $fieldName, array $valueOptions, protected string $valueSuggestUrl)
    {
        parent::__construct($fieldName, $valueOptions);
    }

    public function getQueryStringValueSuggestUrl(): string
    {
        return $this->valueSuggestUrl;
    }
}
