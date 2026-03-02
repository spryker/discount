<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\Mapper;

use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountMoneyAmountTransfer;
use Generated\Shared\Transfer\DiscountTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;

interface DiscountMapperInterface
{
    public function mapDiscountConfiguratorTransferToDiscountTransfer(
        DiscountConfiguratorTransfer $discountConfiguratorTransfer,
        DiscountTransfer $discountTransfer
    ): DiscountTransfer;

    public function mapDiscountTransferToDiscountConfiguratorTransfer(
        DiscountTransfer $discountTransfer,
        DiscountConfiguratorTransfer $discountConfiguratorTransfer
    ): DiscountConfiguratorTransfer;

    public function mapMoneyValueTransferToDiscountMoneyAmountTransfer(
        MoneyValueTransfer $moneyValueTransfer,
        DiscountMoneyAmountTransfer $discountMoneyAmountTransfer
    ): DiscountMoneyAmountTransfer;

    public function mapDiscountMoneyAmountTransferToMoneyValueTransfer(
        DiscountMoneyAmountTransfer $discountMoneyAmountTransfer,
        MoneyValueTransfer $moneyValueTransfer
    ): MoneyValueTransfer;
}
