<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Exception;

use Generated\Shared\Transfer\DiscountVoucherResponseTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;

interface DiscountVoucherCodesBackendExceptionFactoryInterface
{
    /**
     * @param array<int, string> $codes Missing codes keyed by their position in the submitted list.
     */
    public function createVoucherCodesNotFoundException(array $codes, string $discountUuid): GlueApiException;

    public function createInvalidVoucherCodesDeleteRequestException(): GlueApiException;

    public function createExceptionFromDiscountVoucherResponse(
        DiscountVoucherResponseTransfer $discountVoucherResponseTransfer
    ): GlueApiException;

    public function createVoucherCodeGenerationFailedException(string $message): GlueApiException;

    public function createSortFieldNotSupportedException(string $detail): GlueApiException;
}
