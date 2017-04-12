<?php

namespace ZedPresentation\Discount\Zed;

use Codeception\Util\Locator;
use Discount\PageObject\DiscountEditPage;
use Discount\PageObject\DiscountListPage;
use Discount\PageObject\DiscountViewPage;
use Discount\ZedPresentationTester;

/**
 * Auto-generated group annotations
 * @group Discount
 * @group ZedPresentation
 * @group Discount
 * @group Zed
 * @group DiscountListCest
 * Add your own group annotations below this line
 */
class DiscountListCest
{

    /**
     * @param \Discount\ZedPresentationTester $i
     * @param \Discount\PageObject\DiscountEditPage $editPage
     * @param \Discount\PageObject\DiscountViewPage $viewPage
     *
     * @return void
     */
    public function showADiscountInList(ZedPresentationTester $i, DiscountEditPage $editPage, DiscountViewPage $viewPage)
    {
        $name = 'Works as test discount';
        $discount = $i->haveDiscount(['displayName' => $name]);
        $i->amOnPage(DiscountListPage::URL);

        $firstTableRow = Locator::firstElement(DiscountListPage::DATA_TABLE_ROW);
        $i->waitForElementVisible($firstTableRow, 3);
        $i->see($name, $firstTableRow);
        $i->see('Edit', $firstTableRow);
        $i->see('View', $firstTableRow);
        $i->see('Deactivate', $firstTableRow);
        $i->amGoingTo('open edit page for discount');
        $i->click('Edit', $firstTableRow);
        $i->seeInCurrentUrl($editPage->url($discount->getIdDiscount()));
        $i->see('Edit discount', 'h2');
        $i->amGoingTo('open view page for discount');
        $i->amOnPage(DiscountListPage::URL);
        $i->waitForElementVisible($firstTableRow);
        $i->click('View', $firstTableRow);
        $i->seeInCurrentUrl($viewPage->url($discount->getIdDiscount()));
        $i->see('View discount', 'h2');
        $i->see($name);
    }

    /**
     * @param \Discount\ZedPresentationTester $i
     *
     * @return void
     */
    public function testPageShouldShowList(ZedPresentationTester $i)
    {
        $i->amOnPage(DiscountListPage::URL);
        $i->seeElement(DiscountListPage::SELECTOR_DATA_TABLE);
    }

}