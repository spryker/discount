<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\Reader;

use ArrayObject;
use Generated\Shared\Transfer\DiscountCalculatorPluginOptionTransfer;
use Generated\Shared\Transfer\DiscountCollectorStrategyOptionTransfer;
use Generated\Shared\Transfer\DiscountConfigurationOptionsTransfer;
use Generated\Shared\Transfer\DiscountIntegerRangeTransfer;
use Generated\Shared\Transfer\DiscountRuleFieldOptionTransfer;
use Generated\Shared\Transfer\DiscountRuleFieldValueOptionTransfer;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaDataProviderInterface;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaProviderFactory;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaProviderFactoryInterface;
use Spryker\Zed\Discount\Dependency\Facade\DiscountToStoreFacadeInterface;
use Spryker\Zed\Discount\Dependency\Facade\DiscountToTranslatorFacadeInterface;
use Spryker\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginInterface;
use Spryker\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginWithAmountInputTypeInterface;
use Spryker\Zed\Discount\DiscountConfig;

class DiscountConfigurationOptionsReader implements DiscountConfigurationOptionsReaderInterface
{
    /**
     * @uses \Spryker\Zed\Discount\Communication\Form\DataProvider\CalculatorFormDataProvider::getOptions()
     */
    protected const string COLLECTOR_STRATEGY_QUERY_STRING_LABEL = 'Query String';

    protected const string CALCULATOR_PLUGIN_KEY_PREFIX = 'plugin_calculator_';

    /**
     * @param array<\Spryker\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginInterface> $calculatorPlugins
     * @param array<\Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountConfigurationOptionsExpanderPluginInterface> $discountConfigurationOptionsExpanderPlugins
     */
    public function __construct(
        protected DiscountConfig $discountConfig,
        protected MetaProviderFactoryInterface $metaProviderFactory,
        protected DiscountToStoreFacadeInterface $storeFacade,
        protected DiscountToTranslatorFacadeInterface $translatorFacade,
        protected array $calculatorPlugins,
        protected array $discountConfigurationOptionsExpanderPlugins
    ) {
    }

    public function getDiscountConfigurationOptions(): DiscountConfigurationOptionsTransfer
    {
        $discountConfigurationOptionsTransfer = (new DiscountConfigurationOptionsTransfer())
            ->setDiscountTypes([DiscountConstants::TYPE_CART_RULE, DiscountConstants::TYPE_VOUCHER])
            ->setPriorityRange($this->createIntegerRange(
                $this->discountConfig->getPriorityMinValue(),
                $this->discountConfig->getPriorityMaxValue(),
            ))
            ->setVoucherCodesQuantityRange($this->createIntegerRange(
                $this->discountConfig->getVoucherCodesQuantityMinValue(),
                $this->discountConfig->getVoucherCodesQuantityMaxValue(),
            ))
            ->setVoucherCodeLengthRange($this->createIntegerRange(
                $this->discountConfig->getVoucherCodeLengthMinValue(),
                $this->discountConfig->getVoucherCodeLengthMaxValue(),
            ))
            ->setLogicalOperators($this->createDecisionRuleMetaDataProvider()->getLogicalComparators())
            ->addCollectorStrategyType(
                (new DiscountCollectorStrategyOptionTransfer())
                    ->setKey(DiscountConstants::DISCOUNT_COLLECTOR_STRATEGY_QUERY_STRING)
                    ->setLabel(static::COLLECTOR_STRATEGY_QUERY_STRING_LABEL),
            );

        $discountConfigurationOptionsTransfer = $this->expandWithCalculatorPluginOptions($discountConfigurationOptionsTransfer);
        $discountConfigurationOptionsTransfer = $this->expandWithStores($discountConfigurationOptionsTransfer);
        $discountConfigurationOptionsTransfer = $this->expandWithRuleFieldOptions($discountConfigurationOptionsTransfer);

        return $this->executeDiscountConfigurationOptionsExpanderPlugins($discountConfigurationOptionsTransfer);
    }

    protected function expandWithCalculatorPluginOptions(
        DiscountConfigurationOptionsTransfer $discountConfigurationOptionsTransfer
    ): DiscountConfigurationOptionsTransfer {
        foreach ($this->calculatorPlugins as $calculatorPluginKey => $calculatorPlugin) {
            $discountConfigurationOptionsTransfer->addCalculatorPlugin(
                (new DiscountCalculatorPluginOptionTransfer())
                    ->setKey((string)$calculatorPluginKey)
                    ->setInputType($this->resolveCalculatorPluginInputType($calculatorPlugin))
                    ->setLabel($this->resolveCalculatorPluginLabel((string)$calculatorPluginKey)),
            );
        }

        return $discountConfigurationOptionsTransfer;
    }

