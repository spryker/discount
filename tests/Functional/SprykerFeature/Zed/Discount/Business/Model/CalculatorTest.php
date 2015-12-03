<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Functional\SprykerFeature\Zed\Discount\Business\Model;

use Codeception\TestCase\Test;
use Generated\Shared\Transfer\DiscountCollectorTransfer;
use Generated\Shared\Transfer\DiscountTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use SprykerEngine\Shared\Config;
use SprykerFeature\Zed\Discount\Business\Distributor\Distributor;
use SprykerFeature\Zed\Discount\Business\Model\Calculator;
use SprykerEngine\Zed\Kernel\Locator;
use SprykerFeature\Zed\Discount\Business\Model\CollectorResolver;
use SprykerFeature\Zed\Discount\DiscountConfig;
use SprykerFeature\Zed\Sales\Business\Model\CalculableContainer;

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
        $calculator = $this->getCalculator();

        $order = $this->getCalculableContainerWithTwoItems();

        $result = $calculator->calculate([], $order, $settings, new Distributor(Locator::getInstance()));

        $this->assertEquals(0, count($result));
    }

    public function testOneDiscountShouldNotBeFilteredOut()
    {
        $discountCollection = [];
        $discountCollection[] = $discount = $this->initializeDiscount(
            'name 1',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            true
        );

        $settings = new DiscountConfig(Config::getInstance(), Locator::getInstance());
        $calculator = $this->getCalculator();

        $calculableContainer = $this->getCalculableContainerWithTwoItems();

        $result = $calculator->calculate(
            $discountCollection,
            $calculableContainer,
            $settings,
            new Distributor(Locator::getInstance())
        );

        $this->assertEquals(1, count($result));
    }

    public function testTwoDiscountsShouldNotBeFilteredOut()
    {
        $discountCollection = [];
        $discountCollection[] = $this->initializeDiscount(
            'name 1',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            true
        );
        $discountCollection[] = $this->initializeDiscount(
            'name 2',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            false
        );

        $settings = new DiscountConfig(Config::getInstance(), Locator::getInstance());

        $calculator = $this->getCalculator();

        $order = $this->getCalculableContainerWithTwoItems();
        $result = $calculator->calculate(
            $discountCollection,
            $order,
            $settings,
            new Distributor(Locator::getInstance())
        );
        $this->assertEquals(2, count($result));
    }

    public function testFilterOutLowestUnprivilegedDiscountIfThereAreMoreThanOne()
    {
        $discountCollection = [];
        $discountCollection[] = $this->initializeDiscount(
            'name 1',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            true
        );
        $discountCollection[] = $this->initializeDiscount(
            'name 2',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            false
        );
        $discountCollection[] = $this->initializeDiscount(
            'name 3',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            60,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            false
        );

        $settings = new DiscountConfig(Config::getInstance(), Locator::getInstance());
        $calculator = $this->getCalculator();

        $order = $this->getCalculableContainerWithTwoItems();
        $result = $calculator->calculate(
            $discountCollection,
            $order,
            $settings,
            new Distributor(Locator::getInstance())
        );
        $this->assertEquals(2, count($result));
    }

    public function testFilterOutLowestUnprivilegedDiscountIfThereAreMoreThanTwo()
    {
        $discountCollection = [];
        $discountCollection[] = $this->initializeDiscount(
            'name 1',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            true
        );
        $discountCollection[] = $this->initializeDiscount(
            'name 2',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            false
        );
        $discountCollection[] = $this->initializeDiscount(
            'name 3',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            60,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            false
        );
        $discountCollection[] = $this->initializeDiscount(
            'name 4',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            70,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            false
        );

        $settings = new DiscountConfig(Config::getInstance(), Locator::getInstance());
        $calculator = $this->getCalculator();

        $order = $this->getCalculableContainerWithTwoItems();
        $result = $calculator->calculate(
            $discountCollection,
            $order,
            $settings,
            new Distributor(Locator::getInstance())
        );
        $this->assertEquals(2, count($result));
    }

    public function testFilterOutLowestUnprivilegedDiscountIfThereAreMoreThanTwoAndTwoPrivilegedOnes()
    {
        $discountCollection = [];
        $discountCollection[] = $this->initializeDiscount(
            'name 1',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            true
        );
        $discountCollection[] = $this->initializeDiscount(
            'name 2',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            50,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            true
        );
        $discountCollection[] = $this->initializeDiscount(
            'name 3',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            60,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            false
        );
        $discountCollection[] = $this->initializeDiscount(
            'name 4',
            DiscountConfig::PLUGIN_CALCULATOR_PERCENTAGE,
            70,
            true,
            DiscountConfig::PLUGIN_COLLECTOR_ITEM,
            false
        );

        $settings = new DiscountConfig(Config::getInstance(), Locator::getInstance());
        $calculator = $this->getCalculator();

        $order = $this->getCalculableContainerWithTwoItems();
        $result = $calculator->calculate($discountCollection, $order, $settings, new Distributor(Locator::getInstance()));
        $this->assertEquals(3, count($result));
    }

    /**
     * @param $displayName
     * @param $calculatorPlugin
     * @param $amount
     * @param $isActive
     * @param $collectorPlugin
     * @param bool $isPrivileged
     *
     * @return DiscountTransfer
     */
    protected function initializeDiscount(
        $displayName,
        $calculatorPlugin,
        $amount,
        $isActive,
        $collectorPlugin,
        $isPrivileged = true
    ) {
        $discountTransfer = new DiscountTransfer();
        $discountTransfer->setDisplayName($displayName);
        $discountTransfer->setAmount($amount);
        $discountTransfer->setIsActive($isActive);
        $discountTransfer->setCalculatorPlugin($calculatorPlugin);

        $discountCollectorTransfer = new DiscountCollectorTransfer();
        $discountCollectorTransfer->setCollectorPlugin($collectorPlugin);

        $discountTransfer->addDiscountCollectors($discountCollectorTransfer);
        $discountTransfer->setIsPrivileged($isPrivileged);

        return $discountTransfer;
    }

    /**
     * @return CalculableContainer
     */
    protected function getCalculableContainerWithTwoItems()
    {
        $order = new OrderTransfer();
        $item = new ItemTransfer();

        $item->setGrossPrice(self::ITEM_GROSS_PRICE_500);
        $order->addItem($item);
        $order->addItem(clone $item);

        return new CalculableContainer($order);
    }

    /**
     * @return Calculator
     */
    protected function getCalculator()
    {
        $locator = Locator::getInstance();
        $settings = new DiscountConfig(Config::getInstance(), $locator);
        $collectorResolver = new CollectorResolver($settings);

        $flashMessengerFacade = $locator->flashMessenger()->facade();
        $calculator = new Calculator($collectorResolver, $flashMessengerFacade);

        return $calculator;
    }

}
