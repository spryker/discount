<?php

namespace Functional\SprykerFeature\Zed\Discount\Business;

use Codeception\TestCase\Test;
use Generated\Shared\Transfer\SalesOrderItemTransfer;
use Generated\Shared\Transfer\SalesOrderTransfer;
use Generated\Shared\Transfer\OrderItemsTransfer;
use SprykerEngine\Shared\Config;
use SprykerFeature\Zed\Discount\Business\Model\Calculator;
use SprykerFeature\Zed\Discount\Business\DecisionRule;
use SprykerFeature\Zed\Discount\Business\DiscountDependencyContainer;
use SprykerFeature\Zed\Discount\Business\Model\Distributor;
use SprykerEngine\Zed\Kernel\Business\Factory;
use SprykerEngine\Zed\Kernel\Locator;
use SprykerFeature\Zed\Discount\DiscountConfig;
use SprykerFeature\Zed\Discount\Persistence\Propel\SpyDiscountQuery;

/**
 * @group DiscountCalculatorTest
 * @group Discount
 */
class CalculatorTest extends Test
{
    const ITEM_GROSS_PRICE_500 = 500;

    public function testCalculationWithoutAnyDiscountShouldNotReturnMatchingDiscounts()
    {
        $settings = new DiscountConfig(Config::getInstance(), Locator::getInstance());
        $calculator = new Calculator();

        $order = $this->getOrderWithTwoItems();

        $result = $calculator->calculate([], $order, $settings, new Distributor(Locator::getInstance()));

        $this->assertEquals(0, count($result));
    }

    public function testOneDiscountShouldNotBeFilteredOut()
    {
        $discount = $this->initializeDiscount(
            'name 1',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            true
        );

        $settings = new DiscountConfig(Config::getInstance(), Locator::getInstance());
        $calculator = new Calculator();

        $order = $this->getOrderWithTwoItems();

        $result = $calculator->calculate([$discount], $order, $settings, new Distributor(Locator::getInstance()));

        $this->assertEquals(1, count($result));
    }

    public function testTwoDiscountsShouldNotBeFilteredOut()
    {
        $this->initializeDiscount(
            'name 1',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            true
        );
        $this->initializeDiscount(
            'name 2',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            false
        );

        $settings = new DiscountConfig(Config::getInstance(), Locator::getInstance());

        $calculator = new Calculator();

        $order = $this->getOrderWithTwoItems();
        $result = $calculator->calculate(
            $this->retrieveDiscounts(),
            $order,
            $settings,
            new Distributor(Locator::getInstance())
        );
        $this->assertEquals(2, count($result));
    }

    public function testFilterOutLowestUnprivilegedDiscountIfThereAreMoreThanOne()
    {
        $this->initializeDiscount(
            'name 1',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            true
        );
        $this->initializeDiscount(
            'name 2',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            false
        );
        $this->initializeDiscount(
            'name 3',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            60,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            false
        );

        $settings = new DiscountConfig(Config::getInstance(), Locator::getInstance());
        $calculator = new Calculator();

        $order = $this->getOrderWithTwoItems();
        $result = $calculator->calculate(
            $this->retrieveDiscounts(),
            $order,
            $settings,
            new Distributor(Locator::getInstance())
        );
        $this->assertEquals(2, count($result));
    }

    public function testFilterOutLowestUnprivilegedDiscountIfThereAreMoreThanTwo()
    {
        $this->initializeDiscount(
            'name 1',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            true
        );
        $this->initializeDiscount(
            'name 2',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            false
        );
        $this->initializeDiscount(
            'name 3',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            60,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            false
        );
        $this->initializeDiscount(
            'name 4',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            70,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            false
        );

        $settings = new DiscountConfig(Config::getInstance(), Locator::getInstance());
        $calculator = new Calculator();

        $order = $this->getOrderWithTwoItems();
        $result = $calculator->calculate(
            $this->retrieveDiscounts(),
            $order,
            $settings,
            new Distributor(Locator::getInstance())
        );
        $this->assertEquals(2, count($result));
    }

    public function testFilterOutLowestUnprivilegedDiscountIfThereAreMoreThanTwoAndTwoPrivilegedOnes()
    {
        $this->initializeDiscount(
            'name 1',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            true
        );
        $this->initializeDiscount(
            'name 2',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            true
        );
        $this->initializeDiscount(
            'name 3',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            60,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            false
        );
        $this->initializeDiscount(
            'name 4',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            70,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            false
        );

        $settings = new DiscountConfig(Config::getInstance(), Locator::getInstance());
        $calculator = new Calculator();

        $order = $this->getOrderWithTwoItems();
        $result = $calculator->calculate($this->retrieveDiscounts(), $order, $settings, new Distributor(Locator::getInstance()));
        $this->assertEquals(3, count($result));
    }

    /**
     * @param $displayName
     * @param $calculatorPlugin
     * @param $amount
     * @param $isActive
     * @param $collectorPlugin
     * @param bool $isPrivileged
     * @return \SprykerFeature\Zed\Discount\Persistence\Propel\SpyDiscount
     */
    protected function initializeDiscount(
        $displayName,
        $calculatorPlugin,
        $amount,
        $isActive,
        $collectorPlugin,
        $isPrivileged = true
    ) {
        $discount = new \SprykerFeature\Zed\Discount\Persistence\Propel\SpyDiscount();
        $discount->setDisplayName($displayName);
        $discount->setAmount($amount);
        $discount->setIsActive($isActive);
        $discount->setCalculatorPlugin($calculatorPlugin);
        $discount->setCollectorPlugin($collectorPlugin);
        $discount->setIsPrivileged($isPrivileged);
        $discount->save();

        return $discount;
    }

    /**
     * @return mixed
     */
    protected function getOrderWithTwoItems()
    {
        $locator = Locator::getInstance();
        $order = new SalesOrderTransfer();
        $item = new SalesOrderItemTransfer();
        $itemCollection = new OrderItemsTransfer();

        $item->setGrossPrice(self::ITEM_GROSS_PRICE_500);
        $itemCollection->addOrderItem($item);
        $itemCollection->addOrderItem(clone $item);

        $order->setItems($itemCollection);

        return $order;
    }

    /**
     * @return array
     */
    protected function retrieveDiscounts()
    {
        $result = [];
        foreach ((new SpyDiscountQuery())->find() as $discount) {
            $result[] = $discount;
        }

        return $result;
    }
}
