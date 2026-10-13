<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Exception;

use ArrayObject;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Glue\Discount\Api\Backend\Mapper\ErrorResourceMapperInterface;
use Spryker\Glue\Discount\DiscountConfig;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractBackendExceptionFactory
{
    public function __construct(
        protected DiscountConfig $discountConfig,
        protected ErrorResourceMapperInterface $errorResourceMapper
    ) {
    }

    public function createSortFieldNotSupportedException(string $detail): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            DiscountConfig::RESPONSE_CODE_INVALID_SORT_FIELD,
            $detail,
        );
    }

    /**
     * @param array<int, array{code: string, status: int, detail: string}> $errors
     */
    protected function createValidationException(array $errors, string $defaultDetail): GlueApiException
    {
        $glueApiException = new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $errors === [] ? DiscountConfig::RESPONSE_CODE_VALIDATION : $errors[0][RestErrorMessageTransfer::CODE],
            $errors === [] ? $defaultDetail : $errors[0][RestErrorMessageTransfer::DETAIL],
        );

        if (count($errors) > 1) {
            $glueApiException->setErrors($errors);
        }

        return $glueApiException;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ErrorTransfer> $errorTransfers
     *
     * @return array<int, array{code: string, status: int, detail: string}>
     */
    protected function mapErrorTransfersToErrors(
        ArrayObject $errorTransfers
    ): array {
        return $this->errorResourceMapper->mapErrorTransfersToErrors(
            $errorTransfers,
            $this->discountConfig->getResponseCodeByErrorMessage(),
        );
    }
}
