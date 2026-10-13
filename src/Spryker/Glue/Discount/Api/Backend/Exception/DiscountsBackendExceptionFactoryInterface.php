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

interface DiscountsBackendExceptionFactoryInterface
{
    public function createDiscountNotFoundException(string $uuid): GlueApiException;

    public function createExceptionFromDiscountConfiguratorResponse(
        DiscountConfiguratorResponseTransfer $discountConfiguratorResponseTransfer
    ): GlueApiException;

    public function createExceptionFromDiscountRuleGroupResponse(
        DiscountRuleGroupResponseTransfer $discountRuleGroupResponseTransfer
    ): GlueApiException;

    public function createSortFieldNotSupportedException(string $detail): GlueApiException;
}
