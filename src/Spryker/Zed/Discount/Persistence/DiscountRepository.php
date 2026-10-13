<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Persistence;

use ArrayObject;
use Generated\Shared\Transfer\DiscountConditionsTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorCollectionTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountCriteriaTransfer;
use Generated\Shared\Transfer\DiscountVoucherCollectionTransfer;
use Generated\Shared\Transfer\DiscountVoucherConditionsTransfer;
use Generated\Shared\Transfer\DiscountVoucherCriteriaTransfer;
use Generated\Shared\Transfer\DiscountVoucherTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Orm\Zed\Discount\Persistence\Map\SpyDiscountTableMap;
use Orm\Zed\Discount\Persistence\Map\SpyDiscountVoucherTableMap;
use Orm\Zed\Discount\Persistence\SpyDiscount;
use Orm\Zed\Discount\Persistence\SpyDiscountQuery;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucherQuery;
use Orm\Zed\Sales\Persistence\Map\SpySalesDiscountCodeTableMap;
use Orm\Zed\Sales\Persistence\Map\SpySalesDiscountTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Spryker\Zed\Discount\Exception\DiscountSortFieldNotSupportedException;
use Spryker\Zed\Discount\Persistence\Propel\Mapper\DiscountMapper;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \Spryker\Zed\Discount\Persistence\DiscountPersistenceFactory getFactory()
 */
class DiscountRepository extends AbstractRepository implements DiscountRepositoryInterface
{
    protected const string DISCOUNT_COLLECTION_DEFAULT_SORT_FIELD = 'createdAt';

    protected const string DISCOUNT_VOUCHER_COLLECTION_DEFAULT_SORT_FIELD = 'createdAt';

    protected const string MESSAGE_SORT_FIELD_NOT_SUPPORTED = 'Sort field "%s" is not supported. Supported fields: %s.';

    protected const string DISCOUNT_DISPLAY_NAME_LIKE_PATTERN = '%%%s%%';

    /**
     * @param array<string> $codes
     *
     * @return array<string>
     */
    public function findVoucherCodesExceedingUsageLimit(array $codes): array
    {
        /** @var \Propel\Runtime\Collection\ArrayCollection $voucherCodesExceedingUsageLimit */
        $voucherCodesExceedingUsageLimit = $this->getFactory()
            ->createDiscountVoucherQuery()
            ->filterByCode($codes, Criteria::IN)
            ->filterByMaxNumberOfUses(0, Criteria::GREATER_THAN)
            ->where(SpyDiscountVoucherTableMap::COL_NUMBER_OF_USES . '>=' . SpyDiscountVoucherTableMap::COL_MAX_NUMBER_OF_USES)
            ->select(SpyDiscountVoucherTableMap::COL_CODE)
            ->find();

        return $voucherCodesExceedingUsageLimit->toArray();
    }

    /**
     * @deprecated Will be removed in the next major without replacement.
     *
     * @return bool
     */
    public function hasPriorityField(): bool
    {
        return property_exists(SpyDiscount::class, 'priority');
    }

    /**
     * @param int $idDiscount
     *
     * @return array<\Generated\Shared\Transfer\MoneyValueTransfer>
     */
    public function getDiscountAmountCollectionForDiscount(int $idDiscount): array
    {
        $discountAmountEntities = $this->getFactory()
            ->createDiscountAmountQuery()
            ->filterByFkDiscount($idDiscount)
            ->find();

        if ($discountAmountEntities->count() === 0) {
            return [];
        }

        return $this->getFactory()
            ->createDiscountMapper()
            ->mapDiscountAmountEntitiesToMoneyValueTransfers($discountAmountEntities, []);
    }

    /**
     * @module Store
     */
    public function getDiscountStoreRelations(int $idDiscount): StoreRelationTransfer
    {
        $discountStoreEntities = $this->getFactory()
            ->createDiscountStoreQuery()
            ->joinWithSpyStore()
            ->filterByFkDiscount($idDiscount)
            ->find();

        return $this->getFactory()
            ->createDiscountMapper()
            ->mapDiscountStoreEntitiesToStoreRelationTransfer(
                $discountStoreEntities,
                (new StoreRelationTransfer())->setIdEntity($idDiscount),
            );
    }

    public function discountExists(int $idDiscount): bool
    {
        return $this->getFactory()
            ->createDiscountQuery()
            ->filterByIdDiscount($idDiscount)
            ->exists();
    }

    public function discountVoucherPoolExists(int $idDiscount): bool
    {
        return $this->getFactory()
            ->createDiscountQuery()
            ->filterByIdDiscount($idDiscount)
            ->filterByFkDiscountVoucherPool(null, Criteria::ISNOTNULL)
            ->exists();
    }

