<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Discount\Business\Writer;

use Generated\Shared\Transfer\VoucherTransfer;
use Propel\Runtime\Exception\PropelException;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucher;

class DiscountVoucherWriter extends AbstractWriter
{

    /**
     * @param VoucherTransfer $discountVoucherTransfer
     *
     * @return SpyDiscountVoucher
     */
    public function create(VoucherTransfer $discountVoucherTransfer)
    {
        $discountVoucherEntity = new SpyDiscountVoucher();
        $discountVoucherEntity->fromArray($discountVoucherTransfer->toArray());
        $discountVoucherEntity->save();

        return $discountVoucherEntity;
    }

    /**
     * @param VoucherTransfer $discountVoucherTransfer
     *
     * @throws PropelException
     *
     * @return SpyDiscountVoucher
     */
    public function update(VoucherTransfer $discountVoucherTransfer)
    {
        $queryContainer = $this->getQueryContainer();
        $discountVoucherEntity = $queryContainer
            ->queryDiscountVoucher()
            ->findPk($discountVoucherTransfer->getIdDiscountVoucher());
        $discountVoucherEntity->fromArray($discountVoucherTransfer->toArray());
        $discountVoucherEntity->save();

        return $discountVoucherEntity;
    }

}
