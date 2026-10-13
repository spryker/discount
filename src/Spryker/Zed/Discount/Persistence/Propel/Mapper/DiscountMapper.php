<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\DiscountCalculatorTransfer;
use Generated\Shared\Transfer\DiscountConditionTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountGeneralTransfer;
use Generated\Shared\Transfer\DiscountMoneyAmountTransfer;
use Generated\Shared\Transfer\DiscountTransfer;
use Generated\Shared\Transfer\DiscountVoucherTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Orm\Zed\Discount\Persistence\SpyDiscount;
use Orm\Zed\Discount\Persistence\SpyDiscountAmount;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucher;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucherPool;
use Orm\Zed\Store\Persistence\SpyStore;
use Propel\Runtime\Collection\Collection;
use Spryker\Shared\Discount\DiscountConstants;

class DiscountMapper
{
    public const string VIRTUAL_COLUMN_ID_DISCOUNT = 'idDiscount';

    /**
     * @var string
     */
    protected const DATE_TIME_FORMAT = 'Y-d-m H:i:s';

    protected const string DISCOUNT_CONFIGURATOR_DATE_TIME_FORMAT = 'Y-m-d H:i:s';

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

    public function mapDiscountVoucherEntityToDiscountVoucherTransfer(
        SpyDiscountVoucher $discountVoucherEntity,
        DiscountVoucherTransfer $discountVoucherTransfer
    ): DiscountVoucherTransfer {
        /** @var string|null $createdAt */
        $createdAt = $discountVoucherEntity->getCreatedAt(static::DISCOUNT_CONFIGURATOR_DATE_TIME_FORMAT);

        $discountVoucherTransfer
            ->fromArray($discountVoucherEntity->toArray(), true)
            ->setCreatedAt($createdAt);

        if ($discountVoucherEntity->hasVirtualColumn(static::VIRTUAL_COLUMN_ID_DISCOUNT)) {
            $discountVoucherTransfer->setIdDiscount((int)$discountVoucherEntity->getVirtualColumn(static::VIRTUAL_COLUMN_ID_DISCOUNT));
        }

        return $discountVoucherTransfer;
    }

    public function mapDiscountEntityToDiscountConfiguratorTransfer(
        SpyDiscount $discountEntity,
        DiscountConfiguratorTransfer $discountConfiguratorTransfer
    ): DiscountConfiguratorTransfer {
        $discountEntityData = $discountEntity->toArray();

        $discountConfiguratorTransfer
            ->setDiscountGeneral(
                (new DiscountGeneralTransfer())
                    ->fromArray($discountEntityData, true)
                    ->setValidFrom($discountEntity->getValidFrom(static::DISCOUNT_CONFIGURATOR_DATE_TIME_FORMAT))
                    ->setValidTo($discountEntity->getValidTo(static::DISCOUNT_CONFIGURATOR_DATE_TIME_FORMAT))
                    ->setCreatedAt($discountEntity->getCreatedAt(static::DISCOUNT_CONFIGURATOR_DATE_TIME_FORMAT)),
            )
            ->setDiscountCalculator(
                (new DiscountCalculatorTransfer())
                    ->fromArray($discountEntityData, true)
                    ->setCollectorStrategyType(DiscountConstants::DISCOUNT_COLLECTOR_STRATEGY_QUERY_STRING),
            )
            ->setDiscountCondition(
                (new DiscountConditionTransfer())->fromArray($discountEntityData, true),
            );

        if ($discountEntity->getFkDiscountVoucherPool() === null) {
            return $discountConfiguratorTransfer;
        }

        return $discountConfiguratorTransfer->setDiscountVoucher(
            (new DiscountVoucherTransfer())
                ->setIdDiscount($discountEntity->getIdDiscount())
                ->setFkDiscountVoucherPool($discountEntity->getFkDiscountVoucherPool()),
        );
    }

    /**
     * @param \Propel\Runtime\Collection\Collection<\Orm\Zed\Discount\Persistence\SpyDiscountStore> $discountStoreEntities
     *
     * @return array<int, \Generated\Shared\Transfer\StoreRelationTransfer>
     */
    public function mapDiscountStoreEntitiesToStoreRelationTransfersIndexedByIdDiscount(Collection $discountStoreEntities): array
    {
        $storeRelationTransfers = [];

        foreach ($discountStoreEntities as $discountStoreEntity) {
            $idDiscount = $discountStoreEntity->getFkDiscount();
            $storeRelationTransfers[$idDiscount] ??= (new StoreRelationTransfer())->setIdEntity($idDiscount);
            $storeTransfer = $this->mapStoreEntityToStoreTransfer($discountStoreEntity->getSpyStore(), new StoreTransfer());

            $storeRelationTransfers[$idDiscount]
                ->addIdStores($storeTransfer->getIdStoreOrFail())
                ->addStores($storeTransfer);
        }

        return $storeRelationTransfers;
    }

    /**
     * @param \Propel\Runtime\Collection\Collection<\Orm\Zed\Discount\Persistence\SpyDiscountAmount> $discountAmountEntities
     *
     * @return array<int, array<int, \Generated\Shared\Transfer\MoneyValueTransfer>>
     */
    public function mapDiscountAmountEntitiesToMoneyValueTransfersIndexedByIdDiscount(Collection $discountAmountEntities): array
    {
        $moneyValueTransfers = [];

        foreach ($discountAmountEntities as $discountAmountEntity) {
            $moneyValueTransfers[$discountAmountEntity->getFkDiscount()][] = $this
                ->mapDiscountAmountEntityToMoneyValueTransfer($discountAmountEntity, new MoneyValueTransfer())
                ->setCurrency(
                    (new CurrencyTransfer())->fromArray($discountAmountEntity->getCurrency()->toArray(), true),
                );
        }

        return $moneyValueTransfers;
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
