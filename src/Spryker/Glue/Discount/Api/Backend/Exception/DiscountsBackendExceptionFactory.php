<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Exception;

use Generated\Shared\Transfer\DiscountConfiguratorResponseTransfer;
use Generated\Shared\Transfer\DiscountRuleGroupResponseTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Glue\Discount\DiscountConfig;
use Symfony\Component\HttpFoundation\Response;

class DiscountsBackendExceptionFactory extends AbstractBackendExceptionFactory implements DiscountsBackendExceptionFactoryInterface
{
    public function createDiscountNotFoundException(string $uuid): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_NOT_FOUND,
            DiscountConfig::RESPONSE_CODE_DISCOUNT_NOT_FOUND,
            sprintf(DiscountConfig::RESPONSE_DETAILS_DISCOUNT_NOT_FOUND, $uuid),
        );
    }

    public function createExceptionFromDiscountConfiguratorResponse(
        DiscountConfiguratorResponseTransfer $discountConfiguratorResponseTransfer
    ): GlueApiException {
        return $this->createValidationException(
            array_merge(
                $this->mapErrorTransfersToErrors($discountConfiguratorResponseTransfer->getErrors()),
                $this->errorResourceMapper->mapMessageTransfersToErrors($discountConfiguratorResponseTransfer->getMessages()),
            ),
            DiscountConfig::RESPONSE_DETAILS_VALIDATION,
        );
    }

    public function createExceptionFromDiscountRuleGroupResponse(
        DiscountRuleGroupResponseTransfer $discountRuleGroupResponseTransfer
    ): GlueApiException {
        return $this->createValidationException(
            $this->mapErrorTransfersToErrors($discountRuleGroupResponseTransfer->getErrors()),
            DiscountConfig::RESPONSE_DETAILS_VALIDATION,
        );
    }
}
