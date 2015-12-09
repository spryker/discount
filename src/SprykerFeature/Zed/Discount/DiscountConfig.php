<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerFeature\Zed\Discount;

use SprykerEngine\Zed\Kernel\AbstractBundleConfig;
use SprykerFeature\Zed\Discount\Business\Collector\CollectorInterface;
use SprykerFeature\Zed\Discount\Business\Model\CalculatorInterface;
use SprykerFeature\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginInterface;
use SprykerFeature\Zed\Discount\Dependency\Plugin\DiscountCollectorPluginInterface;
use SprykerFeature\Zed\Discount\Dependency\Plugin\DiscountDecisionRulePluginInterface;

class DiscountConfig extends AbstractBundleConfig implements DiscountConfigInterface
{

    const DEFAULT_VOUCHER_CODE_LENGTH = 6;
    const URL_DISCOUNT_POOL_EDIT = '/discount/pool/edit';

    const PARAM_ID_POOL = 'id-pool';
    const PARAM_ID_DISCOUNT = 'id-discount';

    const PLUGIN_DECISION_RULE_VOUCHER = 'PLUGIN_DECISION_RULE_VOUCHER';
    const PLUGIN_DECISION_RULE_MINIMUM_CART_SUB_TOTAL = 'PLUGIN_DECISION_RULE_MINIMUM_CART_SUB_TOTAL';
    const PLUGIN_COLLECTOR_ITEM = 'PLUGIN_COLLECTOR_ITEM';
    const PLUGIN_COLLECTOR_ITEM_PRODUCT_OPTION = 'PLUGIN_COLLECTOR_ITEM_PRODUCT_OPTION';
    const PLUGIN_COLLECTOR_AGGREGATE = 'PLUGIN_COLLECTOR_AGGREGATE';
    const PLUGIN_COLLECTOR_ORDER_EXPENSE = 'PLUGIN_COLLECTOR_ORDER_EXPENSE';
    const PLUGIN_COLLECTOR_ITEM_EXPENSE = 'PLUGIN_COLLECTOR_ITEM_EXPENSE';
    const PLUGIN_CALCULATOR_PERCENTAGE = 'PLUGIN_CALCULATOR_PERCENTAGE';
    const PLUGIN_CALCULATOR_FIXED = 'PLUGIN_CALCULATOR_FIXED';

    /**
     * @var DiscountDecisionRulePluginInterface[]
     */
    protected $decisionRulePlugins = [];

    /**
     * @var DiscountCalculatorPluginInterface[]
     */
    protected $calculatorPlugins = [];

    /**
     * @var DiscountCollectorPluginInterface[]
     */
    protected $collectorPlugins = [];

    /**
     * @return array
     */
    public function getAvailableDecisionRulePlugins()
    {
        return [
            self::PLUGIN_DECISION_RULE_VOUCHER => new \SprykerFeature\Zed\Discount\Communication\Plugin\DecisionRule\Voucher(),
            self::PLUGIN_DECISION_RULE_MINIMUM_CART_SUB_TOTAL => new \SprykerFeature\Zed\Discount\Communication\Plugin\DecisionRule\MinimumCartSubtotal(),
        ];
    }

    /**
     * @return CalculatorInterface[]
     */
    public function getAvailableCalculatorPlugins()
    {
        return [
            self::PLUGIN_CALCULATOR_PERCENTAGE => new \SprykerFeature\Zed\Discount\Communication\Plugin\Calculator\Percentage(),
            self::PLUGIN_CALCULATOR_FIXED => new \SprykerFeature\Zed\Discount\Communication\Plugin\Calculator\Fixed(),
        ];
    }

    /**
     * @return CollectorInterface[]
     */
    public function getAvailableCollectorPlugins()
    {
        return [
            self::PLUGIN_COLLECTOR_ITEM => new \SprykerFeature\Zed\Discount\Communication\Plugin\Collector\Item(),
            self::PLUGIN_COLLECTOR_ORDER_EXPENSE => new \SprykerFeature\Zed\Discount\Communication\Plugin\Collector\OrderExpense(),
            self::PLUGIN_COLLECTOR_ITEM_EXPENSE => new \SprykerFeature\Zed\Discount\Communication\Plugin\Collector\ItemExpense(),
            self::PLUGIN_COLLECTOR_ITEM_PRODUCT_OPTION => new \SprykerFeature\Zed\Discount\Communication\Plugin\Collector\ItemProductOption(),
            self::PLUGIN_COLLECTOR_AGGREGATE => new \SprykerFeature\Zed\Discount\Communication\Plugin\Collector\Aggregate(),
        ];
    }

    /**
     * @throws \ErrorException
     *
     * @return DiscountDecisionRulePluginInterface
     */
    public function getDefaultVoucherDecisionRulePlugin()
    {
        if (!array_key_exists(self::PLUGIN_DECISION_RULE_VOUCHER, $this->getAvailableDecisionRulePlugins())) {
            throw new \ErrorException('No default voucher decision rule plugin registered');
        }

        return $this->getAvailableDecisionRulePlugins()[self::PLUGIN_DECISION_RULE_VOUCHER];
    }

    /**
     * @param string $pluginName
     *
     * @return DiscountDecisionRulePluginInterface
     */
    public function getDecisionRulePluginByName($pluginName)
    {
        return $this->getAvailableDecisionRulePlugins()[$pluginName];
    }

    /**
     * @return array
     */
    public function getDecisionPluginNames()
    {
        return array_keys($this->getAvailableDecisionRulePlugins());
    }

    /**
     * @param string $pluginName
     *
     * @return DiscountCalculatorPluginInterface
     */
    public function getCalculatorPluginByName($pluginName)
    {
        return $this->getAvailableCalculatorPlugins()[$pluginName];
    }

    /**
     * @param string $pluginName
     *
     * @return DiscountCollectorPluginInterface
     */
    public function getCollectorPluginByName($pluginName)
    {
        return $this->getAvailableCollectorPlugins()[$pluginName];
    }

    /**
     * @return int
     */
    public function getVoucherCodeLength()
    {
        return self::DEFAULT_VOUCHER_CODE_LENGTH;
    }

    /**
     * @return array
     */
    public function getVoucherCodeCharacters()
    {
        return [
            self::KEY_VOUCHER_CODE_CONSONANTS => [
                'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z',
            ],
            self::KEY_VOUCHER_CODE_VOWELS => [
                'a', 'e', 'u',
            ],
            self::KEY_VOUCHER_CODE_NUMBERS => [
                1, 2, 3, 4, 5, 6, 7, 8, 9,
            ],
        ];
    }

    /**
     * @return int
     */
    public function getAllowedCodeCharactersLength()
    {
        $charactersLength = array_reduce($this->getVoucherCodeCharacters(), function ($length, $items) {
            $length += count($items);

            return $length;
        });

        return $charactersLength;
    }

    /**
     * @return string
     */
    public function getVoucherPoolTemplateReplacementString()
    {
        return '[code]';
    }

}
