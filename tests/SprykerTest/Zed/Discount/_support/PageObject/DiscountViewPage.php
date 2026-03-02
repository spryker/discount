<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\PageObject;

use SprykerTest\Zed\Discount\DiscountPresentationTester;

class DiscountViewPage
{
    /**
     * @var string
     */
    public const URL = '/discount/index/view';

    /**
     * @var \SprykerTest\Zed\Discount\DiscountPresentationTester|\SprykerTest\Zed\Discount\PageObject\DiscountPresentationTester
     */
    protected $tester;

    /**
     * @var \SprykerTest\Zed\Discount\PageObject\DiscountCreatePage
     */
    protected $createPage;

    public function __construct(DiscountPresentationTester $i)
    {
        $this->tester = $i;
    }

    public function open(string $identifier): void
    {
        $this->tester->amOnPage($this->url($identifier));
    }

    public function url(string $identifier): string
    {
        return static::URL . "?id-discount=$identifier";
    }
}
