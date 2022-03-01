<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\QueryString\Specification\MetaData;

use Spryker\Zed\Discount\Business\Exception\QueryStringException;
use Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface;
use Spryker\Zed\Discount\Business\QueryString\LogicalComparators;
use Spryker\Zed\Discount\Dependency\Plugin\DiscountRuleWithAttributesPluginInterface;
use Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountRuleWithValueOptionsPluginInterface;

class MetaDataProvider implements MetaDataProviderInterface
{
    /**
     * @var array<\Spryker\Zed\DiscountExtension\Dependency\Plugin\DecisionRulePluginInterface|\Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountableItemCollectorPluginInterface>
     */
    protected $specificationPlugins = [];

    /**
     * @var \Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface
     */
    protected $comparatorOperators;

    /**
     * @var \Spryker\Zed\Discount\Business\QueryString\LogicalComparators
     */
    protected $logicalComparators;

    /**
     * @var array<string>|null Numerical array of available fields.
     */
    protected $availableFieldsBuffer;

    /**
     * @see MetaDataProvider::availableFieldsBuffer
     *
     * @var array<string, mixed>|null Each key is an available field. Contains the flipped $availableFieldsBuffer variable for performance reason.
     */
    protected $availableFieldsMapBuffer;

    /**
     * @param array<\Spryker\Zed\DiscountExtension\Dependency\Plugin\DecisionRulePluginInterface|\Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountableItemCollectorPluginInterface> $specificationPlugins
     * @param \Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface $comparatorOperators
     * @param \Spryker\Zed\Discount\Business\QueryString\LogicalComparators $logicalComparators
     */
    public function __construct(
        array $specificationPlugins,
        ComparatorOperatorsInterface $comparatorOperators,
        LogicalComparators $logicalComparators
    ) {
        $this->specificationPlugins = $specificationPlugins;
        $this->comparatorOperators = $comparatorOperators;
        $this->logicalComparators = $logicalComparators;
    }

    /**
     * @return array<string>
     */
    public function getAvailableFields()
    {
        if ($this->availableFieldsBuffer === null) {
            $this->loadAvailableFieldsBuffers();
        }

        return $this->availableFieldsBuffer;
    }

    /**
     * @param string $field
     *
     * @return bool
     */
    public function isFieldAvailable($field)
    {
        if ($this->availableFieldsMapBuffer === null) {
            $this->loadAvailableFieldsBuffers();
        }

        return isset($this->availableFieldsMapBuffer[$field]);
    }

    /**
     * @return void
     */
    protected function loadAvailableFieldsBuffers()
    {
        $queryStringFields = [];
        foreach ($this->specificationPlugins as $specificationPlugin) {
            if ($specificationPlugin instanceof DiscountRuleWithAttributesPluginInterface) {
                $queryStringFields = array_merge(
                    $queryStringFields,
                    $this->getAttributeTypes($specificationPlugin),
                );
            } else {
                $queryStringFields[] = $specificationPlugin->getFieldName();
            }
        }

        /** @var array<string, mixed> $mapBuffer */
        $mapBuffer = array_flip($queryStringFields);

        $this->availableFieldsBuffer = $queryStringFields;
        $this->availableFieldsMapBuffer = $mapBuffer;
    }

    /**
     * @param string $fieldName
     *
     * @throws \Spryker\Zed\Discount\Business\Exception\QueryStringException
     *
     * @return array<string>
     */
    public function getAcceptedTypesByFieldName($fieldName)
    {
        if (strpos($fieldName, '.') !== false) {
            [$fieldName, $attribute] = explode('.', $fieldName);
        }

        foreach ($this->specificationPlugins as $specificationPlugin) {
            if ($fieldName === $specificationPlugin->getFieldName()) {
                return $specificationPlugin->acceptedDataTypes();
            }
        }

        throw new QueryStringException(sprintf(
            'No specification plugin found for "%s" field. Have you added it to DiscountDependencyProvider::getCollectorPlugins or getDecisionRulePlugins respectively?',
            $fieldName,
        ));
    }

    /**
     * @param string $fieldName
     *
     * @return array<string>
     */
    public function getAvailableOperatorExpressionsForField($fieldName)
    {
        $acceptedTypes = $this->getAcceptedTypesByFieldName($fieldName);

        return $this->comparatorOperators->getOperatorExpressionsByTypes($acceptedTypes);
    }

    /**
     * @return array<string>
     */
    public function getAvailableComparatorExpressions()
    {
        return $this->comparatorOperators->getAvailableComparatorExpressions();
    }

    /**
     * @return array<string>
     */
    public function getLogicalComparators()
    {
        return $this->logicalComparators->getLogicalOperators();
    }

    /**
     * @return array<string>
     */
    public function getCompoundExpressions()
    {
        return $this->comparatorOperators->getCompoundComparatorExpressions();
    }

    /**
     * @param \Spryker\Zed\Discount\Dependency\Plugin\DiscountRuleWithAttributesPluginInterface $specificationPlugin
     *
     * @return array
     */
    protected function getAttributeTypes($specificationPlugin)
    {
        $attributeFields = [];
        foreach ($specificationPlugin->getAttributeTypes() as $attributeType) {
            /** @phpstan-var \Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountableItemCollectorPluginInterface|\Spryker\Zed\DiscountExtension\Dependency\Plugin\DecisionRulePluginInterface $specificationPlugin */
            $attributeFields[] = $specificationPlugin->getFieldName() . '.' . $attributeType;
        }

        return $attributeFields;
    }

    /**
     * @return array
     */
    public function getQueryStringValueOptions()
    {
        $valueOptions = [];
        foreach ($this->specificationPlugins as $specificationPlugin) {
            if ($specificationPlugin instanceof DiscountRuleWithValueOptionsPluginInterface) {
                $valueOptions[$specificationPlugin->getFieldName()] = $specificationPlugin->getQueryStringValueOptions();
            }
        }

        return $valueOptions;
    }
}
