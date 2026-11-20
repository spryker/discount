<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Plugin;

use Generated\Shared\Transfer\ClauseTransfer;
use Generated\Shared\Transfer\DiscountRuleValueValidationResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Discount\Business\QueryString\ComparatorOperators;
use Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountableItemCollectorPluginInterface;
use Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountRuleWithValueValidationPluginInterface;

class CollectorWithValueValidationStubPlugin implements DiscountableItemCollectorPluginInterface, DiscountRuleWithValueValidationPluginInterface
{
    /**
     * @var list<list<string>>
     */
    public array $receivedValues = [];

    /**
     * @param list<string> $validValues
     */
    public function __construct(protected string $fieldName, protected array $validValues)
    {
    }

    /**
     * @return array<\Generated\Shared\Transfer\DiscountableItemTransfer>
     */
    public function collect(QuoteTransfer $quoteTransfer, ClauseTransfer $clauseTransfer): array
    {
        return [];
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
