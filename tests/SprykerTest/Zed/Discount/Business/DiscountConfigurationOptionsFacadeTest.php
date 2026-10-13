<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\Discount\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\DiscountCollectorStrategyOptionTransfer;
use Generated\Shared\Transfer\DiscountConfigurationOptionsTransfer;
use Generated\Shared\Transfer\DiscountRuleFieldOptionTransfer;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;
use Spryker\Zed\Discount\DiscountConfig;
use Spryker\Zed\Discount\DiscountDependencyProvider;
use Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountConfigurationOptionsExpanderPluginInterface;
use SprykerTest\Zed\Discount\DiscountBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group Facade
 * @group DiscountConfigurationOptionsFacadeTest
 * Add your own group annotations below this line
 */
class DiscountConfigurationOptionsFacadeTest extends Unit
{
    protected const string COLLECTOR_STRATEGY_QUERY_STRING_LABEL = 'Query String';

    protected const string EXPANDED_COLLECTOR_STRATEGY_KEY = 'promotion';

    protected const string EXPANDED_COLLECTOR_STRATEGY_LABEL = 'Discount promotion to product';

    protected const string DECISION_RULE_FIELD_PRICE_MODE = 'price-mode';

    protected const string DECISION_RULE_FIELD_SKU = 'sku';

    protected const string COLLECTOR_FIELD_SKU = 'sku';

    protected DiscountBusinessTester $tester;

    /**
     * @dataProvider provideConfigurationOptionExpectations
     */
    public function testReturnsTheValuesTheDiscountConfigurationIsBuiltFrom(callable $assertOptions): void
    {
        // Act
        $discountConfigurationOptionsTransfer = $this->createDiscountFacade()->getDiscountConfigurationOptions();

        // Assert
        $assertOptions($discountConfigurationOptionsTransfer);
    }

