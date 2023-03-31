<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount;

use Spryker\Zed\Discount\Communication\Plugin\Calculator\FixedPlugin;
use Spryker\Zed\Discount\Communication\Plugin\Calculator\PercentagePlugin;
use Spryker\Zed\Discount\Communication\Plugin\Collector\ItemByPriceCollectorPlugin;
use Spryker\Zed\Discount\Communication\Plugin\Collector\ItemByQuantityCollectorPlugin;
use Spryker\Zed\Discount\Communication\Plugin\Collector\ItemBySkuCollectorPlugin;
use Spryker\Zed\Discount\Communication\Plugin\DecisionRule\CalendarWeekDecisionRulePlugin;
use Spryker\Zed\Discount\Communication\Plugin\DecisionRule\CurrencyDecisionRulePlugin;
use Spryker\Zed\Discount\Communication\Plugin\DecisionRule\DayOfTheWeekDecisionRulePlugin;
use Spryker\Zed\Discount\Communication\Plugin\DecisionRule\GrandTotalDecisionRulePlugin;
use Spryker\Zed\Discount\Communication\Plugin\DecisionRule\ItemPriceDecisionRulePlugin;
use Spryker\Zed\Discount\Communication\Plugin\DecisionRule\ItemQuantityDecisionRulePlugin;
use Spryker\Zed\Discount\Communication\Plugin\DecisionRule\MonthDecisionRulePlugin;
use Spryker\Zed\Discount\Communication\Plugin\DecisionRule\PriceModeDecisionRulePlugin;
use Spryker\Zed\Discount\Communication\Plugin\DecisionRule\SkuDecisionRulePlugin;
use Spryker\Zed\Discount\Communication\Plugin\DecisionRule\SubTotalDecisionRulePlugin;
use Spryker\Zed\Discount\Communication\Plugin\DecisionRule\TimeDecisionRulePlugin;
use Spryker\Zed\Discount\Communication\Plugin\DecisionRule\TotalQuantityDecisionRulePlugin;
use Spryker\Zed\Discount\Dependency\External\DiscountToSymfonyValidationAdapter;
use Spryker\Zed\Discount\Dependency\Facade\DiscountToCurrencyBridge;
use Spryker\Zed\Discount\Dependency\Facade\DiscountToLocaleFacadeBridge;
use Spryker\Zed\Discount\Dependency\Facade\DiscountToMessengerBridge;
use Spryker\Zed\Discount\Dependency\Facade\DiscountToMoneyBridge;
use Spryker\Zed\Discount\Dependency\Facade\DiscountToStoreFacadeBridge;
use Spryker\Zed\Discount\Dependency\Facade\DiscountToTranslatorFacadeBridge;
use Spryker\Zed\Discount\Exception\MissingMoneyCollectionFormTypePluginException;
use Spryker\Zed\Discount\Exception\MissingStoreRelationFormTypePluginException;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Communication\Form\FormTypeInterface;
use Spryker\Zed\Kernel\Container;

/**
 * @method \Spryker\Zed\Discount\DiscountConfig getConfig()
 */
class DiscountDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const FACADE_MESSENGER = 'MESSENGER_FACADE';

    /**
     * @var string
     */
    public const FACADE_MONEY = 'MONEY_FACADE';

    /**
     * @var string
     */
    public const FACADE_CURRENCY = 'CURRENCY_FACADE';

    /**
     * @var string
     */
    public const FACADE_STORE = 'FACADE_STORE';

    /**
     * @var string
     */
    public const FACADE_LOCALE = 'FACADE_LOCALE';

    /**
     * @var string
     */
    public const FACADE_TRANSLATOR = 'FACADE_TRANSLATOR';

    /**
     * @var string
     */
    public const ADAPTER_VALIDATION = 'ADAPTER_VALIDATION';

    /**
     * @var string
     */
    public const PLUGIN_CALCULATOR_PERCENTAGE = 'PLUGIN_CALCULATOR_PERCENTAGE';

    /**
     * @var string
     */
    public const PLUGIN_CALCULATOR_FIXED = 'PLUGIN_CALCULATOR_FIXED';

    /**
     * @var string
     */
    public const PLUGIN_DISCOUNTABLE_ITEM_FILTER = 'PLUGIN_DISCOUNTABLE_ITEM_FILTER';

    /**
     * @var string
     */
    public const PLUGIN_COLLECTOR_STRATEGY_PLUGINS = 'PLUGIN_COLLECTOR_STRATEGY';

    /**
     * @var string
     */
    public const PLUGIN_DISCOUNT_POST_CREATE = 'PLUGIN_DISCOUNT_POST_CREATE';

    /**
     * @var string
     */
    public const PLUGIN_DISCOUNT_POST_UPDATE = 'PLUGIN_DISCOUNT_POST_UPDATE';

    /**
     * @var string
     */
    public const PLUGIN_DISCOUNT_CONFIGURATION_EXPANDER = 'PLUGIN_DISCOUNT_CONFIGURATION_EXPANDER';

    /**
     * @var string
     */
    public const PLUGIN_DISCOUNT_FORM_TYPE_EXPANDER = 'PLUGIN_DISCOUNT_FORM_EXPANDER';

    /**
     * @var string
     */
    public const PLUGIN_DISCOUNT_FORM_DATA_PROVIDER_EXPANDER = 'PLUGIN_DISCOUNT_FORM_DATA_PROVIDER_EXPANDER';

    /**
     * @var string
     */
    public const PLUGIN_DISCOUNT_VIEW_BLOCK_PROVIDER = 'PLUGIN_DISCOUNT_VIEW_BLOCK_PROVIDER';

    /**
     * @var string
     */
    public const PLUGIN_DISCOUNT_APPLICABLE_FILTER_PLUGINS = 'PLUGIN_DISCOUNT_APPLICABLE_FILTER_PLUGINS';

    /**
     * @var string
     */
    public const PLUGIN_DISCOUNTABLE_ITEM_TRANSFORMER_STRATEGY = 'PLUGIN_DISCOUNTABLE_ITEM_TRANSFORMER_STRATEGY';

    /**
     * @var string
     */
    public const PLUGIN_MONEY_COLLECTION_FORM_TYPE = 'PLUGIN_MONEY_COLLECTION_FORM_TYPE';

    /**
     * @var string
     */
    public const DECISION_RULE_PLUGINS = 'DECISION_RULE_PLUGINS';

    /**
     * @var string
     */
    public const CALCULATOR_PLUGINS = 'CALCULATOR_PLUGINS';

    /**
     * @var string
     */
    public const COLLECTED_DISCOUNT_GROUPING_PLUGINS = 'COLLECTED_DISCOUNT_GROUPING_PLUGINS';

    /**
     * @var string
     */
    public const COLLECTOR_PLUGINS = 'COLLECTOR_PLUGINS';

    /**
     * @var string
     */
    public const PLUGIN_STORE_RELATION_FORM_TYPE = 'PLUGIN_STORE_RELATION_FORM_TYPE';

    /**
     * @var string
     */
    public const PLUGINS_DISCOUNT_VOUCHER_APPLY_CHECKER_STRATEGY = 'PLUGINS_DISCOUNT_VOUCHER_APPLY_CHECKER_STRATEGY';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container)
    {
        $container = $this->addMessengerFacade($container);
        $container = $this->addCalculatorPlugins($container);
        $container = $this->addCollectedDiscountGroupingPlugins($container);
        $container = $this->addDecisionRulePlugins($container);
        $container = $this->addCollectorPlugins($container);
        $container = $this->addDiscountableItemFilterPlugins($container);
        $container = $this->addMoneyFacade($container);
        $container = $this->addCollectorStrategyPlugins($container);
        $container = $this->addDiscountPostCreatePlugins($container);
        $container = $this->addDiscountPostUpdatePlugins($container);
        $container = $this->addDiscountConfigurationExpanderPlugins($container);
        $container = $this->addDiscountApplicableFilterPlugins($container);
        $container = $this->addCurrencyFacade($container);
        $container = $this->addStoreFacade($container);
        $container = $this->addDiscountableItemExpanderStrategyPlugins($container);
        $container = $this->addValidationAdapter($container);
        $container = $this->addTranslatorFacade($container);
        $container = $this->addDiscountVoucherApplyCheckerStrategyPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideCommunicationLayerDependencies(Container $container)
    {
        $container = $this->addDecisionRulePlugins($container);
        $container = $this->addCalculatorPlugins($container);
        $container = $this->addCollectorPlugins($container);
        $container = $this->addMoneyFacade($container);
        $container = $this->addDiscountFormExpanderPlugins($container);
        $container = $this->addDiscountFormDataProviderExpanderPlugins($container);
        $container = $this->addDiscountViewBlockProviderPlugins($container);
        $container = $this->addCurrencyFacade($container);
        $container = $this->addStoreRelationFormTypePlugin($container);
        $container = $this->addLocaleFacade($container);
        $container = $this->addTranslatorFacade($container);
        $container = $this->addStoreFacade($container);
        $container = $this->addMoneyCollectionFormTypePlugin($container);

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginInterface>
     */
    public function getAvailableCalculatorPlugins()
    {
        return [
            static::PLUGIN_CALCULATOR_PERCENTAGE => new PercentagePlugin(),
            static::PLUGIN_CALCULATOR_FIXED => new FixedPlugin(),
        ];
    }

    /**
     * @return array<\Spryker\Zed\DiscountExtension\Dependency\Plugin\CollectedDiscountGroupingStrategyPluginInterface>
     */
    protected function getCollectedDiscountGroupingPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountableItemTransformerStrategyPluginInterface>
     */
    protected function getDiscountableItemTransformerStrategyPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountableItemCollectorPluginInterface>
     */
    protected function getCollectorPlugins()
    {
        return [
            new ItemBySkuCollectorPlugin(),
            new ItemByQuantityCollectorPlugin(),
            new ItemByPriceCollectorPlugin(),
        ];
    }

    /**
     * @return array<\Spryker\Zed\DiscountExtension\Dependency\Plugin\DecisionRulePluginInterface>
     */
    protected function getDecisionRulePlugins()
    {
        return [
            new SkuDecisionRulePlugin(),
            new CurrencyDecisionRulePlugin(),
            new PriceModeDecisionRulePlugin(),
            new GrandTotalDecisionRulePlugin(),
            new SubTotalDecisionRulePlugin(),
            new TotalQuantityDecisionRulePlugin(),
            new ItemQuantityDecisionRulePlugin(),
            new ItemPriceDecisionRulePlugin(),
            new CalendarWeekDecisionRulePlugin(),
            new DayOfTheWeekDecisionRulePlugin(),
            new MonthDecisionRulePlugin(),
            new TimeDecisionRulePlugin(),
        ];
    }

    /**
     * This is additional filter applied to discountable items, the plugins are triggered after discount collectors run
     * this ensures that certain items are never picked by discount calculation and removed from DiscountableItem stack.
     *
     * @return array<\Spryker\Zed\Discount\Dependency\Plugin\DiscountableItemFilterPluginInterface>
     */
    protected function getDiscountableItemFilterPlugins()
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMoneyFacade(Container $container)
    {
        $container->set(static::FACADE_MONEY, function (Container $container) {
            $discountToMoneyBridge = new DiscountToMoneyBridge($container->getLocator()->money()->facade());

            return $discountToMoneyBridge;
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMessengerFacade(Container $container)
    {
        $container->set(static::FACADE_MESSENGER, function (Container $container) {
            return new DiscountToMessengerBridge($container->getLocator()->messenger()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addValidationAdapter(Container $container): Container
    {
        $container->set(static::ADAPTER_VALIDATION, function () {
            return new DiscountToSymfonyValidationAdapter();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addCalculatorPlugins(Container $container)
    {
        $container->set(static::CALCULATOR_PLUGINS, function () {
            return $this->getAvailableCalculatorPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addCollectedDiscountGroupingPlugins(Container $container): Container
    {
        $container->set(static::COLLECTED_DISCOUNT_GROUPING_PLUGINS, function () {
            return $this->getCollectedDiscountGroupingPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDecisionRulePlugins(Container $container)
    {
        $container->set(static::DECISION_RULE_PLUGINS, function () {
            return $this->getDecisionRulePlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addCollectorPlugins(Container $container)
    {
        $container->set(static::COLLECTOR_PLUGINS, function () {
            return $this->getCollectorPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDiscountableItemFilterPlugins(Container $container)
    {
        $container->set(static::PLUGIN_DISCOUNTABLE_ITEM_FILTER, function () {
            return $this->getDiscountableItemFilterPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addCollectorStrategyPlugins(Container $container)
    {
        $container->set(static::PLUGIN_COLLECTOR_STRATEGY_PLUGINS, function () {
            return $this->getCollectorStrategyPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\Discount\Dependency\Plugin\CollectorStrategyPluginInterface>
     */
    protected function getCollectorStrategyPlugins()
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDiscountPostCreatePlugins(Container $container)
    {
        $container->set(static::PLUGIN_DISCOUNT_POST_CREATE, function () {
            return $this->getDiscountPostCreatePlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\Discount\Dependency\Plugin\DiscountPostCreatePluginInterface>
     */
    protected function getDiscountPostCreatePlugins()
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDiscountPostUpdatePlugins(Container $container)
    {
        $container->set(static::PLUGIN_DISCOUNT_POST_UPDATE, function () {
            return $this->getDiscountPostUpdatePlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\Discount\Dependency\Plugin\DiscountPostUpdatePluginInterface>
     */
    protected function getDiscountPostUpdatePlugins()
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDiscountVoucherApplyCheckerStrategyPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_DISCOUNT_VOUCHER_APPLY_CHECKER_STRATEGY, function () {
            return $this->getDiscountVoucherApplyCheckerStrategyPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountVoucherApplyCheckerStrategyPluginInterface>
     */
    protected function getDiscountVoucherApplyCheckerStrategyPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function addDiscountConfigurationExpanderPlugins(Container $container)
    {
        $container->set(static::PLUGIN_DISCOUNT_CONFIGURATION_EXPANDER, function () {
            return $this->getDiscountConfigurationExpanderPlugins();
        });

        return $container;
    }

    /**
     * This plugin allows to expand DiscountConfigurationTransfer when using
     *
     * @return array<\Spryker\Zed\Discount\Dependency\Plugin\DiscountConfigurationExpanderPluginInterface>
     */
    protected function getDiscountConfigurationExpanderPlugins()
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function addDiscountFormExpanderPlugins(Container $container)
    {
        $container->set(static::PLUGIN_DISCOUNT_FORM_TYPE_EXPANDER, function () {
            return $this->getDiscountFormExpanderPlugins();
        });

        return $container;
    }

    /**
     * This plugin allows to expand DiscountConfigurationTransfer when using
     *
     * @return array<\Spryker\Zed\Discount\Dependency\Plugin\Form\DiscountFormExpanderPluginInterface>
     */
    protected function getDiscountFormExpanderPlugins()
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDiscountFormDataProviderExpanderPlugins(Container $container)
    {
        $container->set(static::PLUGIN_DISCOUNT_FORM_DATA_PROVIDER_EXPANDER, function () {
            return $this->getDiscountFormDataProviderExpanderPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\Discount\Dependency\Plugin\Form\DiscountFormDataProviderExpanderPluginInterface>
     */
    protected function getDiscountFormDataProviderExpanderPlugins()
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDiscountViewBlockProviderPlugins(Container $container)
    {
        $container->set(static::PLUGIN_DISCOUNT_VIEW_BLOCK_PROVIDER, function () {
            return $this->getDiscountViewTemplateProviderPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\Discount\Dependency\Plugin\DiscountViewBlockProviderPluginInterface>
     */
    protected function getDiscountViewTemplateProviderPlugins()
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDiscountApplicableFilterPlugins(Container $container)
    {
        $container->set(static::PLUGIN_DISCOUNT_APPLICABLE_FILTER_PLUGINS, function () {
            return $this->getDiscountApplicableFilterPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\Discount\Dependency\Plugin\DiscountViewBlockProviderPluginInterface>
     */
    protected function getDiscountApplicableFilterPlugins()
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addCurrencyFacade(Container $container)
    {
        $container->set(static::FACADE_CURRENCY, function (Container $container) {
            return new DiscountToCurrencyBridge($container->getLocator()->currency()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addStoreFacade(Container $container)
    {
        $container->set(static::FACADE_STORE, function (Container $container) {
            return new DiscountToStoreFacadeBridge($container->getLocator()->store()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addLocaleFacade(Container $container): Container
    {
        $container->set(static::FACADE_LOCALE, function (Container $container) {
            return new DiscountToLocaleFacadeBridge(
                $container->getLocator()->locale()->facade(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addStoreRelationFormTypePlugin(Container $container)
    {
        $container->set(static::PLUGIN_STORE_RELATION_FORM_TYPE, function () {
            return $this->getStoreRelationFormTypePlugin();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDiscountableItemExpanderStrategyPlugins(Container $container): Container
    {
        $container->set(static::PLUGIN_DISCOUNTABLE_ITEM_TRANSFORMER_STRATEGY, function () {
            return $this->getDiscountableItemTransformerStrategyPlugins();
        });

        return $container;
    }

    /**
     * @throws \Spryker\Zed\Discount\Exception\MissingStoreRelationFormTypePluginException
     *
     * @return \Spryker\Zed\Kernel\Communication\Form\FormTypeInterface
     */
    protected function getStoreRelationFormTypePlugin()
    {
        throw new MissingStoreRelationFormTypePluginException(
            sprintf(
                'Missing instance of %s! You need to configure StoreRelationFormType ' .
                'in your own DiscountDependencyProvider::getStoreRelationFormTypePlugin() ' .
                'to be able to manage discounts.',
                FormTypeInterface::class,
            ),
        );
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addTranslatorFacade(Container $container): Container
    {
        $container->set(static::FACADE_TRANSLATOR, function (Container $container) {
            return new DiscountToTranslatorFacadeBridge(
                $container->getLocator()->translator()->facade(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMoneyCollectionFormTypePlugin(Container $container): Container
    {
        $container->set(static::PLUGIN_MONEY_COLLECTION_FORM_TYPE, function () {
            return $this->getMoneyCollectionFormTypePlugin();
        });

        return $container;
    }

    /**
     * @throws \Spryker\Zed\Discount\Exception\MissingMoneyCollectionFormTypePluginException
     *
     * @return \Spryker\Zed\Kernel\Communication\Form\FormTypeInterface
     */
    protected function getMoneyCollectionFormTypePlugin(): FormTypeInterface
    {
        throw new MissingMoneyCollectionFormTypePluginException(
            sprintf(
                'Missing instance of %s! You need to configure MoneyCollectionFormType ' .
                'in your own DiscountDependencyProvider::getMoneyCollectionFormTypePlugin() ' .
                'to be able to discount prices.',
                FormTypeInterface::class,
            ),
        );
    }
}
