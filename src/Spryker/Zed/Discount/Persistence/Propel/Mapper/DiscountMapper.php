<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\DiscountGeneralTransfer;
use Generated\Shared\Transfer\DiscountMoneyAmountTransfer;
use Generated\Shared\Transfer\DiscountTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Orm\Zed\Discount\Persistence\SpyDiscount;
use Orm\Zed\Discount\Persistence\SpyDiscountAmount;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucherPool;
use Orm\Zed\Store\Persistence\SpyStore;
use Propel\Runtime\Collection\Collection;

class DiscountMapper
{
    /**
     * @var string
     */
    protected const DATE_TIME_FORMAT = 'Y-d-m H:i:s';

    public function mapDiscountTransferToDiscountEntity(DiscountTransfer $discountTransfer, SpyDiscount $discountEntity): SpyDiscount
    {
        $discountEntity->fromArray($discountTransfer->toArray());

        return $discountEntity;
    }

    public function mapDiscountEntityToDiscountTransfer(SpyDiscount $discountEntity, DiscountTransfer $discountTransfer): DiscountTransfer
    {
        /** @var string|null $validFrom */
        $validFrom = $discountEntity->getValidFrom(static::DATE_TIME_FORMAT);
        /** @var string|null $validTo */
        $validTo = $discountEntity->getValidTo(static::DATE_TIME_FORMAT);

        return $discountTransfer->fromArray($discountEntity->toArray(), true)
            ->setValidFrom($validFrom)
            ->setValidTo($validTo);
    }

    public function mapDiscountGeneralTransferToDiscountVoucherPoolEntity(
        DiscountGeneralTransfer $discountGeneralTransfer,
        SpyDiscountVoucherPool $discountVoucherPoolEntity
    ): SpyDiscountVoucherPool {
        return $discountVoucherPoolEntity
            ->setName($discountGeneralTransfer->getDisplayNameOrFail())
            ->setIsActive(true);
    }

    public function mapDiscountMoneyAmountTransferToDiscountAmountEntity(
        DiscountMoneyAmountTransfer $discountMoneyAmountTransfer,
        SpyDiscountAmount $discountAmountEntity
    ): SpyDiscountAmount {
        $discountAmountEntity->fromArray($discountMoneyAmountTransfer->modifiedToArray());

        return $discountAmountEntity;
    }

    public function mapDiscountAmountEntityToDiscountMoneyAmountTransfer(
        SpyDiscountAmount $discountAmountEntity,
        DiscountMoneyAmountTransfer $discountMoneyAmountTransfer
    ): DiscountMoneyAmountTransfer {
        return $discountMoneyAmountTransfer->fromArray($discountAmountEntity->toArray(), true);
    }

    /**
     * @param \Propel\Runtime\Collection\Collection<\Orm\Zed\Discount\Persistence\SpyDiscountAmount> $discountAmountEntities
     * @param array<\Generated\Shared\Transfer\MoneyValueTransfer> $moneyValueTransfers
     *
     * @return array<\Generated\Shared\Transfer\MoneyValueTransfer>
     */
    public function mapDiscountAmountEntitiesToMoneyValueTransfers(
        Collection $discountAmountEntities,
        array $moneyValueTransfers
    ): array {
        foreach ($discountAmountEntities as $discountAmountEntity) {
            $moneyValueTransfers[] = $this->mapDiscountAmountEntityToMoneyValueTransfer($discountAmountEntity, new MoneyValueTransfer());
        }

        return $moneyValueTransfers;
    }

    /**
     * @param \Propel\Runtime\Collection\Collection<\Orm\Zed\Discount\Persistence\SpyDiscountStore> $discountStoreEntities
     * @param \Generated\Shared\Transfer\StoreRelationTransfer $storeRelationTransfer
     *
     * @return \Generated\Shared\Transfer\StoreRelationTransfer
     */
    public function mapDiscountStoreEntitiesToStoreRelationTransfer(
        Collection $discountStoreEntities,
        StoreRelationTransfer $storeRelationTransfer
    ): StoreRelationTransfer {
        foreach ($discountStoreEntities as $discountStoreEntity) {
            $storeTransfer = $this->mapStoreEntityToStoreTransfer($discountStoreEntity->getSpyStore(), new StoreTransfer());
            $storeRelationTransfer
                ->addIdStores($storeTransfer->getIdStoreOrFail())
                ->addStores($storeTransfer);
        }

        return $storeRelationTransfer;
    }

    protected function mapDiscountAmountEntityToMoneyValueTransfer(
        SpyDiscountAmount $discountAmountEntity,
        MoneyValueTransfer $moneyValueTransfer
    ): MoneyValueTransfer {
        return $moneyValueTransfer
            ->fromArray($discountAmountEntity->toArray(), true)
            ->setIdEntity($discountAmountEntity->getIdDiscountAmount());
    }

    protected function mapStoreEntityToStoreTransfer(SpyStore $storeEntity, StoreTransfer $storeTransfer): StoreTransfer
    {
        return $storeTransfer->fromArray($storeEntity->toArray(), true);
    }
}
