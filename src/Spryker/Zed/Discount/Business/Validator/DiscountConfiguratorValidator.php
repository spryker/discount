<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Business\Validator;

use Generated\Shared\Transfer\DiscountConfiguratorResponseTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\ErrorCollectionTransfer;

class DiscountConfiguratorValidator implements DiscountConfiguratorAggregateValidatorInterface
{
    /**
     * @param array<\Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\DiscountConfiguratorValidatorRuleInterface> $discountConfiguratorValidatorRules
     * @param array<\Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountConfiguratorValidatorRulePluginInterface> $discountConfiguratorValidatorRulePlugins
     */
    public function __construct(
        protected DiscountConfiguratorValidatorInterface $discountConfiguratorPeriodValidator,
        protected DiscountConfiguratorValidatorInterface $discountConfiguratorDiscountExistsValidator,
        protected array $discountConfiguratorValidatorRules,
        protected array $discountConfiguratorValidatorRulePlugins
    ) {
    }

    public function validate(DiscountConfiguratorTransfer $discountConfiguratorTransfer): DiscountConfiguratorResponseTransfer
    {
        return $this->validateDiscountConfigurator(
            $discountConfiguratorTransfer,
            (new DiscountConfiguratorResponseTransfer())->setDiscountConfigurator($discountConfiguratorTransfer),
        );
    }

    public function validateDiscountConfigurator(
        DiscountConfiguratorTransfer $discountConfiguratorTransfer,
        DiscountConfiguratorResponseTransfer $discountConfiguratorResponseTransfer
    ): DiscountConfiguratorResponseTransfer {
        $discountConfiguratorResponseTransfer->setIsSuccessful(true);

        if ($discountConfiguratorTransfer->getDiscountGeneral()?->getIdDiscount() !== null) {
            $discountConfiguratorResponseTransfer = $this->discountConfiguratorDiscountExistsValidator->validateDiscountConfigurator(
                $discountConfiguratorTransfer,
                $discountConfiguratorResponseTransfer,
            );
        }

        $discountConfiguratorResponseTransfer = $this->discountConfiguratorPeriodValidator->validateDiscountConfigurator(
            $discountConfiguratorTransfer,
            $discountConfiguratorResponseTransfer,
        );

        foreach ($this->discountConfiguratorValidatorRules as $discountConfiguratorValidatorRule) {
            $discountConfiguratorResponseTransfer = $this->addErrors(
                $discountConfiguratorResponseTransfer,
                $discountConfiguratorValidatorRule->validate($discountConfiguratorTransfer),
            );
        }

        foreach ($this->discountConfiguratorValidatorRulePlugins as $discountConfiguratorValidatorRulePlugin) {
            $discountConfiguratorResponseTransfer = $this->addErrors(
                $discountConfiguratorResponseTransfer,
                $discountConfiguratorValidatorRulePlugin->validate($discountConfiguratorTransfer),
            );
        }

        return $discountConfiguratorResponseTransfer;
    }

    protected function addErrors(
        DiscountConfiguratorResponseTransfer $discountConfiguratorResponseTransfer,
        ErrorCollectionTransfer $errorCollectionTransfer
    ): DiscountConfiguratorResponseTransfer {
        if ($errorCollectionTransfer->getErrors()->count() === 0) {
            return $discountConfiguratorResponseTransfer;
        }

        foreach ($errorCollectionTransfer->getErrors() as $errorTransfer) {
            $discountConfiguratorResponseTransfer->addError($errorTransfer);
        }

        return $discountConfiguratorResponseTransfer->setIsSuccessful(false);
    }
}
