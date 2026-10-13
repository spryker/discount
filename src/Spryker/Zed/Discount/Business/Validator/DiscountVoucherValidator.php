<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Business\Validator;

use Generated\Shared\Transfer\DiscountVoucherResponseTransfer;
use Generated\Shared\Transfer\DiscountVoucherTransfer;
use Generated\Shared\Transfer\ErrorCollectionTransfer;

class DiscountVoucherValidator implements DiscountVoucherValidatorInterface
{
    /**
     * @param array<\Spryker\Zed\Discount\Business\Validator\Rule\DiscountVoucher\DiscountVoucherValidatorRuleInterface> $discountVoucherValidatorRules
     * @param array<\Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountVoucherValidatorRulePluginInterface> $discountVoucherValidatorRulePlugins
     */
    public function __construct(
        protected array $discountVoucherValidatorRules,
        protected array $discountVoucherValidatorRulePlugins
    ) {
    }

    public function validateDiscountVoucher(DiscountVoucherTransfer $discountVoucherTransfer): DiscountVoucherResponseTransfer
    {
        $discountVoucherResponseTransfer = (new DiscountVoucherResponseTransfer())
            ->setDiscountVoucher($discountVoucherTransfer)
            ->setIsSuccessful(true);

        foreach ($this->discountVoucherValidatorRules as $discountVoucherValidatorRule) {
            $discountVoucherResponseTransfer = $this->addErrors(
                $discountVoucherResponseTransfer,
                $discountVoucherValidatorRule->validate($discountVoucherTransfer),
            );
        }

        foreach ($this->discountVoucherValidatorRulePlugins as $discountVoucherValidatorRulePlugin) {
            $discountVoucherResponseTransfer = $this->addErrors(
                $discountVoucherResponseTransfer,
                $discountVoucherValidatorRulePlugin->validate($discountVoucherTransfer),
            );
        }

        return $discountVoucherResponseTransfer;
    }

    protected function addErrors(
        DiscountVoucherResponseTransfer $discountVoucherResponseTransfer,
        ErrorCollectionTransfer $errorCollectionTransfer
    ): DiscountVoucherResponseTransfer {
        if ($errorCollectionTransfer->getErrors()->count() === 0) {
            return $discountVoucherResponseTransfer;
        }

        foreach ($errorCollectionTransfer->getErrors() as $errorTransfer) {
            $discountVoucherResponseTransfer->addError($errorTransfer);
        }

        return $discountVoucherResponseTransfer->setIsSuccessful(false);
    }
}
