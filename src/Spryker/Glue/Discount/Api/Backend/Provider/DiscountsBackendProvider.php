<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Provider;

use Generated\Api\Backend\DiscountsBackendResource;
use Generated\Shared\Transfer\DiscountConditionsTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountCriteriaTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\ApiPlatform\State\Request\CollectionQueryReaderInterface;
use Spryker\Glue\Discount\Api\Backend\Mapper\DiscountResourceMapperInterface;
use Spryker\Glue\Discount\Api\Backend\Reader\DiscountConfiguratorReaderInterface;
use Spryker\Glue\Discount\DiscountConfig;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;
use Symfony\Component\HttpFoundation\Request;

class DiscountsBackendProvider extends AbstractBackendProvider
{
    protected const string URI_VARIABLE_UUID = 'uuid';

    protected const string FILTER_KEY_PREFIX = 'discounts.';

    protected const string FILTER_DISPLAY_NAME = 'displayName';

    protected const string FILTER_IS_ACTIVE = 'isActive';

    protected const string FILTER_DISCOUNT_TYPE = 'discountType';

    protected const string FILTER_STORE = 'store';

    protected const string FILTER_VALID_FROM = 'validFrom';

    protected const string FILTER_VALID_TO = 'validTo';

    public function __construct(
        protected DiscountFacadeInterface $discountFacade,
        protected DiscountResourceMapperInterface $discountResourceMapper,
        protected CollectionQueryReaderInterface $collectionQueryReader,
        protected DiscountConfiguratorReaderInterface $discountConfiguratorReader,
        protected DiscountConfig $discountConfig
    ) {
    }

    protected function provideItem(): ?object
    {
        return $this->buildDiscountsBackendResource(
            $this->discountConfiguratorReader->getDiscountConfiguratorByUuid(
                (string)$this->getUriVariable(static::URI_VARIABLE_UUID),
            ),
        );
    }

    /**
     * @return array<int, \Generated\Api\Backend\DiscountsBackendResource>
     */
    protected function provideCollection(): array
    {
        $request = $this->getRequest();
        $paginationTransfer = $this->buildPaginationTransfer();

        $discountCriteriaTransfer = (new DiscountCriteriaTransfer())
            ->setDiscountConditions($this->buildDiscountConditions($request))
            ->setPagination($paginationTransfer);

        $sortTransfers = $this->collectionQueryReader->getSortCollection(
            $request,
            $this->discountConfig->getDiscountCollectionSortableFields(),
            DiscountConfig::RESPONSE_CODE_INVALID_SORT_FIELD,
        );

        foreach ($sortTransfers as $sortTransfer) {
            $discountCriteriaTransfer->addSort($sortTransfer);
        }

        $discountConfiguratorCollectionTransfer = $this->discountConfiguratorReader->getDiscountConfiguratorCollection($discountCriteriaTransfer);
        $discountsBackendResources = [];

        foreach ($discountConfiguratorCollectionTransfer->getDiscountConfigurators() as $discountConfiguratorTransfer) {
            $discountsBackendResources[] = $this->buildDiscountsBackendResource($discountConfiguratorTransfer);
        }

        $this->setCollectionPagination(
            $paginationTransfer->getOffsetOrFail(),
            $paginationTransfer->getLimitOrFail(),
            $paginationTransfer->getNbResults() ?? 0,
        );

        return $discountsBackendResources;
    }

    protected function buildDiscountsBackendResource(
        DiscountConfiguratorTransfer $discountConfiguratorTransfer
    ): DiscountsBackendResource {
        return $this->discountResourceMapper->mapDiscountConfiguratorTransferToDiscountsBackendResource(
            $discountConfiguratorTransfer,
            $this->discountConfiguratorReader->getDiscountRuleGroupTransfersByType($discountConfiguratorTransfer),
            new DiscountsBackendResource(),
        );
    }

    protected function buildDiscountConditions(Request $request): DiscountConditionsTransfer
    {
        $filters = $this->collectionQueryReader->getFilters($request, static::FILTER_KEY_PREFIX);

        return (new DiscountConditionsTransfer())
            ->setDisplayName($this->collectionQueryReader->findFilterValue($filters, static::FILTER_DISPLAY_NAME))
            ->setValidFrom($this->collectionQueryReader->findDateTimeFilterValue($filters, static::FILTER_VALID_FROM))
            ->setValidTo($this->collectionQueryReader->findDateTimeFilterValue($filters, static::FILTER_VALID_TO))
            ->setIsActive($this->collectionQueryReader->findBooleanFilterValue($filters, static::FILTER_IS_ACTIVE))
            ->setDiscountTypes($this->collectionQueryReader->findFilterValues($filters, static::FILTER_DISCOUNT_TYPE))
            ->setStoreNames($this->collectionQueryReader->findFilterValues($filters, static::FILTER_STORE));
    }
}
