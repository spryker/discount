<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Presentation;

use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use SprykerTest\Zed\Discount\DiscountPresentationTester;
use SprykerTest\Zed\Discount\PageObject\DiscountEditPage;
use SprykerTest\Zed\Discount\PageObject\DiscountListPage;
use SprykerTest\Zed\Discount\PageObject\DiscountViewPage;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Presentation
 * @group DiscountListCest
 * Add your own group annotations below this line
 */
class DiscountListCest
{
    /**
     * @var string
     */
    protected const CURRENCY_CODE = 'EUR';

    public function _before(DiscountPresentationTester $i): void
    {
        $i->amZed();
        $i->amLoggedInUser();
    }

    public function showADiscountInList(DiscountPresentationTester $i, DiscountEditPage $editPage): void
    {
        $name = 'Works as test discount' . uniqid();
        $currencyTransfer = $i->haveCurrencyTransfer([CurrencyTransfer::CODE => static::CURRENCY_CODE]);
        $discount = $i->haveDiscount(['displayName' => $name], [
            [
                MoneyValueTransfer::NET_AMOUNT => 100,
                MoneyValueTransfer::GROSS_AMOUNT => 100,
                MoneyValueTransfer::FK_CURRENCY => $currencyTransfer->getIdCurrency(),
                MoneyValueTransfer::CURRENCY => $currencyTransfer,
            ],
        ]);

        $i->amOnPage(DiscountListPage::URL);
        $i->waitForElement(DiscountListPage::DATA_TABLE_ROW, 30);

        $discountRow = sprintf('//td[contains(., "%s")]/parent::tr', $name);
        $i->waitForElementVisible($discountRow, 30);
        $i->see('Edit', $discountRow);
        $i->see('View', $discountRow);
        $i->see('Deactivate', $discountRow);

        $i->amGoingTo('open edit page for discount');
        $editUrl = $editPage->url($discount->getIdDiscount());
        $editLink = sprintf('//a[contains(@href, "%s")]', $editUrl);
        $i->click($editLink);
        $i->seeInCurrentUrl($editUrl);
        $i->see('Edit discount', 'h2');
    }

    public function openDiscountViewPage(DiscountPresentationTester $i, DiscountViewPage $viewPage): void
    {
        $name = 'Works as test discount' . uniqid();
        $currencyTransfer = $i->haveCurrencyTransfer([CurrencyTransfer::CODE => static::CURRENCY_CODE]);
        $discount = $i->haveDiscount(['displayName' => $name], [
            [
                MoneyValueTransfer::NET_AMOUNT => 100,
                MoneyValueTransfer::GROSS_AMOUNT => 100,
                MoneyValueTransfer::FK_CURRENCY => $currencyTransfer->getIdCurrency(),
                MoneyValueTransfer::CURRENCY => $currencyTransfer,
            ],
        ]);

        $i->amOnPage(DiscountListPage::URL);
        $i->waitForElement(DiscountListPage::DATA_TABLE_ROW, 30);

        $discountRow = sprintf('//td[contains(., "%s")]/parent::tr', $name);
        $i->waitForElementVisible($discountRow, 30);

        $i->amGoingTo('open view page for discount');
        $viewUrl = $viewPage->url($discount->getIdDiscount());
        $viewLink = sprintf('//a[contains(@href, "%s")]', $viewUrl);
        $i->click($viewLink);
        $i->seeInCurrentUrl($viewUrl);
        $i->see('View discount', 'h2');
        $i->see($name);
    }

    public function testPageShouldShowList(DiscountPresentationTester $i): void
    {
        $i->amOnPage(DiscountListPage::URL);
        $i->seeElement(DiscountListPage::SELECTOR_DATA_TABLE);
    }
}
