<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Plugin;

use Generated\Shared\Transfer\DiscountRuleValueValidationResponseTransfer;
use Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountRuleWithValueValidationPluginInterface;

class DecisionRuleWithValueValidationStubPlugin extends DecisionRuleStubPlugin implements DiscountRuleWithValueValidationPluginInterface
{
    /**
     * @var list<list<string>>
     */
    public array $receivedValues = [];

    /**
     * @param list<string> $validValues
     */
    public function __construct(string $fieldName, protected array $validValues)
    {
        parent::__construct($fieldName);
    }

    /**
     * @param list<string> $values
     */
    public function validateQueryStringValues(array $values): DiscountRuleValueValidationResponseTransfer
    {
        $this->receivedValues[] = $values;

        $invalidValues = array_values(array_diff($values, $this->validValues));

        return (new DiscountRuleValueValidationResponseTransfer())
            ->setIsSuccessful($invalidValues === [])
            ->setInvalidValues($invalidValues);
    }
}
