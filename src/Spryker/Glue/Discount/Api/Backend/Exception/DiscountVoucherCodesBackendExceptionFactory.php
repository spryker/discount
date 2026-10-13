<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Exception;

use Generated\Shared\Transfer\DiscountVoucherResponseTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Glue\Discount\DiscountConfig;
use Symfony\Component\HttpFoundation\Response;

class DiscountVoucherCodesBackendExceptionFactory extends AbstractBackendExceptionFactory implements DiscountVoucherCodesBackendExceptionFactoryInterface
{
    /**
     * @param array<int, string> $codes Missing codes keyed by their position in the submitted list.
     */
    public function createVoucherCodesNotFoundException(array $codes, string $discountUuid): GlueApiException
    {
        $errors = [];

        foreach ($codes as $index => $code) {
            $errors[] = [
                RestErrorMessageTransfer::CODE => DiscountConfig::RESPONSE_CODE_VOUCHER_CODE_NOT_FOUND,
                RestErrorMessageTransfer::STATUS => Response::HTTP_NOT_FOUND,
                RestErrorMessageTransfer::DETAIL => sprintf(
                    DiscountConfig::RESPONSE_DETAILS_ERROR,
                    DiscountConfig::ATTRIBUTE_CODES . '.' . $index,
                    sprintf(DiscountConfig::RESPONSE_DETAILS_VOUCHER_CODE_NOT_FOUND, $code, $discountUuid),
                ),
            ];
        }

        $glueApiException = new GlueApiException(
            Response::HTTP_NOT_FOUND,
            DiscountConfig::RESPONSE_CODE_VOUCHER_CODE_NOT_FOUND,
            $errors === []
                ? sprintf(DiscountConfig::RESPONSE_DETAILS_VOUCHER_CODE_NOT_FOUND, '', $discountUuid)
                : $errors[0][RestErrorMessageTransfer::DETAIL],
        );

        if (count($errors) > 1) {
            $glueApiException->setErrors($errors);
        }

        return $glueApiException;
    }

    public function createInvalidVoucherCodesDeleteRequestException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            DiscountConfig::RESPONSE_CODE_VOUCHER_CODES_DELETE_REQUEST_INVALID,
            DiscountConfig::RESPONSE_DETAILS_VOUCHER_CODES_DELETE_REQUEST_INVALID,
        );
    }

    public function createExceptionFromDiscountVoucherResponse(
        DiscountVoucherResponseTransfer $discountVoucherResponseTransfer
    ): GlueApiException {
        return $this->createValidationException(
            $this->mapErrorTransfersToErrors($discountVoucherResponseTransfer->getErrors()),
            DiscountConfig::RESPONSE_DETAILS_VOUCHER_CODE_VALIDATION,
        );
    }

    public function createVoucherCodeGenerationFailedException(string $message): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            DiscountConfig::RESPONSE_CODE_VOUCHER_CODE_GENERATION_FAILED,
            $message,
        );
    }
}
