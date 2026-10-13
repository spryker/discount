<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Business\Reader;

use ArrayObject;
use Generated\Shared\Transfer\DiscountConfiguratorCollectionTransfer;
use Generated\Shared\Transfer\DiscountCriteriaTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Spryker\Zed\Discount\Business\Calculator\DiscountAmountTransformerInterface;
use Spryker\Zed\Discount\DiscountConfig;
use Spryker\Zed\Discount\Exception\DiscountUuidMissingException;
use Spryker\Zed\Discount\Exception\DiscountUuidNotEnabledException;
use Spryker\Zed\Discount\Persistence\DiscountRepositoryInterface;

class DiscountReader implements DiscountReaderInterface
{
    protected const string MESSAGE_UUID_NOT_ENABLED = 'Discount uuids are disabled: `%s::isDiscountUuidEnabled()` returns false. Discounts are addressed by uuid, so enable it in the project `DiscountConfig`, run `console propel:install` to add the `spy_discount.uuid` column, and run `console uuid:generate Discount spy_discount` for the existing discounts.';

    protected const string MESSAGE_UUID_MISSING = 'Discounts with the ids %s have no uuid. Run `console uuid:generate Discount spy_discount` to generate the missing uuids.';

    /**
     * @param array<\Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountConfiguratorCollectionExpanderPluginInterface> $discountConfiguratorCollectionExpanderPlugins
     */
    public function __construct(
        protected DiscountRepositoryInterface $discountRepository,
        protected DiscountAmountTransformerInterface $discountAmountTransformer,
        protected DiscountConfig $discountConfig,
        protected array $discountConfiguratorCollectionExpanderPlugins
    ) {
    }

    /**
     * @throws \Spryker\Zed\Discount\Exception\DiscountUuidNotEnabledException
     */
    public function getDiscountConfiguratorCollection(DiscountCriteriaTransfer $discountCriteriaTransfer): DiscountConfiguratorCollectionTransfer
    {
        if (!$this->discountConfig->isDiscountUuidEnabled()) {
            throw new DiscountUuidNotEnabledException(sprintf(static::MESSAGE_UUID_NOT_ENABLED, DiscountConfig::class));
        }

        $discountConfiguratorCollectionTransfer = $this->expandDiscountConfigurators(
            $this->discountRepository->getDiscountConfiguratorCollection($discountCriteriaTransfer),
        );
        $this->assertEveryDiscountHasUuid($discountConfiguratorCollectionTransfer);

        return $discountConfiguratorCollectionTransfer;
    }

    /**
     * @throws \Spryker\Zed\Discount\Exception\DiscountUuidMissingException
     */
    protected function assertEveryDiscountHasUuid(DiscountConfiguratorCollectionTransfer $discountConfiguratorCollectionTransfer): void
    {
        $discountIdsWithoutUuid = [];

        foreach ($discountConfiguratorCollectionTransfer->getDiscountConfigurators() as $discountConfiguratorTransfer) {
            $discountGeneralTransfer = $discountConfiguratorTransfer->getDiscountGeneralOrFail();

            if ($discountGeneralTransfer->getUuid() === null || $discountGeneralTransfer->getUuid() === '') {
                $discountIdsWithoutUuid[] = $discountGeneralTransfer->getIdDiscountOrFail();
            }
        }

        if ($discountIdsWithoutUuid === []) {
            return;
        }

        throw new DiscountUuidMissingException(sprintf(static::MESSAGE_UUID_MISSING, implode(', ', $discountIdsWithoutUuid)));
    }

    protected function expandDiscountConfigurators(
        DiscountConfiguratorCollectionTransfer $discountConfiguratorCollectionTransfer
    ): DiscountConfiguratorCollectionTransfer {
        $discountIds = [];

        foreach ($discountConfiguratorCollectionTransfer->getDiscountConfigurators() as $discountConfiguratorTransfer) {
            $discountIds[] = $discountConfiguratorTransfer->getDiscountGeneralOrFail()->getIdDiscountOrFail();
        }

        if ($discountIds === []) {
            return $discountConfiguratorCollectionTransfer;
        }

        $storeRelationTransfers = $this->discountRepository->getDiscountStoreRelationsByDiscountIds($discountIds);
        $moneyValueTransfers = $this->discountRepository->getDiscountAmountsByDiscountIds($discountIds);
        $expandedDiscountConfiguratorTransfers = new ArrayObject();

        foreach ($discountConfiguratorCollectionTransfer->getDiscountConfigurators() as $discountConfiguratorTransfer) {
            $idDiscount = $discountConfiguratorTransfer->getDiscountGeneralOrFail()->getIdDiscountOrFail();

            $discountConfiguratorTransfer->getDiscountGeneralOrFail()->setStoreRelation(
                $storeRelationTransfers[$idDiscount] ?? (new StoreRelationTransfer())->setIdEntity($idDiscount),
            );
            $discountConfiguratorTransfer->getDiscountCalculatorOrFail()->setMoneyValueCollection(
                new ArrayObject($moneyValueTransfers[$idDiscount] ?? []),
            );

            $this->discountAmountTransformer->transformAmountFromPersistence(
                $discountConfiguratorTransfer->getDiscountCalculatorOrFail(),
            );

            $expandedDiscountConfiguratorTransfers->append($discountConfiguratorTransfer);
        }

        return $this->executeDiscountConfiguratorCollectionExpanderPlugins(
            $discountConfiguratorCollectionTransfer->setDiscountConfigurators($expandedDiscountConfiguratorTransfers),
        );
    }

    protected function executeDiscountConfiguratorCollectionExpanderPlugins(
        DiscountConfiguratorCollectionTransfer $discountConfiguratorCollectionTransfer
    ): DiscountConfiguratorCollectionTransfer {
        foreach ($this->discountConfiguratorCollectionExpanderPlugins as $discountConfiguratorCollectionExpanderPlugin) {
            $discountConfiguratorCollectionTransfer = $discountConfiguratorCollectionExpanderPlugin->expand($discountConfiguratorCollectionTransfer);
        }

        return $discountConfiguratorCollectionTransfer;
    }
}