    /**
     * @return array<string, array{callable}>
     */
    public function provideConfigurationOptionExpectations(): array
    {
        return [
            'given the core wiring, when the options are read, then both discount types are offered' => [
                function (DiscountConfigurationOptionsTransfer $optionCollectionTransfer): void {
                    $this->assertSame(
                        [DiscountConstants::TYPE_CART_RULE, DiscountConstants::TYPE_VOUCHER],
                        $optionCollectionTransfer->getDiscountTypes(),
                    );
                },
            ],
            'given the core wiring, when the options are read, then each calculator plugin carries its amount input type' => [
                function (DiscountConfigurationOptionsTransfer $optionCollectionTransfer): void {
                    $inputTypeByKey = [];

                    foreach ($optionCollectionTransfer->getCalculatorPlugins() as $calculatorPluginOptionTransfer) {
                        $inputTypeByKey[(string)$calculatorPluginOptionTransfer->getKey()] = $calculatorPluginOptionTransfer->getInputType();
                    }

                    $this->assertSame(
                        [
                            DiscountDependencyProvider::PLUGIN_CALCULATOR_PERCENTAGE => DiscountConstants::CALCULATOR_DEFAULT_INPUT_TYPE,
                            DiscountDependencyProvider::PLUGIN_CALCULATOR_FIXED => DiscountConstants::CALCULATOR_MONEY_INPUT_TYPE,
                        ],
                        $inputTypeByKey,
                    );
                },
            ],
            'given the core wiring, when the options are read, then each calculator plugin carries a readable label' => [
                function (DiscountConfigurationOptionsTransfer $optionCollectionTransfer): void {
                    foreach ($optionCollectionTransfer->getCalculatorPlugins() as $calculatorPluginOptionTransfer) {
                        $this->assertNotSame('', (string)$calculatorPluginOptionTransfer->getLabel());
                        $this->assertNotSame($calculatorPluginOptionTransfer->getKey(), $calculatorPluginOptionTransfer->getLabel());
                    }
                },
            ],
            'given an empty expander stack, when the options are read, then the query string strategy is the only one seeded' => [
                function (DiscountConfigurationOptionsTransfer $optionCollectionTransfer): void {
                    $this->assertSame(
                        [[DiscountConstants::DISCOUNT_COLLECTOR_STRATEGY_QUERY_STRING, self::COLLECTOR_STRATEGY_QUERY_STRING_LABEL]],
                        $this->mapCollectorStrategyOptions($optionCollectionTransfer),
                    );
                },
            ],
            'given the module configuration, when the options are read, then the value ranges equal it' => [
                function (DiscountConfigurationOptionsTransfer $optionCollectionTransfer): void {
                    $discountConfig = new DiscountConfig();

                    $this->assertSame(
                        [
                            [$discountConfig->getPriorityMinValue(), $discountConfig->getPriorityMaxValue()],
                            [$discountConfig->getVoucherCodesQuantityMinValue(), $discountConfig->getVoucherCodesQuantityMaxValue()],
                            [$discountConfig->getVoucherCodeLengthMinValue(), $discountConfig->getVoucherCodeLengthMaxValue()],
                        ],
                        [
                            [$optionCollectionTransfer->getPriorityRangeOrFail()->getMin(), $optionCollectionTransfer->getPriorityRangeOrFail()->getMax()],
                            [$optionCollectionTransfer->getVoucherCodesQuantityRangeOrFail()->getMin(), $optionCollectionTransfer->getVoucherCodesQuantityRangeOrFail()->getMax()],
                            [$optionCollectionTransfer->getVoucherCodeLengthRangeOrFail()->getMin(), $optionCollectionTransfer->getVoucherCodeLengthRangeOrFail()->getMax()],
                        ],
                    );
                },
            ],
            'given the core wiring, when the options are read, then both logical operators are offered' => [
                function (DiscountConfigurationOptionsTransfer $optionCollectionTransfer): void {
                    $this->assertSame(['and', 'or'], $optionCollectionTransfer->getLogicalOperators());
                },
            ],
            'given the core decision rule plugins, when the options are read, then each field carries its operators and accepted types' => [
                function (DiscountConfigurationOptionsTransfer $optionCollectionTransfer): void {
                    $discountRuleFieldOptionTransfer = $this->findRuleFieldOption(
                        $optionCollectionTransfer->getDecisionRuleFields()->getArrayCopy(),
                        self::DECISION_RULE_FIELD_SKU,
                    );

                    $this->assertNotNull($discountRuleFieldOptionTransfer);
                    $this->assertNotEmpty($discountRuleFieldOptionTransfer->getOperators());
                    $this->assertNotEmpty($discountRuleFieldOptionTransfer->getAcceptedTypes());
                    $this->assertCount(0, $discountRuleFieldOptionTransfer->getValueOptions());
                },
            ],
            'given a field with fixed values, when the options are read, then its value options are listed' => [
                function (DiscountConfigurationOptionsTransfer $optionCollectionTransfer): void {
                    $discountRuleFieldOptionTransfer = $this->findRuleFieldOption(
                        $optionCollectionTransfer->getDecisionRuleFields()->getArrayCopy(),
                        self::DECISION_RULE_FIELD_PRICE_MODE,
                    );

                    $this->assertNotNull($discountRuleFieldOptionTransfer);
                    $this->assertNotCount(0, $discountRuleFieldOptionTransfer->getValueOptions());

                    foreach ($discountRuleFieldOptionTransfer->getValueOptions() as $valueOptionTransfer) {
                        $this->assertNotNull($valueOptionTransfer->getValue());
                        $this->assertNotNull($valueOptionTransfer->getLabel());
                    }
                },
            ],
            'given the core collector plugins, when the options are read, then the collector fields are listed separately' => [
                function (DiscountConfigurationOptionsTransfer $optionCollectionTransfer): void {
                    $this->assertNotNull(
                        $this->findRuleFieldOption(
                            $optionCollectionTransfer->getCollectorFields()->getArrayCopy(),
                            self::COLLECTOR_FIELD_SKU,
                        ),
                    );
                    $this->assertNull(
                        $this->findRuleFieldOption(
                            $optionCollectionTransfer->getCollectorFields()->getArrayCopy(),
                            self::DECISION_RULE_FIELD_PRICE_MODE,
                        ),
                    );
                },
            ],
            'given the configured stores, when the options are read, then each is listed by name' => [
                function (DiscountConfigurationOptionsTransfer $optionCollectionTransfer): void {
                    $this->assertNotCount(0, $optionCollectionTransfer->getStores());

                    foreach ($optionCollectionTransfer->getStores() as $storeTransfer) {
                        $this->assertNotNull($storeTransfer->getName());
                        $this->assertIsArray($storeTransfer->getAvailableCurrencyIsoCodes());
                    }
                },
            ],
        ];
    }