    protected function resolveCalculatorPluginLabel(string $calculatorPluginKey): string
    {
        $label = $this->translatorFacade->trans($calculatorPluginKey);

        if ($label !== $calculatorPluginKey) {
            return $label;
        }

        return ucfirst(trim(str_replace(
            [static::CALCULATOR_PLUGIN_KEY_PREFIX, '_'],
            ['', ' '],
            strtolower($calculatorPluginKey),
        )));
    }

    protected function resolveCalculatorPluginInputType(DiscountCalculatorPluginInterface $calculatorPlugin): string
    {
        if ($calculatorPlugin instanceof DiscountCalculatorPluginWithAmountInputTypeInterface) {
            return $calculatorPlugin->getInputType();
        }

        return DiscountConstants::CALCULATOR_DEFAULT_INPUT_TYPE;
    }

    protected function expandWithStores(
        DiscountConfigurationOptionsTransfer $discountConfigurationOptionsTransfer
    ): DiscountConfigurationOptionsTransfer {
        foreach ($this->storeFacade->getAllStores() as $storeTransfer) {
            $discountConfigurationOptionsTransfer->addStore($storeTransfer);
        }

        return $discountConfigurationOptionsTransfer;
    }

    protected function expandWithRuleFieldOptions(
        DiscountConfigurationOptionsTransfer $discountConfigurationOptionsTransfer
    ): DiscountConfigurationOptionsTransfer {
        $decisionRuleFieldOptionTransfers = $this->createRuleFieldOptions($this->createDecisionRuleMetaDataProvider());

        foreach ($decisionRuleFieldOptionTransfers as $discountRuleFieldOptionTransfer) {
            $discountConfigurationOptionsTransfer->addDecisionRuleField($discountRuleFieldOptionTransfer);
        }

        $collectorMetaDataProvider = $this->metaProviderFactory->createMetaProviderByType(MetaProviderFactory::TYPE_COLLECTOR);

        foreach ($this->createRuleFieldOptions($collectorMetaDataProvider) as $discountRuleFieldOptionTransfer) {
            $discountConfigurationOptionsTransfer->addCollectorField($discountRuleFieldOptionTransfer);
        }

        return $discountConfigurationOptionsTransfer;
    }

    /**
     * @return array<\Generated\Shared\Transfer\DiscountRuleFieldOptionTransfer>
     */
    protected function createRuleFieldOptions(MetaDataProviderInterface $metaDataProvider): array
    {
        $queryStringValueOptions = $metaDataProvider->getQueryStringValueOptions();
        $discountRuleFieldOptionTransfers = [];

        foreach ($metaDataProvider->getAvailableFields() as $field) {
            $discountRuleFieldOptionTransfers[] = (new DiscountRuleFieldOptionTransfer())
                ->setField($field)
                ->setOperators($metaDataProvider->getAvailableOperatorExpressionsForField($field))
                ->setAcceptedTypes($metaDataProvider->getAcceptedTypesByFieldName($field))
                ->setValueOptions($this->createRuleFieldValueOptions($queryStringValueOptions[$field] ?? []));
        }

        return $discountRuleFieldOptionTransfers;
    }

    /**
     * @param array<int|string, string> $valueOptions
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\DiscountRuleFieldValueOptionTransfer>
     */
    protected function createRuleFieldValueOptions(array $valueOptions): ArrayObject
    {
        /** @var \ArrayObject<int, \Generated\Shared\Transfer\DiscountRuleFieldValueOptionTransfer> $discountRuleFieldValueOptionTransfers */
        $discountRuleFieldValueOptionTransfers = new ArrayObject();

        foreach ($valueOptions as $value => $label) {
            $discountRuleFieldValueOptionTransfers->append(
                (new DiscountRuleFieldValueOptionTransfer())
                    ->setValue((string)$value)
                    ->setLabel($label),
            );
        }

        return $discountRuleFieldValueOptionTransfers;
    }

    protected function executeDiscountConfigurationOptionsExpanderPlugins(
        DiscountConfigurationOptionsTransfer $discountConfigurationOptionsTransfer
    ): DiscountConfigurationOptionsTransfer {
        foreach ($this->discountConfigurationOptionsExpanderPlugins as $discountConfigurationOptionsExpanderPlugin) {
            $discountConfigurationOptionsTransfer = $discountConfigurationOptionsExpanderPlugin
                ->expand($discountConfigurationOptionsTransfer);
        }

        return $discountConfigurationOptionsTransfer;
    }

    protected function createDecisionRuleMetaDataProvider(): MetaDataProviderInterface
    {
        return $this->metaProviderFactory->createMetaProviderByType(MetaProviderFactory::TYPE_DECISION_RULE);
    }

    protected function createIntegerRange(int $min, int $max): DiscountIntegerRangeTransfer
    {
        return (new DiscountIntegerRangeTransfer())
            ->setMin($min)
            ->setMax($max);
    }
}
