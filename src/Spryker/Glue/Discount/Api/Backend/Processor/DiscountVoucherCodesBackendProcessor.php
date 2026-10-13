<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Processor;

use Generated\Shared\Transfer\DiscountVoucherCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\DiscountVoucherCollectionResponseTransfer;
use Generated\Shared\Transfer\DiscountVoucherConditionsTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractBackendProcessor;
use Spryker\Glue\Discount\Api\Backend\Exception\DiscountVoucherCodesBackendExceptionFactoryInterface;
use Spryker\Glue\Discount\Api\Backend\Reader\DiscountConfiguratorReaderInterface;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;

class DiscountVoucherCodesBackendProcessor extends AbstractBackendProcessor
{
    protected const string URI_VARIABLE_DISCOUNT_UUID = 'discountUuid';

    /**
     * @uses \Spryker\Zed\Discount\Business\Deleter\DiscountVoucherDeleter::ERROR_PARAMETER_CODE
     */
    protected const string ERROR_PARAMETER_CODE = 'code';

    public function __construct(
        protected DiscountFacadeInterface $discountFacade,
        protected DiscountConfiguratorReaderInterface $discountConfiguratorReader,
        protected DiscountVoucherCodesBackendExceptionFactoryInterface $exceptionFactory
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processPost(mixed $data): mixed
    {
        /** @var \Generated\Api\Backend\DiscountVoucherCodesBackendResource $discountVoucherCodesBackendResource */
        $discountVoucherCodesBackendResource = $data;
        $discountUuid = (string)$this->getUriVariable(static::URI_VARIABLE_DISCOUNT_UUID);
        $isAll = $discountVoucherCodesBackendResource->all === true;
        $codes = $this->extractCodes($discountVoucherCodesBackendResource->codes);

        if ($isAll === ($codes !== [])) {
            throw $this->exceptionFactory->createInvalidVoucherCodesDeleteRequestException();
        }

        $idDiscount = $this->discountConfiguratorReader
            ->getDiscountConfiguratorByUuid($discountUuid)
            ->getDiscountGeneralOrFail()
            ->getIdDiscountOrFail();

        $discountVoucherCollectionResponseTransfer = $this->discountFacade->deleteDiscountVoucherCollection(
            (new DiscountVoucherCollectionDeleteCriteriaTransfer())->setDiscountVoucherConditions(
                (new DiscountVoucherConditionsTransfer())
                    ->addIdDiscount($idDiscount)
                    ->setCodes($codes),
            ),
        );

        if ($discountVoucherCollectionResponseTransfer->getIsSuccessful() !== true) {
            throw $this->exceptionFactory->createVoucherCodesNotFoundException(
                $this->extractMissingCodes($discountVoucherCollectionResponseTransfer, $codes),
                $discountUuid,
            );
        }

        return null;
    }

    /**
     * @param array<mixed>|null $rawCodes
     *
     * @return array<int, string>
     */
    protected function extractCodes(?array $rawCodes): array
    {
        $codes = [];

        foreach ($rawCodes ?? [] as $rawCode) {
            $code = trim((string)$rawCode);

            if ($code !== '') {
                $codes[] = $code;
            }
        }

        return array_values(array_unique($codes));
    }

    /**
     * @param array<int, string> $submittedCodes
     *
     * @return array<int, string> Missing codes keyed by their position in the submitted list.
     */
    protected function extractMissingCodes(
        DiscountVoucherCollectionResponseTransfer $discountVoucherCollectionResponseTransfer,
        array $submittedCodes
    ): array {
        $missingCodes = [];

        foreach ($discountVoucherCollectionResponseTransfer->getErrors() as $errorTransfer) {
            $missingCode = (string)($errorTransfer->getParameters()[static::ERROR_PARAMETER_CODE] ?? '');
            $position = array_search($missingCode, $submittedCodes, true);
            $missingCodes[$position === false ? count($missingCodes) : $position] = $missingCode;
        }

        ksort($missingCodes);

        return $missingCodes;
    }
}
