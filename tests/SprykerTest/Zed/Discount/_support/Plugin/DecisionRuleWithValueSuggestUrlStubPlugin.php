<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Plugin;

use Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountRuleWithValueSuggestUrlPluginInterface;

class DecisionRuleWithValueSuggestUrlStubPlugin extends DecisionRuleStubPlugin implements DiscountRuleWithValueSuggestUrlPluginInterface
{
    public function __construct(string $fieldName, protected string $valueSuggestUrl)
    {
        parent::__construct($fieldName);
    }

    public function getQueryStringValueSuggestUrl(): string
    {
        return $this->valueSuggestUrl;
    }
}
