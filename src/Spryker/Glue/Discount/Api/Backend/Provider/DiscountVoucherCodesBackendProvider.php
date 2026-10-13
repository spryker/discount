<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Provider;

use Generated\Api\Backend\DiscountVoucherCodesBackendResource;
use Generated\Shared\Transfer\DiscountVoucherConditionsTransfer;
use Generated\Shared\Transfer\DiscountVoucherCriteriaTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\ApiPlatform\State\Request\CollectionQueryReaderInterface;
use Spryker\Glue\Discount\Api\Backend\Exception\DiscountVoucherCodesBackendExceptionFactoryInterface;
use Spryker\Glue\Discount\Api\Backend\Mapper\DiscountVoucherCodeResourceMapperInterface;
use Spryker\Glue\Discount\Api\Backend\Reader\DiscountConfiguratorReaderInterface;
use Spryker\Glue\Discount\DiscountConfig;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;
use Spryker\Zed\Discount\Exception\DiscountSortFieldNotSupportedException;
use Symfony\Component\HttpFoundation\Request;

class DiscountVoucherCodesBackendProvider extends AbstractBackendProvider
{
    protected const string URI_VARIABLE_DISCOUNT_UUID = 'discountUuid';

    protected const string FILTER_KEY_PREFIX = 'discount-voucher-codes.';

    protected const string FILTER_CODE = 'code';

    protected const string FILTER_VOUCHER_BATCH = 'voucherBatch';

    public function __construct(
        protected DiscountFacadeInterface $discountFacade,
        protected DiscountVoucherCodeResourceMapperInterface $discountVoucherCodeResourceMapper,
        protected CollectionQueryReaderInterface $collectionQueryReader,
        protected DiscountConfiguratorReaderInterface $discountConfiguratorReader,
        protected DiscountConfig $discountConfig,
        protected DiscountVoucherCodesBackendExceptionFactoryInterface $exceptionFactory
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     *
     * @return array<int, \Generated\Api\Backend\DiscountVoucherCodesBackendResource>
     */
    protected function provideCollection(): array
    {
        $request = $this->getRequest();
        $discountUuid = (string)$this->getUriVariable(static::URI_VARIABLE_DISCOUNT_UUID);
        $paginationTransfer = $this->buildPaginationTransfer();

        $idDiscount = $this->discountConfiguratorReader
            ->getDiscountConfiguratorByUuid($discountUuid)
            ->getDiscountGeneralOrFail()
            ->getIdDiscountOrFail();

        $discountVoucherCriteriaTransfer = (new DiscountVoucherCriteriaTransfer())
            ->setDiscountVoucherConditions($this->buildDiscountVoucherConditions($request, $idDiscount))
            ->setPagination($paginationTransfer);

        $sortTransfers = $this->collectionQueryReader->getSortCollection(
            $request,
            $this->discountConfig->getDiscountVoucherCodeCollectionSortableFields(),
            DiscountConfig::RESPONSE_CODE_INVALID_SORT_FIELD,
        );

        foreach ($sortTransfers as $sortTransfer) {
            $discountVoucherCriteriaTransfer->addSort($sortTransfer);
        }

        try {
            $discountVoucherCollectionTransfer = $this->discountFacade->getDiscountVoucherCollection($discountVoucherCriteriaTransfer);
        } catch (DiscountSortFieldNotSupportedException $exception) {
            throw $this->exceptionFactory->createSortFieldNotSupportedException($exception->getMessage());
        }
        $discountVoucherCodesBackendResources = [];

        foreach ($discountVoucherCollectionTransfer->getDiscountVouchers() as $discountVoucherTransfer) {
            $discountVoucherCodesBackendResources[] = $this->discountVoucherCodeResourceMapper
                ->mapDiscountVoucherTransferToDiscountVoucherCodesBackendResource(
                    $discountVoucherTransfer,
                    $discountUuid,
                    new DiscountVoucherCodesBackendResource(),
                );
        }

        $this->setCollectionPagination(
            $paginationTransfer->getOffsetOrFail(),
            $paginationTransfer->getLimitOrFail(),
            $paginationTransfer->getNbResults() ?? 0,
        );

        return $discountVoucherCodesBackendResources;
    }

    protected function buildDiscountVoucherConditions(Request $request, int $idDiscount): DiscountVoucherConditionsTransfer
    {
        $filters = $this->collectionQueryReader->getFilters($request, static::FILTER_KEY_PREFIX);
        $discountVoucherConditionsTransfer = (new DiscountVoucherConditionsTransfer())->addIdDiscount($idDiscount);

        $code = $this->collectionQueryReader->findFilterValue($filters, static::FILTER_CODE);

        if ($code !== null) {
            $discountVoucherConditionsTransfer->setCode($code);
        }

        $voucherBatch = $this->collectionQueryReader->findIntegerFilterValue($filters, static::FILTER_VOUCHER_BATCH);

        if ($voucherBatch !== null) {
            $discountVoucherConditionsTransfer->addVoucherBatch($voucherBatch);
        }

        return $discountVoucherConditionsTransfer;
    }
}