    /**
     * @param array<int> $salesOrderIds
     *
     * @return array<string>
     */
    public function getUsedSalesDiscountCodesBySalesOrderIds(array $salesOrderIds): array
    {
        $salesDiscountCodes = $this->getFactory()
            ->createSalesDiscountCodeQuery()
            ->useDiscountQuery()
                ->filterByFkSalesOrder_In($salesOrderIds)
            ->endUse()
            ->addJoin(
                SpySalesDiscountCodeTableMap::COL_CODE,
                SpyDiscountVoucherTableMap::COL_CODE,
                Criteria::LEFT_JOIN,
            )
            ->where(sprintf('%s > 0', SpyDiscountVoucherTableMap::COL_NUMBER_OF_USES))
            ->select([SpySalesDiscountCodeTableMap::COL_CODE])
            ->distinct()
            ->find()
            ->getData();

        return $salesDiscountCodes;
    }

    /**
     * @param array<int> $salesOrderIds
     * @param array<int> $salesExpenseIds
     * @param array<int> $salesOrderItemIds
     *
     * @return array<int>
     */
    public function getSalesDiscountIds(
        array $salesOrderIds = [],
        array $salesExpenseIds = [],
        array $salesOrderItemIds = []
    ): array {
        $salesDiscountQuery = $this->getFactory()->createSalesDiscountQuery();

        if ($salesOrderIds !== []) {
            $salesDiscountQuery->filterByFkSalesOrder_In($salesOrderIds);
        }

        if ($salesExpenseIds !== []) {
            $salesDiscountQuery->filterByFkSalesExpense_In($salesExpenseIds);
        }

        if ($salesOrderItemIds !== []) {
            $salesDiscountQuery->filterByFkSalesOrderItem_In($salesOrderItemIds);
        }

        $salesDiscountIds = $salesDiscountQuery->select([SpySalesDiscountTableMap::COL_ID_SALES_DISCOUNT])
            ->find()
            ->getData();

        return $salesDiscountIds;
    }

    public function getDiscountConfiguratorCollection(DiscountCriteriaTransfer $discountCriteriaTransfer): DiscountConfiguratorCollectionTransfer
    {
        $paginationTransfer = $discountCriteriaTransfer->getPagination();

        $discountQuery = $this->buildDiscountQueryByConditions($discountCriteriaTransfer->getDiscountConditions());
        $this->applySortToQuery(
            $discountQuery,
            $discountCriteriaTransfer->getSortCollection(),
            $this->getFactory()->getConfig()->getDiscountCollectionSortableColumnMap(),
            static::DISCOUNT_COLLECTION_DEFAULT_SORT_FIELD,
            SpyDiscountTableMap::COL_ID_DISCOUNT,
        );

        /** @var \Orm\Zed\Discount\Persistence\SpyDiscountQuery $discountQuery */
        $discountQuery = $this->applyPagination($discountQuery, $paginationTransfer);

        $discountMapper = $this->getFactory()->createDiscountMapper();
        $discountConfiguratorCollectionTransfer = (new DiscountConfiguratorCollectionTransfer())->setPagination($paginationTransfer);

        foreach ($discountQuery->find() as $discountEntity) {
            $discountConfiguratorCollectionTransfer->addDiscountConfigurator(
                $discountMapper->mapDiscountEntityToDiscountConfiguratorTransfer($discountEntity, new DiscountConfiguratorTransfer()),
            );
        }

        return $discountConfiguratorCollectionTransfer;
    }

    public function getDiscountVoucherCollection(
        DiscountVoucherCriteriaTransfer $discountVoucherCriteriaTransfer
    ): DiscountVoucherCollectionTransfer {
        $paginationTransfer = $discountVoucherCriteriaTransfer->getPagination();

        $discountVoucherQuery = $this->buildDiscountVoucherQueryByConditions(
            $discountVoucherCriteriaTransfer->getDiscountVoucherConditions(),
        );
        $this->applySortToQuery(
            $discountVoucherQuery,
            $discountVoucherCriteriaTransfer->getSortCollection(),
            $this->getFactory()->getConfig()->getDiscountVoucherCollectionSortableColumnMap(),
            static::DISCOUNT_VOUCHER_COLLECTION_DEFAULT_SORT_FIELD,
            SpyDiscountVoucherTableMap::COL_ID_DISCOUNT_VOUCHER,
        );

        /** @var \Orm\Zed\Discount\Persistence\SpyDiscountVoucherQuery $discountVoucherQuery */
        $discountVoucherQuery = $this->applyPagination($discountVoucherQuery, $paginationTransfer);

        $discountMapper = $this->getFactory()->createDiscountMapper();
        $discountVoucherCollectionTransfer = (new DiscountVoucherCollectionTransfer())->setPagination($paginationTransfer);

        foreach ($discountVoucherQuery->find() as $discountVoucherEntity) {
            $discountVoucherCollectionTransfer->addDiscountVoucher(
                $discountMapper->mapDiscountVoucherEntityToDiscountVoucherTransfer($discountVoucherEntity, new DiscountVoucherTransfer()),
            );
        }

        return $discountVoucherCollectionTransfer;
    }

