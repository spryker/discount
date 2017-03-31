<?php

/**
 * This file is part of the Spryker Demoshop.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace ZedPresentation;

use Discount\PageObject\DiscountCreatePage;
use Discount\ZedPresentationTester;

/**
 * Auto-generated group annotations
 * @group Discount
 * @group ZedPresentation
 * @group DiscountCreateCest
 * Add your own group annotations below this line
 */
class DiscountCreateCest
{

    /**
     * @param \Discount\ZedPresentationTester $i
     * @param \Discount\PageObject\DiscountCreatePage $createPage
     *
     * @return void
     */
    public function createExclusiveDiscount(ZedPresentationTester $i, DiscountCreatePage $createPage)
    {
        $createPage->createDiscount(DiscountCreatePage::DISCOUNT_VALID_EXCLUSIVE);
        $i->see($createPage::MESSAGE_SUCCESSFUL_ALERT_CREATION);
    }

    /**
     * @param \Discount\ZedPresentationTester $i
     * @param \Discount\PageObject\DiscountCreatePage $createPage
     *
     * @return void
     */
    public function createNotExclusiveDiscount(ZedPresentationTester $i, DiscountCreatePage $createPage)
    {
        $createPage->createDiscount(DiscountCreatePage::DISCOUNT_VALID_NOT_EXCLUSIVE);
        $i->see($createPage::MESSAGE_SUCCESSFUL_ALERT_CREATION);
    }

    /**
     * @param \Discount\ZedPresentationTester $i
     * @param \Discount\PageObject\DiscountCreatePage $createPage
     *
     * @return void
     */
    public function createInvalidDiscount(ZedPresentationTester $i, DiscountCreatePage $createPage)
    {
        $createPage->createDiscount(DiscountCreatePage::EMPTY_DISCOUNT, ['name' => null]);
        $i->dontSee($createPage::MESSAGE_SUCCESSFUL_ALERT_CREATION);
        $i->seeInCurrentUrl($createPage::URL);
        $createPage->tab('General information');
        $i->seeElement($createPage::CURRENT_TAB_ERROR);
        $i->see('This value should not be blank');
        $i->see('Name', '.has-error');
        $createPage->tab('Discount calculation');
        $i->seeElement($createPage::CURRENT_TAB_ERROR);
        $i->see('This value should not be blank');
    }

    /**
     * @param \Discount\ZedPresentationTester $i
     * @param \Discount\PageObject\DiscountCreatePage $createPage
     *
     * @return void
     */
    public function simpleDiscountComputation(ZedPresentationTester $i, DiscountCreatePage $createPage)
    {
        $createPage->open()->tab('Discount calculation');
        $createPage->fillInDiscountRule(0, 'item-price', 'equal', '12');
        $createPage->assertDiscountQuery("item-price = '12'");
        $i->click('Add rule');
        $createPage->fillInDiscountRule(1, 'item-quantity', 'greater', '2');
        $createPage->assertDiscountQuery("item-price = '12' AND item-quantity > '2'");
        $i->click('Delete', '#builder_calculation_rule_0');
        $createPage->assertDiscountQuery("item-quantity > '2'");
        $i->click('Add rule');
        $createPage->fillInDiscountRule(1, 'attribute.width', 'less or equal', '500');
        $createPage->changeDiscountGroupOperator('OR');
        $createPage->assertDiscountQuery("item-quantity > '2' OR attribute.width <= '500'");
    }

}
