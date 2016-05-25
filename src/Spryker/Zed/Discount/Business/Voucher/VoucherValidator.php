<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\Voucher;

use Generated\Shared\Transfer\MessageTransfer;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucher;
use Spryker\Zed\Discount\Dependency\Facade\DiscountToMessengerInterface;
use Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface;

class VoucherValidator
{

    const REASON_VOUCHER_CODE_NOT_ACTIVE = 'discount.voucher_code.not_active';
    const REASON_VOUCHER_CODE_POOL_MISSING = 'discount.voucher_code.pool_not_set';
    const REASON_VOUCHER_CODE_POOL_NOT_ACTIVE = 'discount.voucher_code.pool_not_active';
    const REASON_VOUCHER_CODE_LIMIT_REACHED = 'discount.voucher_code.usage_limit.reached';

    /**
     * @var \Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface
     */
    protected $discountQueryContainer;

    /**
     * @var \Spryker\Zed\Discount\Dependency\Facade\DiscountToMessengerInterface
     */
    protected $messengerFacade;

    /**
     * @param \Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface $discountQueryContainer
     * @param \Spryker\Zed\Discount\Dependency\Facade\DiscountToMessengerInterface $messengerFacade
     */
    public function __construct(
        DiscountQueryContainerInterface $discountQueryContainer,
        DiscountToMessengerInterface $messengerFacade
    ) {

        $this->discountQueryContainer = $discountQueryContainer;
        $this->messengerFacade = $messengerFacade;
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    public function isUsable($code)
    {
        $discountVoucherEntity = $this->discountQueryContainer
            ->queryVoucher($code)
            ->findOne();

        return $this->validateDiscountVoucher($discountVoucherEntity);
    }

    /**
     * @param \Orm\Zed\Discount\Persistence\SpyDiscountVoucher $discountVoucherEntity
     *
     * @return array|string[]
     */
    protected function validateDiscountVoucher(SpyDiscountVoucher $discountVoucherEntity)
    {
        if (!$discountVoucherEntity->getIsActive()) {
            $this->addMessage(self::REASON_VOUCHER_CODE_NOT_ACTIVE);
            return false;
        }

        $voucherPoolEntity = $discountVoucherEntity->getVoucherPool();
        if (!$voucherPoolEntity) {
            $this->addMessage(self::REASON_VOUCHER_CODE_POOL_MISSING);
            return false;
        }

        if (!$voucherPoolEntity->getIsActive()) {
            $this->addMessage(self::REASON_VOUCHER_CODE_POOL_NOT_ACTIVE);
            return false;
        }

        if (!$this->isValidNumberOfUses($discountVoucherEntity)) {
            $this->addMessage(self::REASON_VOUCHER_CODE_LIMIT_REACHED);
            return false;
        }

        return true;
    }

    /**
     * @param string $message
     *
     * @return void
     */
    protected function addMessage($message)
    {
        $messageTransfer = new MessageTransfer();
        $messageTransfer->setValue($message);

        $this->messengerFacade->addErrorMessage($messageTransfer);
    }


    /**
     * @param \Orm\Zed\Discount\Persistence\SpyDiscountVoucher $discountVoucherEntity
     *
     * @return bool
     */
    protected function isValidNumberOfUses(SpyDiscountVoucher $discountVoucherEntity)
    {
        if ($discountVoucherEntity->getMaxNumberOfUses() > 0 &&
            $discountVoucherEntity->getNumberOfUses() >= $discountVoucherEntity->getMaxNumberOfUses()) {
            return false;
        }

        return true;
    }

}
