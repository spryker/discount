<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Mapper;

use ArrayObject;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\Glue\Discount\DiscountConfig;
use Symfony\Component\HttpFoundation\Response;

class ErrorResourceMapper implements ErrorResourceMapperInterface
{
    protected const string ENTITY_IDENTIFIER_SEPARATOR = '.';

    public function __construct(protected DiscountConfig $discountConfig)
    {
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ErrorTransfer> $errorTransfers
     * @param array<string, string> $responseCodeByErrorMessage
     *
     * @return array<int, array{code: string, status: int, detail: string}>
     */
    public function mapErrorTransfersToErrors(
        ArrayObject $errorTransfers,
        array $responseCodeByErrorMessage
    ): array {
        $errors = [];

        foreach ($errorTransfers as $errorTransfer) {
            $translationKey = (string)$errorTransfer->getMessage();

            $errors[] = [
                RestErrorMessageTransfer::CODE => $responseCodeByErrorMessage[$translationKey] ?? DiscountConfig::RESPONSE_CODE_VALIDATION,
                RestErrorMessageTransfer::STATUS => Response::HTTP_UNPROCESSABLE_ENTITY,
                RestErrorMessageTransfer::DETAIL => sprintf(
                    DiscountConfig::RESPONSE_DETAILS_ERROR,
                    $this->mapEntityIdentifierToAttribute((string)$errorTransfer->getEntityIdentifier()),
                    strtr($translationKey, $this->stringifyParameters($errorTransfer->getParameters())),
                ),
            ];
        }

        return $errors;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\MessageTransfer> $messageTransfers
     *
     * @return array<int, array{code: string, status: int, detail: string}>
     */
    public function mapMessageTransfersToErrors(
        ArrayObject $messageTransfers
    ): array {
        $errors = [];

        foreach ($messageTransfers as $messageTransfer) {
            $errors[] = [
                RestErrorMessageTransfer::CODE => DiscountConfig::RESPONSE_CODE_VALIDATION,
                RestErrorMessageTransfer::STATUS => Response::HTTP_UNPROCESSABLE_ENTITY,
                RestErrorMessageTransfer::DETAIL => strtr(
                    (string)$messageTransfer->getValue(),
                    $this->stringifyParameters($messageTransfer->getParameters()),
                ),
            ];
        }

        return $errors;
    }

    protected function mapEntityIdentifierToAttribute(string $entityIdentifier): string
    {
        $attributeByEntityIdentifier = $this->discountConfig->getAttributeByEntityIdentifier();

        if (isset($attributeByEntityIdentifier[$entityIdentifier])) {
            return $attributeByEntityIdentifier[$entityIdentifier];
        }

        $separatorPosition = strrpos($entityIdentifier, static::ENTITY_IDENTIFIER_SEPARATOR);

        if ($separatorPosition === false) {
            return $entityIdentifier;
        }

        $parentEntityIdentifier = substr($entityIdentifier, 0, $separatorPosition);
        $index = substr($entityIdentifier, $separatorPosition + 1);

        if (!ctype_digit($index) || !isset($attributeByEntityIdentifier[$parentEntityIdentifier])) {
            return $entityIdentifier;
        }

        return $attributeByEntityIdentifier[$parentEntityIdentifier] . static::ENTITY_IDENTIFIER_SEPARATOR . $index;
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<string, string>
     */
    protected function stringifyParameters(array $parameters): array
    {
        return array_map(static fn (mixed $parameter): string => (string)$parameter, $parameters);
    }
}
