<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Business\Calculator;

use Generated\Shared\Transfer\DiscountCalculatorTransfer;

interface DiscountAmountTransformerInterface
{
    /**
     * @throws \Spryker\Zed\Discount\Business\Exception\CalculatorException
     */
    public function transformAmountFromPersistence(DiscountCalculatorTransfer $discountCalculatorTransfer): DiscountCalculatorTransfer;

    /**
     * @throws \Spryker\Zed\Discount\Business\Exception\CalculatorException
     */
    public function transformAmountForPersistence(DiscountCalculatorTransfer $discountCalculatorTransfer): DiscountCalculatorTransfer;
}
