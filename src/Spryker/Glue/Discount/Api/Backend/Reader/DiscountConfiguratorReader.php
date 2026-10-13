<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Reader;

use Generated\Shared\Transfer\DiscountConditionsTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorCollectionTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountCriteriaTransfer;
use Generated\Shared\Transfer\DiscountRuleGroupTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Spryker\Glue\Discount\Api\Backend\Exception\DiscountsBackendExceptionFactoryInterface;
use Spryker\Glue\Discount\DiscountConfig;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;
use Spryker\Zed\Discount\Exception\DiscountSortFieldNotSupportedException;

class DiscountConfiguratorReader implements DiscountConfiguratorReaderInterface
{
    protected const int SINGLE_ITEM_PAGE = 1;

    protected const int SINGLE_ITEM_MAX_PER_PAGE = 1;

    public function __construct(
        protected DiscountFacadeInterface $discountFacade,
        protected DiscountsBackendExceptionFactoryInterface $exceptionFactory
    ) {
    }

    public function getDiscountConfiguratorByUuid(string $uuid): DiscountConfiguratorTransfer
    {
        return $this->getDiscountConfigurator(
            (new DiscountConditionsTransfer())->addUuid($uuid),
            $uuid,
        );
    }

    public function getDiscountConfiguratorCollection(DiscountCriteriaTransfer $discountCriteriaTransfer): DiscountConfiguratorCollectionTransfer
    {
        try {
            return $this->discountFacade->getDiscountConfiguratorCollection($discountCriteriaTransfer);
        } catch (DiscountSortFieldNotSupportedException $exception) {
            throw $this->exceptionFactory->createSortFieldNotSupportedException($exception->getMessage());
        }
    }

    public function assertDiscountUuidsAvailable(): void
    {
        $this->getDiscountConfiguratorCollection(
            (new DiscountCriteriaTransfer())->setPagination(
                (new PaginationTransfer())->setPage(static::SINGLE_ITEM_PAGE)->setMaxPerPage(static::SINGLE_ITEM_MAX_PER_PAGE),
            ),
        );
    }

    public function getDiscountConfiguratorByIdDiscount(int $idDiscount): DiscountConfiguratorTransfer
    {
        return $this->getDiscountConfigurator(
            (new DiscountConditionsTransfer())->addIdDiscount($idDiscount),
            (string)$idDiscount,
        );
    }

    public function getDiscountRuleGroupTransfersByType(DiscountConfiguratorTransfer $discountConfiguratorTransfer): array
    {
        return [
            DiscountConfig::QUERY_STRING_TYPE_COLLECTOR => $this->findDiscountRuleGroup(
                DiscountConfig::QUERY_STRING_TYPE_COLLECTOR,
                $discountConfiguratorTransfer->getDiscountCalculator()?->getCollectorQueryString(),
            ),
            DiscountConfig::QUERY_STRING_TYPE_DECISION_RULE => $this->findDiscountRuleGroup(
                DiscountConfig::QUERY_STRING_TYPE_DECISION_RULE,
                $discountConfiguratorTransfer->getDiscountCondition()?->getDecisionRuleQueryString(),
            ),
        ];
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function getDiscountConfigurator(
        DiscountConditionsTransfer $discountConditionsTransfer,
        string $identifier
    ): DiscountConfiguratorTransfer {
        $discountCriteriaTransfer = (new DiscountCriteriaTransfer())
            ->setDiscountConditions($discountConditionsTransfer)
            ->setPagination(
                (new PaginationTransfer())
                    ->setPage(static::SINGLE_ITEM_PAGE)
                    ->setMaxPerPage(static::SINGLE_ITEM_MAX_PER_PAGE),
            );

        $discountConfiguratorTransfers = $this->getDiscountConfiguratorCollection($discountCriteriaTransfer)->getDiscountConfigurators();

        foreach ($discountConfiguratorTransfers as $discountConfiguratorTransfer) {
            return $discountConfiguratorTransfer;
        }

        throw $this->exceptionFactory->createDiscountNotFoundException($identifier);
    }

    protected function findDiscountRuleGroup(string $type, ?string $queryString): ?DiscountRuleGroupTransfer
    {
        if ($queryString === null || trim($queryString) === '') {
            return null;
        }

        $discountRuleGroupResponseTransfer = $this->discountFacade->buildDiscountRuleGroupFromQueryString($type, $queryString);

        if ($discountRuleGroupResponseTransfer->getIsSuccessful() !== true) {
            return null;
        }

        return $discountRuleGroupResponseTransfer->getDiscountRuleGroup();
    }
}
