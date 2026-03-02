<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\Mapper;

use Generated\Shared\Transfer\DiscountCalculatorTransfer;
use Generated\Shared\Transfer\DiscountConditionTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountGeneralTransfer;
use Generated\Shared\Transfer\DiscountMoneyAmountTransfer;
use Generated\Shared\Transfer\DiscountTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;

class DiscountMapper implements DiscountMapperInterface
{
    public function mapDiscountConfiguratorTransferToDiscountTransfer(
        DiscountConfiguratorTransfer $discountConfiguratorTransfer,
        DiscountTransfer $discountTransfer
    ): DiscountTransfer {
        $discountGeneralTransfer = $discountConfiguratorTransfer->getDiscountGeneralOrFail();
        $discountCalculatorTransfer = $discountConfiguratorTransfer->getDiscountCalculatorOrFail();
        $discountConditionTransfer = $discountConfiguratorTransfer->getDiscountConditionOrFail();

        $discountTransfer->fromArray($discountGeneralTransfer->toArray(), true);
        $discountTransfer->fromArray($discountCalculatorTransfer->toArray(), true);
        $discountTransfer->fromArray($discountConditionTransfer->toArray(), true);

        return $discountTransfer;
    }

    public function mapDiscountTransferToDiscountConfiguratorTransfer(
        DiscountTransfer $discountTransfer,
        DiscountConfiguratorTransfer $discountConfiguratorTransfer
    ): DiscountConfiguratorTransfer {
        $discountGeneralTransfer = $discountConfiguratorTransfer->getDiscountGeneral() ?? new DiscountGeneralTransfer();
        $discountCalculatorTransfer = $discountConfiguratorTransfer->getDiscountCalculator() ?? new DiscountCalculatorTransfer();
        $discountConditionTransfer = $discountConfiguratorTransfer->getDiscountCondition() ?? new DiscountConditionTransfer();

        $discountGeneralTransfer->fromArray($discountTransfer->toArray(), true);
        $discountCalculatorTransfer->fromArray($discountTransfer->toArray(), true);
        $discountConditionTransfer->fromArray($discountTransfer->toArray(), true);

        return $discountConfiguratorTransfer
            ->setDiscountGeneral($discountGeneralTransfer)
            ->setDiscountCalculator($discountCalculatorTransfer)
            ->setDiscountCondition($discountConditionTransfer);
    }

    public function mapMoneyValueTransferToDiscountMoneyAmountTransfer(
        MoneyValueTransfer $moneyValueTransfer,
        DiscountMoneyAmountTransfer $discountMoneyAmountTransfer
    ): DiscountMoneyAmountTransfer {
        return $discountMoneyAmountTransfer
            ->fromArray($moneyValueTransfer->toArray(), true)
            ->setIdDiscountAmount($moneyValueTransfer->getIdEntity());
    }

    public function mapDiscountMoneyAmountTransferToMoneyValueTransfer(
        DiscountMoneyAmountTransfer $discountMoneyAmountTransfer,
        MoneyValueTransfer $moneyValueTransfer
    ): MoneyValueTransfer {
        return $moneyValueTransfer
            ->fromArray($discountMoneyAmountTransfer->toArray(), true)
            ->setIdEntity($discountMoneyAmountTransfer->getIdDiscountAmount());
    }
}
