<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Mapper;

use Generated\Api\Backend\DiscountOptions\DiscountOptionsCalculatorTypesBackendObject;
use Generated\Api\Backend\DiscountOptions\DiscountOptionsCollectorFieldsBackendObject;
use Generated\Api\Backend\DiscountOptions\DiscountOptionsCollectorFieldsValueOptionsBackendObject;
use Generated\Api\Backend\DiscountOptions\DiscountOptionsCollectorStrategyTypesBackendObject;
use Generated\Api\Backend\DiscountOptions\DiscountOptionsDecisionRuleFieldsBackendObject;
use Generated\Api\Backend\DiscountOptions\DiscountOptionsDecisionRuleFieldsValueOptionsBackendObject;
use Generated\Api\Backend\DiscountOptions\DiscountOptionsPriorityBackendObject;
use Generated\Api\Backend\DiscountOptions\DiscountOptionsStoresBackendObject;
use Generated\Api\Backend\DiscountOptions\DiscountOptionsVoucherCodeLengthBackendObject;
use Generated\Api\Backend\DiscountOptions\DiscountOptionsVoucherCodesQuantityBackendObject;
use Generated\Api\Backend\DiscountOptionsBackendResource;
use Generated\Shared\Transfer\DiscountConfigurationOptionsTransfer;
use Generated\Shared\Transfer\DiscountIntegerRangeTransfer;
use Generated\Shared\Transfer\DiscountRuleFieldOptionTransfer;
use Spryker\Glue\Discount\DiscountConfig;

class DiscountOptionsResourceMapper implements DiscountOptionsResourceMapperInterface
{
    public function __construct(protected DiscountConfig $discountConfig)
    {
    }

    protected const string INTEGER_RANGE_KEY_MIN = 'min';

    protected const string INTEGER_RANGE_KEY_MAX = 'max';

    public function mapDiscountConfigurationOptionsTransferToDiscountOptionsBackendResource(
        DiscountConfigurationOptionsTransfer $discountConfigurationOptionsTransfer,
        DiscountOptionsBackendResource $discountOptionsBackendResource
    ): DiscountOptionsBackendResource {
        $priorityRangeTransfer = $discountConfigurationOptionsTransfer->getPriorityRange();
        $voucherCodesQuantityRangeTransfer = $discountConfigurationOptionsTransfer->getVoucherCodesQuantityRange();
        $voucherCodeLengthRangeTransfer = $discountConfigurationOptionsTransfer->getVoucherCodeLengthRange();

        $discountOptionsBackendResource->discountTypes = $discountConfigurationOptionsTransfer->getDiscountTypes();
        $discountOptionsBackendResource->calculatorTypes = $this->mapCalculatorTypeOptions($discountConfigurationOptionsTransfer);
        $discountOptionsBackendResource->collectorStrategyTypes = $this->mapCollectorStrategyOptions($discountConfigurationOptionsTransfer);
        $discountOptionsBackendResource->priority = $priorityRangeTransfer === null
            ? null
            : DiscountOptionsPriorityBackendObject::fromArray($this->mapIntegerRange($priorityRangeTransfer));
        $discountOptionsBackendResource->voucherCodesQuantity = $voucherCodesQuantityRangeTransfer === null
            ? null
            : DiscountOptionsVoucherCodesQuantityBackendObject::fromArray($this->mapIntegerRange($voucherCodesQuantityRangeTransfer));
        $discountOptionsBackendResource->voucherCodeLength = $voucherCodeLengthRangeTransfer === null
            ? null
            : DiscountOptionsVoucherCodeLengthBackendObject::fromArray($this->mapIntegerRange($voucherCodeLengthRangeTransfer));
        $discountOptionsBackendResource->stores = $this->mapStores($discountConfigurationOptionsTransfer);
        $discountOptionsBackendResource->decisionRuleFields = $this->mapDecisionRuleFieldOptions($discountConfigurationOptionsTransfer);
        $discountOptionsBackendResource->collectorFields = $this->mapCollectorFieldOptions($discountConfigurationOptionsTransfer);
        $discountOptionsBackendResource->logicalOperators = $discountConfigurationOptionsTransfer->getLogicalOperators();

        return $discountOptionsBackendResource;
    }

    /**
     * @return array<\Generated\Api\Backend\DiscountOptions\DiscountOptionsCalculatorTypesBackendObject>
     */
    protected function mapCalculatorTypeOptions(
        DiscountConfigurationOptionsTransfer $discountConfigurationOptionsTransfer
    ): array {
        $calculatorPluginOptions = [];

        foreach ($discountConfigurationOptionsTransfer->getCalculatorPlugins() as $discountCalculatorPluginOptionTransfer) {
            $calculatorPluginOption = new DiscountOptionsCalculatorTypesBackendObject();
            $calculatorPluginOption->key = $this->mapCalculatorPluginToCalculatorType((string)$discountCalculatorPluginOptionTransfer->getKey());
            $calculatorPluginOption->label = $discountCalculatorPluginOptionTransfer->getLabel();
            $calculatorPluginOption->inputType = $discountCalculatorPluginOptionTransfer->getInputType();

            $calculatorPluginOptions[] = $calculatorPluginOption;
        }

        return $calculatorPluginOptions;
    }

    protected function mapCalculatorPluginToCalculatorType(string $calculatorPlugin): string
    {
        $calculatorType = array_search($calculatorPlugin, $this->discountConfig->getCalculatorPluginKeyByCalculatorType(), true);

        return $calculatorType === false ? $calculatorPlugin : $calculatorType;
    }

