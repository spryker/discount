<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Processor;

use Generated\Api\Backend\DiscountVoucherCodesGenerateBackendResource;
use Generated\Shared\Transfer\DiscountVoucherTransfer;
use Generated\Shared\Transfer\VoucherCreateInfoTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractBackendProcessor;
use Spryker\Glue\Discount\Api\Backend\Exception\DiscountVoucherCodesBackendExceptionFactoryInterface;
use Spryker\Glue\Discount\Api\Backend\Mapper\DiscountVoucherCodeResourceMapperInterface;
use Spryker\Glue\Discount\Api\Backend\Reader\DiscountConfiguratorReaderInterface;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;

class DiscountVoucherCodesGenerateBackendProcessor extends AbstractBackendProcessor
{
    protected const string URI_VARIABLE_DISCOUNT_UUID = 'discountUuid';

    public function __construct(
        protected DiscountFacadeInterface $discountFacade,
        protected DiscountVoucherCodeResourceMapperInterface $discountVoucherCodeResourceMapper,
        protected DiscountConfiguratorReaderInterface $discountConfiguratorReader,
        protected DiscountVoucherCodesBackendExceptionFactoryInterface $exceptionFactory
    ) {
    }

    protected function processPost(mixed $data): object
    {
        return $this->generateVoucherCodes($data, (string)$this->getUriVariable(static::URI_VARIABLE_DISCOUNT_UUID));
    }

    protected function generateVoucherCodes(
        DiscountVoucherCodesGenerateBackendResource $discountVoucherCodesGenerateBackendResource,
        string $discountUuid
    ): DiscountVoucherCodesGenerateBackendResource {
        $idDiscount = $this->discountConfiguratorReader
            ->getDiscountConfiguratorByUuid($discountUuid)
            ->getDiscountGeneralOrFail()
            ->getIdDiscountOrFail();

        $discountVoucherTransfer = $this->discountVoucherCodeResourceMapper
            ->mapDiscountVoucherCodesGenerateBackendResourceToDiscountVoucherTransfer(
                $discountVoucherCodesGenerateBackendResource,
                (new DiscountVoucherTransfer())->setIdDiscount($idDiscount),
            );

        $this->assertDiscountVoucherIsValid($discountVoucherTransfer);

        $voucherCreateInfoTransfer = $this->saveVoucherCodes($discountVoucherTransfer);

        $discountVoucherCodesGenerateBackendResource = $this->discountVoucherCodeResourceMapper
            ->mapDiscountVoucherTransferToDiscountVoucherCodesGenerateBackendResource(
                $discountVoucherTransfer,
                $discountUuid,
                new DiscountVoucherCodesGenerateBackendResource(),
            );

        return $this->discountVoucherCodeResourceMapper
            ->mapVoucherCreateInfoTransferToDiscountVoucherCodesGenerateBackendResource(
                $voucherCreateInfoTransfer,
                $discountVoucherCodesGenerateBackendResource,
            );
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function assertDiscountVoucherIsValid(DiscountVoucherTransfer $discountVoucherTransfer): void
    {
        $discountVoucherResponseTransfer = $this->discountFacade->validateDiscountVoucher($discountVoucherTransfer);

        if ($discountVoucherResponseTransfer->getIsSuccessful() === true) {
            return;
        }

        throw $this->exceptionFactory->createExceptionFromDiscountVoucherResponse($discountVoucherResponseTransfer);
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function saveVoucherCodes(DiscountVoucherTransfer $discountVoucherTransfer): VoucherCreateInfoTransfer
    {
        $voucherCreateInfoTransfer = $this->discountFacade->saveVoucherCodes($discountVoucherTransfer);

        if ($voucherCreateInfoTransfer->getType() !== DiscountConstants::MESSAGE_TYPE_SUCCESS) {
            throw $this->exceptionFactory->createVoucherCodeGenerationFailedException(
                (string)$voucherCreateInfoTransfer->getMessage(),
            );
        }

        return $voucherCreateInfoTransfer;
    }
}
