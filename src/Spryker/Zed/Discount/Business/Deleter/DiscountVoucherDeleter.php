<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Business\Deleter;

use ArrayObject;
use Generated\Shared\Transfer\DiscountVoucherCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\DiscountVoucherCollectionResponseTransfer;
use Generated\Shared\Transfer\DiscountVoucherCriteriaTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Spryker\Shared\Discount\DiscountConfig;
use Spryker\Zed\Discount\Persistence\DiscountEntityManagerInterface;
use Spryker\Zed\Discount\Persistence\DiscountRepositoryInterface;

class DiscountVoucherDeleter implements DiscountVoucherDeleterInterface
{
    public const string ENTITY_IDENTIFIER_CODE = 'discountVoucher.code';

    public const string ERROR_PARAMETER_CODE = 'code';

    public function __construct(
        protected DiscountRepositoryInterface $discountRepository,
        protected DiscountEntityManagerInterface $discountEntityManager
    ) {
    }

    public function deleteDiscountVoucherCollection(
        DiscountVoucherCollectionDeleteCriteriaTransfer $discountVoucherCollectionDeleteCriteriaTransfer
    ): DiscountVoucherCollectionResponseTransfer {
        $discountVoucherConditionsTransfer = $discountVoucherCollectionDeleteCriteriaTransfer->getDiscountVoucherConditions();

        if ($discountVoucherConditionsTransfer === null) {
            return $this->createNotFoundResponse();
        }

        if ($discountVoucherConditionsTransfer->getDiscountIds() === []) {
            return $this->createNotFoundResponse();
        }

        if ($discountVoucherConditionsTransfer->getCodes() === []) {
            return (new DiscountVoucherCollectionResponseTransfer())
                ->setDeletedCount($this->discountEntityManager->deleteDiscountVoucherCollection($discountVoucherCollectionDeleteCriteriaTransfer))
                ->setIsSuccessful(true);
        }

        $discountVoucherTransfers = $this->discountRepository->getDiscountVoucherCollection(
            (new DiscountVoucherCriteriaTransfer())->setDiscountVoucherConditions($discountVoucherConditionsTransfer),
        )->getDiscountVouchers();

        $missingCodes = $this->findMissingCodes($discountVoucherConditionsTransfer->getCodes(), $discountVoucherTransfers);

        if ($missingCodes !== []) {
            return $this->createNotFoundResponse($missingCodes);
        }

        return (new DiscountVoucherCollectionResponseTransfer())
            ->setDiscountVouchers($discountVoucherTransfers)
            ->setDeletedCount($this->discountEntityManager->deleteDiscountVoucherCollection($discountVoucherCollectionDeleteCriteriaTransfer))
            ->setIsSuccessful(true);
    }

    /**
     * @param array<int, string> $codes
     * @param \ArrayObject<int, \Generated\Shared\Transfer\DiscountVoucherTransfer> $discountVoucherTransfers
     *
     * @return array<int, string>
     */
    protected function findMissingCodes(array $codes, ArrayObject $discountVoucherTransfers): array
    {
        $foundCodes = [];

        foreach ($discountVoucherTransfers as $discountVoucherTransfer) {
            $foundCodes[] = (string)$discountVoucherTransfer->getCode();
        }

        return array_values(array_diff($codes, $foundCodes));
    }

    /**
     * @param array<int, string> $missingCodes
     */
    protected function createNotFoundResponse(array $missingCodes = ['']): DiscountVoucherCollectionResponseTransfer
    {
        $discountVoucherCollectionResponseTransfer = (new DiscountVoucherCollectionResponseTransfer())->setIsSuccessful(false);

        foreach ($missingCodes as $missingCode) {
            $discountVoucherCollectionResponseTransfer->addError(
                (new ErrorTransfer())
                    ->setMessage(DiscountConfig::TRANSLATION_KEY_VOUCHER_CODE_NOT_FOUND)
                    ->setEntityIdentifier(static::ENTITY_IDENTIFIER_CODE)
                    ->setParameters([static::ERROR_PARAMETER_CODE => $missingCode]),
            );
        }

        return $discountVoucherCollectionResponseTransfer;
    }
}
