<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\CartCode;

use Generated\Shared\Transfer\QuoteTransfer;

class VoucherCartCodeRemover implements VoucherCartCodeRemoverInterface
{
    public function removeCartCode(QuoteTransfer $quoteTransfer, string $cartCode): QuoteTransfer
    {
        $voucherDiscountsIterator = $quoteTransfer->getVoucherDiscounts()->getIterator();
        foreach ($quoteTransfer->getVoucherDiscounts() as $key => $voucherDiscountTransfer) {
            if ($voucherDiscountTransfer->getVoucherCode() === $cartCode) {
                $voucherDiscountsIterator->offsetUnset($key);
            }

            if (!$voucherDiscountsIterator->valid()) {
                break;
            }
        }

        $usedNotAppliedVoucherCodeResultList = array_filter(
            $quoteTransfer->getUsedNotAppliedVoucherCodes(),
            function (string $usedNotAppliedVoucherCode) use ($cartCode) {
                return $usedNotAppliedVoucherCode != $cartCode;
            },
        );

        return $quoteTransfer->setUsedNotAppliedVoucherCodes($usedNotAppliedVoucherCodeResultList);
    }
}
