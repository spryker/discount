<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\CartCode;

use Generated\Shared\Transfer\QuoteTransfer;

interface VoucherCartCodeAdderInterface
{
    public function addCartCode(QuoteTransfer $quoteTransfer, string $cartCode): QuoteTransfer;
}
