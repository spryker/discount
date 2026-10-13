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

class DiscountVoucherCodeResourceMapper implements DiscountVoucherCodeResourceMapperInterface
{
    public function mapDiscountVoucherTransferToDiscountVoucherCodesBackendResource(
        DiscountVoucherTransfer $discountVoucherTransfer,
        string $discountUuid,
        DiscountVoucherCodesBackendResource $discountVoucherCodesBackendResource
    ): DiscountVoucherCodesBackendResource {
        $discountVoucherCodesBackendResource->discountUuid = $discountUuid;
        $discountVoucherCodesBackendResource->code = $discountVoucherTransfer->getCode();
        $discountVoucherCodesBackendResource->maxNumberOfUses = (int)$discountVoucherTransfer->getMaxNumberOfUses();
        $discountVoucherCodesBackendResource->numberOfUses = (int)$discountVoucherTransfer->getNumberOfUses();
        $discountVoucherCodesBackendResource->isActive = $discountVoucherTransfer->getIsActive();
        $discountVoucherCodesBackendResource->voucherBatch = $discountVoucherTransfer->getVoucherBatch();
        $discountVoucherCodesBackendResource->createdAt = $discountVoucherTransfer->getCreatedAt();

        return $discountVoucherCodesBackendResource;
    }

    public function mapDiscountVoucherCodesGenerateBackendResourceToDiscountVoucherTransfer(
        DiscountVoucherCodesGenerateBackendResource $discountVoucherCodesGenerateBackendResource,
        DiscountVoucherTransfer $discountVoucherTransfer
    ): DiscountVoucherTransfer {
        return $discountVoucherTransfer
            ->setQuantity($discountVoucherCodesGenerateBackendResource->quantity)
            ->setRandomGeneratedCodeLength($discountVoucherCodesGenerateBackendResource->codeLength)
            ->setCustomCode($this->trimCustomCode($discountVoucherCodesGenerateBackendResource->customCode))
            ->setMaxNumberOfUses($discountVoucherCodesGenerateBackendResource->maxNumberOfUses);
    }

    protected function trimCustomCode(?string $customCode): ?string
    {
        $customCode = trim((string)$customCode);

        return $customCode === '' ? null : $customCode;
    }

    public function mapDiscountVoucherTransferToDiscountVoucherCodesGenerateBackendResource(
        DiscountVoucherTransfer $discountVoucherTransfer,
        string $discountUuid,
        DiscountVoucherCodesGenerateBackendResource $discountVoucherCodesGenerateBackendResource
    ): DiscountVoucherCodesGenerateBackendResource {
        $discountVoucherCodesGenerateBackendResource->discountUuid = $discountUuid;
        $discountVoucherCodesGenerateBackendResource->voucherBatch = $discountVoucherTransfer->getVoucherBatch();
        $discountVoucherCodesGenerateBackendResource->generatedCount = $discountVoucherTransfer->getQuantity();

        return $discountVoucherCodesGenerateBackendResource;
    }

    public function mapVoucherCreateInfoTransferToDiscountVoucherCodesGenerateBackendResource(
        VoucherCreateInfoTransfer $voucherCreateInfoTransfer,
        DiscountVoucherCodesGenerateBackendResource $discountVoucherCodesGenerateBackendResource
    ): DiscountVoucherCodesGenerateBackendResource {
        $discountVoucherCodesGenerateBackendResource->codes = array_values($voucherCreateInfoTransfer->getCodes());
        $discountVoucherCodesGenerateBackendResource->generatedCount = count($voucherCreateInfoTransfer->getCodes());

        return $discountVoucherCodesGenerateBackendResource;
    }
}
