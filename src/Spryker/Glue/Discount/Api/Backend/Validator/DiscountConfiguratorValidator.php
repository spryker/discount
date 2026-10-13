<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Validator;

use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Spryker\Glue\Discount\Api\Backend\Exception\DiscountsBackendExceptionFactoryInterface;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;

class DiscountConfiguratorValidator implements DiscountConfiguratorValidatorInterface
{
    public function __construct(
        protected DiscountFacadeInterface $discountFacade,
        protected DiscountsBackendExceptionFactoryInterface $exceptionFactory
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    public function assertIsValid(DiscountConfiguratorTransfer $discountConfiguratorTransfer): void
    {
        $discountConfiguratorResponseTransfer = $this->discountFacade->validateDiscountConfigurator($discountConfiguratorTransfer);

        if ($discountConfiguratorResponseTransfer->getIsSuccessful() === true) {
            return;
        }

        throw $this->exceptionFactory->createExceptionFromDiscountConfiguratorResponse($discountConfiguratorResponseTransfer);
    }
}
