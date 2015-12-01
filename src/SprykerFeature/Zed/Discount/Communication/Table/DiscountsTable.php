<?php

namespace SprykerFeature\Zed\Discount\Communication\Table;

use SprykerFeature\Zed\Application\Business\Url\Url;
use SprykerFeature\Zed\Discount\DiscountConfig;
use Orm\Zed\Discount\Persistence\Map\SpyDiscountTableMap;
use Orm\Zed\Discount\Persistence\SpyDiscount;
use Orm\Zed\Discount\Persistence\SpyDiscountQuery;
use SprykerFeature\Zed\Gui\Communication\Table\AbstractTable;
use SprykerFeature\Zed\Gui\Communication\Table\TableConfiguration;

class DiscountsTable extends AbstractTable
{

    const COL_VALUE = 'Value';
    const COL_PERIOD = 'Period';
    const COL_OPTIONS = 'Options';
    const DATE_FORMAT = 'Y-m-d';
    const COL_DECISION_RULES = 'Cart Rules';
    const DECISION_RULE_PLUGIN = 'DecisionRulePlugin';

    const PARAM_ID_DISCOUNT = 'id-discount';

    /**
     * @var SpyDiscountQuery
     */
    protected $discountQuery;

    /**
     * @param SpyDiscountQuery $discountQuery
     */
    public function __construct(SpyDiscountQuery $discountQuery)
    {
        $this->discountQuery = $discountQuery;
    }

    /**
     * @param TableConfiguration $config
     *
     * @return TableConfiguration
     */
    protected function configure(TableConfiguration $config)
    {
        $config->setHeader([
            SpyDiscountTableMap::COL_ID_DISCOUNT => 'ID',
            SpyDiscountTableMap::COL_DISPLAY_NAME => 'Display Name',
            SpyDiscountTableMap::COL_DESCRIPTION => 'Description',
            self::COL_VALUE => self::COL_VALUE,
            SpyDiscountTableMap::COL_IS_PRIVILEGED => 'Is Privileged',
            SpyDiscountTableMap::COL_IS_ACTIVE => 'Is Active',
            self::COL_PERIOD => self::COL_PERIOD,
            self::COL_DECISION_RULES => self::COL_DECISION_RULES,
            self::COL_OPTIONS => self::COL_OPTIONS,
        ]);

        return $config;
    }

    /**
     * @param TableConfiguration $config
     *
     * @return array
     */
    protected function prepareData(TableConfiguration $config)
    {
        $result = [];

        $query = $this->discountQuery
            ->where('fk_discount_voucher_pool IS NULL');

        $queryResult = $this->runQuery($query, $config, true);
        /** @var SpyDiscount $item */
        foreach ($queryResult as $item) {
            $chosenDecisionRules = array_column($item->getDecisionRules()->toArray(), self::DECISION_RULE_PLUGIN);

            $result[] = [
                SpyDiscountTableMap::COL_ID_DISCOUNT => $item->getIdDiscount(),
                SpyDiscountTableMap::COL_DISPLAY_NAME => $item->getDisplayName(),
                SpyDiscountTableMap::COL_DESCRIPTION => $item->getDescription(),
                self::COL_VALUE => $this->getDiscountPrice($item),
                SpyDiscountTableMap::COL_IS_PRIVILEGED => $item->getIsPrivileged(),
                SpyDiscountTableMap::COL_IS_ACTIVE => $item->getIsActive(),
                self::COL_PERIOD => $item->getValidFrom(self::DATE_FORMAT) . ' - ' . $item->getValidTo(self::DATE_FORMAT),
                self::COL_DECISION_RULES => implode(', ', $chosenDecisionRules),
                self::COL_OPTIONS => $this->getRowOptions($item),
            ];
        }

        return $result;
    }

    /**
     * @param SpyDiscount $discount
     *
     * @return string
     */
    protected function getDiscountPrice(SpyDiscount $discount)
    {
        $amount = $discount->getAmount();
        $amountType = $this->getDiscountAmountType($discount);

        return $amount . ' ' . $amountType;
    }

    /**
     * @param SpyDiscount $discount
     *
     * @return string
     */
    protected function getDiscountAmountType(SpyDiscount $discount)
    {
        if ($discount->getCalculatorPlugin() === DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE) {
            return 'percentage';
        }

        return 'fixed';
    }

    /**
     * @param SpyDiscount $item
     *
     * @return string
     */
    protected function getRowOptions(SpyDiscount $item)
    {
        $url = Url::generate('/discount/cart-rule/edit', [self::PARAM_ID_DISCOUNT => $item->getIdDiscount()]);

        return '<a class="btn btn-xs btn-info" href="' . $url->buildEscaped() . '">Edit</a>';
    }

}