    /**
     * @return array<\Generated\Api\Backend\DiscountOptions\DiscountOptionsCollectorStrategyTypesBackendObject>
     */
    protected function mapCollectorStrategyOptions(
        DiscountConfigurationOptionsTransfer $discountConfigurationOptionsTransfer
    ): array {
        $collectorStrategyOptions = [];

        foreach ($discountConfigurationOptionsTransfer->getCollectorStrategyTypes() as $discountCollectorStrategyOptionTransfer) {
            $collectorStrategyOption = new DiscountOptionsCollectorStrategyTypesBackendObject();
            $collectorStrategyOption->key = $discountCollectorStrategyOptionTransfer->getKey();
            $collectorStrategyOption->label = $discountCollectorStrategyOptionTransfer->getLabel();

            $collectorStrategyOptions[] = $collectorStrategyOption;
        }

        return $collectorStrategyOptions;
    }

    /**
     * @return array<string, int|null>
     */
    protected function mapIntegerRange(
        DiscountIntegerRangeTransfer $discountIntegerRangeTransfer
    ): array {
        return [
            static::INTEGER_RANGE_KEY_MIN => $discountIntegerRangeTransfer->getMin(),
            static::INTEGER_RANGE_KEY_MAX => $discountIntegerRangeTransfer->getMax(),
        ];
    }

    /**
     * @return array<\Generated\Api\Backend\DiscountOptions\DiscountOptionsStoresBackendObject>
     */
    protected function mapStores(
        DiscountConfigurationOptionsTransfer $discountConfigurationOptionsTransfer
    ): array {
        $stores = [];

        foreach ($discountConfigurationOptionsTransfer->getStores() as $storeTransfer) {
            $store = new DiscountOptionsStoresBackendObject();
            $store->name = $storeTransfer->getName();
            $store->currencyIsoCodes = $storeTransfer->getAvailableCurrencyIsoCodes();

            $stores[] = $store;
        }

        return $stores;
    }

    /**
     * @return array<\Generated\Api\Backend\DiscountOptions\DiscountOptionsDecisionRuleFieldsBackendObject>
     */
    protected function mapDecisionRuleFieldOptions(
        DiscountConfigurationOptionsTransfer $discountConfigurationOptionsTransfer
    ): array {
        $decisionRuleFields = [];

        foreach ($discountConfigurationOptionsTransfer->getDecisionRuleFields() as $discountRuleFieldOptionTransfer) {
            $decisionRuleField = new DiscountOptionsDecisionRuleFieldsBackendObject();
            $decisionRuleField->field = $discountRuleFieldOptionTransfer->getField();
            $decisionRuleField->operators = $discountRuleFieldOptionTransfer->getOperators();
            $decisionRuleField->acceptedTypes = $discountRuleFieldOptionTransfer->getAcceptedTypes();
            $decisionRuleField->valueOptions = $this->mapDecisionRuleFieldValueOptions($discountRuleFieldOptionTransfer);

            $decisionRuleFields[] = $decisionRuleField;
        }

        return $decisionRuleFields;
    }

    /**
     * @return array<\Generated\Api\Backend\DiscountOptions\DiscountOptionsDecisionRuleFieldsValueOptionsBackendObject>
     */
    protected function mapDecisionRuleFieldValueOptions(
        DiscountRuleFieldOptionTransfer $discountRuleFieldOptionTransfer
    ): array {
        $valueOptions = [];

        foreach ($discountRuleFieldOptionTransfer->getValueOptions() as $discountRuleFieldValueOptionTransfer) {
            $valueOption = new DiscountOptionsDecisionRuleFieldsValueOptionsBackendObject();
            $valueOption->value = $discountRuleFieldValueOptionTransfer->getValue();
            $valueOption->label = $discountRuleFieldValueOptionTransfer->getLabel();

            $valueOptions[] = $valueOption;
        }

        return $valueOptions;
    }

    /**
     * @return array<\Generated\Api\Backend\DiscountOptions\DiscountOptionsCollectorFieldsBackendObject>
     */
    protected function mapCollectorFieldOptions(
        DiscountConfigurationOptionsTransfer $discountConfigurationOptionsTransfer
    ): array {
        $collectorFields = [];

        foreach ($discountConfigurationOptionsTransfer->getCollectorFields() as $discountRuleFieldOptionTransfer) {
            $collectorField = new DiscountOptionsCollectorFieldsBackendObject();
            $collectorField->field = $discountRuleFieldOptionTransfer->getField();
            $collectorField->operators = $discountRuleFieldOptionTransfer->getOperators();
            $collectorField->acceptedTypes = $discountRuleFieldOptionTransfer->getAcceptedTypes();
            $collectorField->valueOptions = $this->mapCollectorFieldValueOptions($discountRuleFieldOptionTransfer);

            $collectorFields[] = $collectorField;
        }

        return $collectorFields;
    }

    /**
     * @return array<\Generated\Api\Backend\DiscountOptions\DiscountOptionsCollectorFieldsValueOptionsBackendObject>
     */
    protected function mapCollectorFieldValueOptions(
        DiscountRuleFieldOptionTransfer $discountRuleFieldOptionTransfer
    ): array {
        $valueOptions = [];

        foreach ($discountRuleFieldOptionTransfer->getValueOptions() as $discountRuleFieldValueOptionTransfer) {
            $valueOption = new DiscountOptionsCollectorFieldsValueOptionsBackendObject();
            $valueOption->value = $discountRuleFieldValueOptionTransfer->getValue();
            $valueOption->label = $discountRuleFieldValueOptionTransfer->getLabel();

            $valueOptions[] = $valueOption;
        }

        return $valueOptions;
    }
}