    /**
     * @module Store
     *
     * @param array<int> $discountIds
     *
     * @return array<int, \Generated\Shared\Transfer\StoreRelationTransfer>
     */
    public function getDiscountStoreRelationsByDiscountIds(array $discountIds): array
    {
        if ($discountIds === []) {
            return [];
        }

        $discountStoreEntities = $this->getFactory()
            ->createDiscountStoreQuery()
            ->joinWithSpyStore()
            ->filterByFkDiscount_In($discountIds)
            ->find();

        return $this->getFactory()
            ->createDiscountMapper()
            ->mapDiscountStoreEntitiesToStoreRelationTransfersIndexedByIdDiscount($discountStoreEntities);
    }

    /**
     * @module Currency
     *
     * @param array<int> $discountIds
     *
     * @return array<int, array<int, \Generated\Shared\Transfer\MoneyValueTransfer>>
     */
    public function getDiscountAmountsByDiscountIds(array $discountIds): array
    {
        if ($discountIds === []) {
            return [];
        }

        $discountAmountEntities = $this->getFactory()
            ->createDiscountAmountQuery()
            ->joinWithCurrency()
            ->filterByFkDiscount_In($discountIds)
            ->find();

        return $this->getFactory()
            ->createDiscountMapper()
            ->mapDiscountAmountEntitiesToMoneyValueTransfersIndexedByIdDiscount($discountAmountEntities);
    }

    public function findIdDiscountByDisplayName(string $displayName): ?int
    {
        /** @var string|int|null $idDiscount */
        $idDiscount = $this->getFactory()
            ->createDiscountQuery()
            ->filterByDisplayName($displayName)
            ->select(SpyDiscountTableMap::COL_ID_DISCOUNT)
            ->findOne();

        return $idDiscount === null ? null : (int)$idDiscount;
    }

    protected function buildDiscountQueryByConditions(?DiscountConditionsTransfer $discountConditionsTransfer): SpyDiscountQuery
    {
        $discountQuery = $this->getFactory()->createDiscountQuery();

        if ($discountConditionsTransfer === null) {
            return $discountQuery;
        }

        if ($discountConditionsTransfer->getDiscountIds() !== []) {
            $discountQuery->filterByIdDiscount_In($discountConditionsTransfer->getDiscountIds());
        }

        if ($discountConditionsTransfer->getUuids() !== []) {
            $discountQuery->filterByUuid_In($discountConditionsTransfer->getUuids());
        }

        if ($discountConditionsTransfer->getIsActive() !== null) {
            $discountQuery->filterByIsActive($discountConditionsTransfer->getIsActiveOrFail());
        }

        if ($discountConditionsTransfer->getDiscountTypes() !== []) {
            $discountQuery->filterByDiscountType_In($discountConditionsTransfer->getDiscountTypes());
        }

        if ($discountConditionsTransfer->getDisplayName() !== null) {
            $discountQuery->where(
                'LOWER(' . SpyDiscountTableMap::COL_DISPLAY_NAME . ') LIKE ?',
                sprintf(static::DISCOUNT_DISPLAY_NAME_LIKE_PATTERN, mb_strtolower($discountConditionsTransfer->getDisplayNameOrFail())),
            );
        }

        if ($discountConditionsTransfer->getValidFrom() !== null) {
            $discountQuery->filterByValidFrom($discountConditionsTransfer->getValidFromOrFail(), Criteria::GREATER_EQUAL);
        }

        if ($discountConditionsTransfer->getValidTo() !== null) {
            $discountQuery->filterByValidTo($discountConditionsTransfer->getValidToOrFail(), Criteria::LESS_EQUAL);
        }

        return $this->applyStoreNamesToQuery($discountQuery, $discountConditionsTransfer->getStoreNames());
    }

