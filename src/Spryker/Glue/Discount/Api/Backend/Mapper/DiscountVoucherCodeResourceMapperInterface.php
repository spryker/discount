<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Mapper;

use Generated\Api\Backend\DiscountVoucherCodesBackendResource;
use Generated\Api\Backend\DiscountVoucherCodesGenerateBackendResource;
use Generated\Shared\Transfer\DiscountVoucherTransfer;
use Generated\Shared\Transfer\VoucherCreateInfoTransfer;

interface DiscountVoucherCodeResourceMapperInterface
{
    public function mapDiscountVoucherTransferToDiscountVoucherCodesBackendResource(
        DiscountVoucherTransfer $discountVoucherTransfer,
        string $discountUuid,
        DiscountVoucherCodesBackendResource $discountVoucherCodesBackendResource
    ): DiscountVoucherCodesBackendResource;

    public function mapDiscountVoucherCodesGenerateBackendResourceToDiscountVoucherTransfer(
        DiscountVoucherCodesGenerateBackendResource $discountVoucherCodesGenerateBackendResource,
        DiscountVoucherTransfer $discountVoucherTransfer
    ): DiscountVoucherTransfer;

    public function mapDiscountVoucherTransferToDiscountVoucherCodesGenerateBackendResource(
        DiscountVoucherTransfer $discountVoucherTransfer,
        string $discountUuid,
        DiscountVoucherCodesGenerateBackendResource $discountVoucherCodesGenerateBackendResource
    ): DiscountVoucherCodesGenerateBackendResource;

    public function mapVoucherCreateInfoTransferToDiscountVoucherCodesGenerateBackendResource(
        VoucherCreateInfoTransfer $voucherCreateInfoTransfer,
        DiscountVoucherCodesGenerateBackendResource $discountVoucherCodesGenerateBackendResource
    ): DiscountVoucherCodesGenerateBackendResource;
}
