<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\Persistence;

use Generated\Shared\Transfer\DiscountCalculatorTransfer;
use Generated\Shared\Transfer\DiscountConditionTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountGeneralTransfer;
use Generated\Shared\Transfer\DiscountVoucherTransfer;
use Orm\Zed\Discount\Persistence\SpyDiscount;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface;

class DiscountConfiguratorHydrate
{

    /**
     * @var \Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface
     */
    protected $discountQueryContainer;

    /**
     * DiscountConfiguratorHydrate constructor.
     */
    public function __construct(DiscountQueryContainerInterface $discountQueryContainer)
    {
        $this->discountQueryContainer = $discountQueryContainer;
    }

    /**
     * @param int $idDiscount
     *
     * @return \Generated\Shared\Transfer\DiscountConfiguratorTransfer
     */
    public function getByIdDiscount($idDiscount)
    {
        $discountEntity = $this->discountQueryContainer
            ->queryDiscount()
            ->findOneByIdDiscount($idDiscount);

        $discountConfigurator = $this->createDiscountConfiguratorTransfer();

        $discountGeneralTransfer = $this->hydrateGeneralDiscount($discountEntity);
        $discountConfigurator->setDiscountGeneral($discountGeneralTransfer);

        $discountCalculatorTransfer = $this->hydrateDiscountCalculator($discountEntity);
        $discountConfigurator->setDiscountCalculator($discountCalculatorTransfer);

        $discountConditionTransfer = $this->hydrateDiscountCondition($discountEntity);
        $discountConfigurator->setDiscountCondition($discountConditionTransfer);

        $this->hydrateDiscountVoucher($idDiscount, $discountEntity, $discountConfigurator);

        return $discountConfigurator;

    }

    /**
     * @param \Orm\Zed\Discount\Persistence\SpyDiscount $discountEntity
     *
     * @return \Generated\Shared\Transfer\DiscountGeneralTransfer
     */
    protected function hydrateGeneralDiscount(SpyDiscount $discountEntity)
    {
        $discountGeneralTransfer = new DiscountGeneralTransfer();
        $discountGeneralTransfer->fromArray($discountEntity->toArray(), true);

        $voucherType = $this->getVoucherType($discountEntity);
        $discountGeneralTransfer->setDiscountType($voucherType);

        $discountGeneralTransfer->setValidFrom($discountEntity->getValidFrom());
        $discountGeneralTransfer->setValidTo($discountEntity->getValidTo());
        return $discountGeneralTransfer;
    }

    /**
     * @param \Orm\Zed\Discount\Persistence\SpyDiscount $discountEntity
     *
     * @return \Generated\Shared\Transfer\DiscountCalculatorTransfer
     */
    protected function hydrateDiscountCalculator(SpyDiscount $discountEntity)
    {
        $discountCalculatorTransfer = new DiscountCalculatorTransfer();
        $discountCalculatorTransfer->fromArray($discountEntity->toArray(), true);
        return $discountCalculatorTransfer;
    }

    /**
     * @param \Orm\Zed\Discount\Persistence\SpyDiscount $discountEntity
     *
     * @return \Generated\Shared\Transfer\DiscountConditionTransfer
     */
    protected function hydrateDiscountCondition(SpyDiscount $discountEntity)
    {
        $discountConditionTransfer = new DiscountConditionTransfer();
        $discountConditionTransfer->fromArray($discountEntity->toArray(), true);

        return $discountConditionTransfer;
    }

    /**
     * @param integer $idDiscount
     * @param \Orm\Zed\Discount\Persistence\SpyDiscount $discountEntity
     * @param \Generated\Shared\Transfer\DiscountConfiguratorTransfer $discountConfigurator
     *
     * @return void
     */
    protected function hydrateDiscountVoucher(
        $idDiscount,
        SpyDiscount $discountEntity,
        DiscountConfiguratorTransfer $discountConfigurator
    ) {
        $voucherPoolEntity = $discountEntity->getVoucherPool();
        if ($voucherPoolEntity) {
            $discountVoucherTransfer = new DiscountVoucherTransfer();
            $discountVoucherTransfer->setIdDiscount($idDiscount);
            $discountVoucherTransfer->setFkDiscountVoucherPool($discountEntity->getFkDiscountVoucherPool());
            $discountConfigurator->setDiscountVoucher($discountVoucherTransfer);
        }
    }

    /**
     * @return \Generated\Shared\Transfer\DiscountConfiguratorTransfer
     */
    protected function createDiscountConfiguratorTransfer()
    {
        return new DiscountConfiguratorTransfer();
    }

    /**
     * @param \Orm\Zed\Discount\Persistence\SpyDiscount $discountEntity
     *
     * @return string
     */
    protected function getVoucherType(SpyDiscount $discountEntity)
    {
        $voucherType = DiscountConstants::TYPE_CART_RULE;
        if ($discountEntity->getFkDiscountVoucherPool()) {
            $voucherType = DiscountConstants::TYPE_CART_RULE;
        }

        return $voucherType;
    }

}