    /**
     * @module Store
     *
     * @param array<int, string> $storeNames
     */
    protected function applyStoreNamesToQuery(SpyDiscountQuery $discountQuery, array $storeNames): SpyDiscountQuery
    {
        if ($storeNames === []) {
            return $discountQuery;
        }

        $discountQuery
            ->useInSpyDiscountStoreQuery()
                ->useSpyStoreQuery()
                    ->filterByName_In($storeNames)
                ->endUse()
            ->endUse();

        return $discountQuery;
    }

    protected function buildDiscountVoucherQueryByConditions(
        ?DiscountVoucherConditionsTransfer $discountVoucherConditionsTransfer
    ): SpyDiscountVoucherQuery {
        $discountVoucherQuery = $this->getFactory()->createDiscountVoucherQuery();

        if ($discountVoucherConditionsTransfer === null) {
            return $discountVoucherQuery;
        }

        $discountVoucherQuery = $this->getFactory()
            ->createDiscountVoucherQueryBuilder()
            ->applyDiscountVoucherConditions($discountVoucherQuery, $discountVoucherConditionsTransfer);

        return $this->applyDiscountIdsToDiscountVoucherQuery(
            $discountVoucherQuery,
            $discountVoucherConditionsTransfer->getDiscountIds(),
        );
    }

    /**
     * @param array<int, int> $discountIds
     */
    protected function applyDiscountIdsToDiscountVoucherQuery(
        SpyDiscountVoucherQuery $discountVoucherQuery,
        array $discountIds
    ): SpyDiscountVoucherQuery {
        $discountQuery = $discountVoucherQuery
            ->useVoucherPoolQuery()
                ->useDiscountQuery();

        if ($discountIds !== []) {
            $discountQuery->filterByIdDiscount_In($discountIds);
        }

        $discountQuery
                ->endUse()
            ->endUse();

        return $discountVoucherQuery->withColumn(SpyDiscountTableMap::COL_ID_DISCOUNT, DiscountMapper::VIRTUAL_COLUMN_ID_DISCOUNT);
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\SortTransfer> $sortCollection
     * @param array<string, string> $sortableFieldMap
     *
     * @throws \Spryker\Zed\Discount\Exception\DiscountSortFieldNotSupportedException
     */
    protected function applySortToQuery(
        ModelCriteria $query,
        ArrayObject $sortCollection,
        array $sortableFieldMap,
        string $defaultSortField,
        string $tiebreakerColumn
    ): void {
        $tiebreakerDirection = Criteria::DESC;
        $isSortApplied = false;

        foreach ($sortCollection as $sortTransfer) {
            $column = $sortableFieldMap[(string)$sortTransfer->getField()] ?? null;

            if ($column === null) {
                throw new DiscountSortFieldNotSupportedException(sprintf(
                    static::MESSAGE_SORT_FIELD_NOT_SUPPORTED,
                    (string)$sortTransfer->getField(),
                    implode(', ', array_keys($sortableFieldMap)),
                ));
            }

            $tiebreakerDirection = $sortTransfer->getIsAscending() === false ? Criteria::DESC : Criteria::ASC;
            $this->addOrderByColumn($query, $column, $tiebreakerDirection);
            $isSortApplied = true;
        }

        if (!$isSortApplied) {
            $this->addOrderByColumn($query, $sortableFieldMap[$defaultSortField], $tiebreakerDirection);
        }

        $this->addOrderByColumn($query, $tiebreakerColumn, $tiebreakerDirection);
    }

    protected function addOrderByColumn(ModelCriteria $query, string $column, string $direction): void
    {
        if ($direction === Criteria::DESC) {
            $query->addDescendingOrderByColumn($column);

            return;
        }

        $query->addAscendingOrderByColumn($column);
    }

    protected function applyPagination(ModelCriteria $query, ?PaginationTransfer $paginationTransfer): ModelCriteria
    {
        if ($paginationTransfer === null) {
            return $query;
        }

        $propelModelPager = $query->paginate(
            $paginationTransfer->requirePage()->getPageOrFail(),
            $paginationTransfer->requireMaxPerPage()->getMaxPerPageOrFail(),
        );

        $paginationTransfer
            ->setNbResults($propelModelPager->getNbResults())
            ->setFirstIndex($propelModelPager->getFirstIndex())
            ->setLastIndex($propelModelPager->getLastIndex())
            ->setFirstPage($propelModelPager->getFirstPage())
            ->setLastPage($propelModelPager->getLastPage())
            ->setNextPage($propelModelPager->getNextPage())
            ->setPreviousPage($propelModelPager->getPreviousPage());

        return $propelModelPager->getQuery();
    }
}
