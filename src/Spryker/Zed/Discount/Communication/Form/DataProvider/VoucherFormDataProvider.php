<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Communication\Form\DataProvider;

use Generated\Shared\Transfer\DiscountVoucherTransfer;

class VoucherFormDataProvider
{

    /**
     * @param int $idDiscount
     * @return \Generated\Shared\Transfer\DiscountVoucherTransfer
     */
    public function getData($idDiscount)
    {
        $discountVoucherTransfer = new DiscountVoucherTransfer();
        $discountVoucherTransfer->setIdDiscount($idDiscount);

        return $discountVoucherTransfer;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return [];
    }

}
