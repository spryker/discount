<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Persistence;

use Generated\Shared\Transfer\DiscountAmountCriteriaTransfer;
use Generated\Shared\Transfer\DiscountGeneralTransfer;
use Generated\Shared\Transfer\DiscountMoneyAmountTransfer;
use Generated\Shared\Transfer\DiscountTransfer;
use Orm\Zed\Discount\Persistence\SpyDiscount;
use Orm\Zed\Discount\Persistence\SpyDiscountAmount;
use Orm\Zed\Discount\Persistence\SpyDiscountAmountQuery;
use Orm\Zed\Discount\Persistence\SpyDiscountStore;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucherPool;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;

/**
 * @method \Spryker\Zed\Discount\Persistence\DiscountPersistenceFactory getFactory()
 */
class DiscountEntityManager extends AbstractEntityManager implements DiscountEntityManagerInterface
{
    public function createDiscount(DiscountTransfer $discountTransfer): DiscountTransfer
    {
        $discountEntity = new SpyDiscount();

        return $this->saveDiscount($discountEntity, $discountTransfer);
    }

    public function updateDiscount(DiscountTransfer $discountTransfer): DiscountTransfer
    {
        $discountEntity = $this->getFactory()->createDiscountQuery()
            ->filterByIdDiscount($discountTransfer->getIdDiscount())
            ->findOne();

        return $this->saveDiscount($discountEntity, $discountTransfer);
    }

    public function createDiscountAmount(DiscountMoneyAmountTransfer $discountMoneyAmountTransfer): DiscountMoneyAmountTransfer
    {
        $discountMapper = $this->getFactory()->createDiscountMapper();
        $discountAmountEntity = $discountMapper->mapDiscountMoneyAmountTransferToDiscountAmountEntity(
            $discountMoneyAmountTransfer,
            new SpyDiscountAmount(),
        );

        $discountAmountEntity->save();

        return $discountMapper->mapDiscountAmountEntityToDiscountMoneyAmountTransfer($discountAmountEntity, $discountMoneyAmountTransfer);
    }

    public function updateDiscountAmount(DiscountMoneyAmountTransfer $discountMoneyAmountTransfer): void
    {
        $discountAmountEntity = $this->getFactory()
            ->createDiscountAmountQuery()
            ->findOneByIdDiscountAmount($discountMoneyAmountTransfer->getIdDiscountAmountOrFail());

        $discountAmountEntity = $this->getFactory()
            ->createDiscountMapper()
            ->mapDiscountMoneyAmountTransferToDiscountAmountEntity($discountMoneyAmountTransfer, $discountAmountEntity);

        $discountAmountEntity->save();
    }

    public function createDiscountVoucherPool(DiscountGeneralTransfer $discountGeneralTransfer): int
    {
        $discountVoucherPoolEntity = $this->getFactory()
            ->createDiscountMapper()
            ->mapDiscountGeneralTransferToDiscountVoucherPoolEntity($discountGeneralTransfer, new SpyDiscountVoucherPool());

        $discountVoucherPoolEntity->save();

        return $discountVoucherPoolEntity->getIdDiscountVoucherPool();
    }

    /**
     * @param int $idDiscount
     * @param array<int> $storeIds
     *
     * @return void
     */
    public function createDiscountStoreRelations(int $idDiscount, array $storeIds): void
    {
        foreach ($storeIds as $idStore) {
            (new SpyDiscountStore())
                ->setFkDiscount($idDiscount)
                ->setFkStore($idStore)
                ->save();
        }
    }

    public function updateDiscountVoucherPool(DiscountGeneralTransfer $discountGeneralTransfer): int
    {
        /** @var \Orm\Zed\Discount\Persistence\SpyDiscountVoucherPool $discountVoucherPoolEntity */
        $discountVoucherPoolEntity = $this->getFactory()
            ->createDiscountVoucherPoolQuery()
            ->joinDiscount()
            ->useDiscountQuery()
                ->filterByIdDiscount($discountGeneralTransfer->getIdDiscountOrFail())
            ->endUse()
            ->findOne();

        $discountVoucherPoolEntity = $this->getFactory()
            ->createDiscountMapper()
            ->mapDiscountGeneralTransferToDiscountVoucherPoolEntity($discountGeneralTransfer, $discountVoucherPoolEntity);

        $discountVoucherPoolEntity->save();

        return $discountVoucherPoolEntity->getIdDiscountVoucherPool();
    }

