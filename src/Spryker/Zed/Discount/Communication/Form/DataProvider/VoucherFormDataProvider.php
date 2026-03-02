<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Communication\Form\DataProvider;

use Generated\Shared\Transfer\DiscountVoucherTransfer;
use Spryker\Zed\Discount\Communication\Form\VoucherForm;
use Spryker\Zed\Discount\Dependency\Facade\DiscountToLocaleFacadeInterface;

class VoucherFormDataProvider extends BaseDiscountFormDataProvider
{
    /**
     * @var \Spryker\Zed\Discount\Dependency\Facade\DiscountToLocaleFacadeInterface
     */
    protected DiscountToLocaleFacadeInterface $localeFacade;

    public function __construct(DiscountToLocaleFacadeInterface $localeFacade)
    {
        $this->localeFacade = $localeFacade;
    }

    public function getData(?int $idDiscount = null): DiscountVoucherTransfer
    {
        $discountVoucherTransfer = new DiscountVoucherTransfer();
        $discountVoucherTransfer->setIdDiscount($idDiscount);
        $discountVoucherTransfer->setNumberOfUses(0);

        if ($discountVoucherTransfer->getMaxNumberOfUses() === null) {
            $discountVoucherTransfer->setMaxNumberOfUses(0);
        }

        return $discountVoucherTransfer;
    }

    /**
     * @return array<string, string>
     */
    public function getOptions(): array
    {
        return [
            VoucherForm::OPTION_LOCALE => $this->localeFacade->getCurrentLocaleName(),
        ];
    }
}