    public function testAppendsTheCollectorStrategyContributedByAWiredExpanderPlugin(): void
    {
        // Arrange
        $this->tester->setDependency(
            DiscountDependencyProvider::PLUGINS_DISCOUNT_CONFIGURATION_OPTIONS_EXPANDER,
            [$this->createCollectorStrategyExpanderPlugin()],
        );

        // Act
        $discountConfigurationOptionsTransfer = $this->createDiscountFacade()->getDiscountConfigurationOptions();

        // Assert
        $this->assertSame(
            [
                [DiscountConstants::DISCOUNT_COLLECTOR_STRATEGY_QUERY_STRING, static::COLLECTOR_STRATEGY_QUERY_STRING_LABEL],
                [static::EXPANDED_COLLECTOR_STRATEGY_KEY, static::EXPANDED_COLLECTOR_STRATEGY_LABEL],
            ],
            $this->mapCollectorStrategyOptions($discountConfigurationOptionsTransfer),
        );
    }

    /**
     * @return array<int, array{string|null, string|null}>
     */
    protected function mapCollectorStrategyOptions(
        DiscountConfigurationOptionsTransfer $discountConfigurationOptionsTransfer
    ): array {
        $collectorStrategyOptions = [];

        foreach ($discountConfigurationOptionsTransfer->getCollectorStrategyTypes() as $collectorStrategyOptionTransfer) {
            $collectorStrategyOptions[] = [$collectorStrategyOptionTransfer->getKey(), $collectorStrategyOptionTransfer->getLabel()];
        }

        return $collectorStrategyOptions;
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\DiscountRuleFieldOptionTransfer> $discountRuleFieldOptionTransfers
     */
    protected function findRuleFieldOption(array $discountRuleFieldOptionTransfers, string $field): ?DiscountRuleFieldOptionTransfer
    {
        foreach ($discountRuleFieldOptionTransfers as $discountRuleFieldOptionTransfer) {
            if ($discountRuleFieldOptionTransfer->getField() === $field) {
                return $discountRuleFieldOptionTransfer;
            }
        }

        return null;
    }

    protected function createCollectorStrategyExpanderPlugin(): DiscountConfigurationOptionsExpanderPluginInterface
    {
        $discountConfigurationOptionsExpanderPluginMock = $this
            ->getMockBuilder(DiscountConfigurationOptionsExpanderPluginInterface::class)
            ->getMock();

        $discountConfigurationOptionsExpanderPluginMock
            ->method('expand')
            ->willReturnCallback(
                static fn (DiscountConfigurationOptionsTransfer $optionCollectionTransfer): DiscountConfigurationOptionsTransfer => $optionCollectionTransfer->addCollectorStrategyType(
                    (new DiscountCollectorStrategyOptionTransfer())
                            ->setKey(self::EXPANDED_COLLECTOR_STRATEGY_KEY)
                            ->setLabel(self::EXPANDED_COLLECTOR_STRATEGY_LABEL),
                ),
            );

        /** @var \Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountConfigurationOptionsExpanderPluginInterface $discountConfigurationOptionsExpanderPluginMock */
        return $discountConfigurationOptionsExpanderPluginMock;
    }

    protected function createDiscountFacade(): DiscountFacadeInterface
    {
        return $this->tester->getLocator()->discount()->facade();
    }
}