    public function deleteDiscountAmounts(DiscountAmountCriteriaTransfer $discountAmountCriteriaTransfer): void
    {
        $discountAmountQuery = $this->getFactory()->createDiscountAmountQuery();
        $discountAmountQuery = $this->applyDiscountAmountCriteria($discountAmountQuery, $discountAmountCriteriaTransfer);
        /** @var \Propel\Runtime\Collection\ObjectCollection $discountAmountCollection */
        $discountAmountCollection = $discountAmountQuery->find();
        $discountAmountCollection->delete();
    }

    /**
     * @param int $idDiscount
     * @param array<int> $storeIds
     *
     * @return void
     */
    public function deleteDiscountStoreRelations(int $idDiscount, array $storeIds): void
    {
        if ($storeIds === []) {
            return;
        }

        /** @var \Propel\Runtime\Collection\ObjectCollection $dicountStoreCollection */
        $dicountStoreCollection = $this->getFactory()
            ->createDiscountStoreQuery()
            ->filterByFkDiscount($idDiscount)
            ->filterByFkStore_In($storeIds)
            ->find();
        $dicountStoreCollection->delete();
    }

    /**
     * @param array<int> $salesDiscountIds
     *
     * @return void
     */
    public function deleteSalesDiscountsBySalesDiscountIds(array $salesDiscountIds): void
    {
        $this->getFactory()
            ->createSalesDiscountQuery()
            ->filterByIdSalesDiscount_In($salesDiscountIds)
            ->delete();
    }

    /**
     * @param array<int> $salesDiscountIds
     *
     * @return void
     */
    public function deleteSalesDiscountCodesBySalesDiscountIds(array $salesDiscountIds): void
    {
        $this->getFactory()
            ->createSalesDiscountCodeQuery()
            ->filterByFkSalesDiscount_In($salesDiscountIds)
            ->delete();
    }

    public function deleteDiscountVouchersByIdDiscountVoucherPool(int $idDiscountVoucherPool): void
    {
        $this->getFactory()
            ->createDiscountVoucherQuery()
            ->filterByFkDiscountVoucherPool($idDiscountVoucherPool)
            ->delete();
    }

    public function deleteDiscountVoucherPoolByIdDiscountVoucherPool(int $idDiscountVoucherPool): void
    {
        $this->getFactory()
            ->createDiscountVoucherPoolQuery()
            ->filterByIdDiscountVoucherPool($idDiscountVoucherPool)
            ->delete();
    }

    protected function saveDiscount(SpyDiscount $discountEntity, DiscountTransfer $discountTransfer): DiscountTransfer
    {
        $discountMapper = $this->getFactory()->createDiscountMapper();
        $discountEntity = $discountMapper->mapDiscountTransferToDiscountEntity($discountTransfer, $discountEntity);

        $discountEntity->save();

        return $discountMapper->mapDiscountEntityToDiscountTransfer($discountEntity, $discountTransfer);
    }

    protected function applyDiscountAmountCriteria(
        SpyDiscountAmountQuery $discountAmountQuery,
        DiscountAmountCriteriaTransfer $discountAmountCriteriaTransfer
    ): SpyDiscountAmountQuery {
        if ($discountAmountCriteriaTransfer->getIdDiscount()) {
            $discountAmountQuery->filterByFkDiscount($discountAmountCriteriaTransfer->getIdDiscountOrFail());
        }

        if ($discountAmountCriteriaTransfer->getDiscountAmountIds()) {
            $discountAmountQuery->filterByIdDiscountAmount_In($discountAmountCriteriaTransfer->getDiscountAmountIds());
        }

        return $discountAmountQuery;
    }
}
