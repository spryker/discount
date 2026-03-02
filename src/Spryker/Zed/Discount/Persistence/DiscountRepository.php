<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Persistence;

use Generated\Shared\Transfer\StoreRelationTransfer;
use Orm\Zed\Discount\Persistence\Map\SpyDiscountVoucherTableMap;
use Orm\Zed\Discount\Persistence\SpyDiscount;
use Orm\Zed\Sales\Persistence\Map\SpySalesDiscountCodeTableMap;
use Orm\Zed\Sales\Persistence\Map\SpySalesDiscountTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \Spryker\Zed\Discount\Persistence\DiscountPersistenceFactory getFactory()
 */
class DiscountRepository extends AbstractRepository implements DiscountRepositoryInterface
{
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
}
