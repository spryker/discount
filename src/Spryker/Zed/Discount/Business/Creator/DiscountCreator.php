<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\Creator;

use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountTransfer;
use Spryker\Zed\Discount\Business\Mapper\DiscountMapperInterface;
use Spryker\Zed\Discount\Persistence\DiscountEntityManagerInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

class DiscountCreator implements DiscountCreatorInterface
{
    use TransactionTrait;

    /**
     * @var \Spryker\Zed\Discount\Business\Mapper\DiscountMapperInterface
     */
    protected $discountMapper;

    /**
     * @var \Spryker\Zed\Discount\Persistence\DiscountEntityManagerInterface
     */
    protected $discountEntityManager;

    /**
     * @var \Spryker\Zed\Discount\Business\Creator\DiscountVoucherPoolCreatorInterface
     */
    protected $discountVoucherPoolCreator;

    public function __construct(
        DiscountMapperInterface $discountMapper,
        DiscountEntityManagerInterface $discountEntityManager,
        DiscountVoucherPoolCreatorInterface $discountVoucherPoolCreator
    ) {
        $this->discountMapper = $discountMapper;
        $this->discountEntityManager = $discountEntityManager;
        $this->discountVoucherPoolCreator = $discountVoucherPoolCreator;
    }

    public function createDiscount(DiscountConfiguratorTransfer $discountConfiguratorTransfer): DiscountConfiguratorTransfer
    {
        return $this->getTransactionHandler()->handleTransaction(function () use ($discountConfiguratorTransfer) {
            return $this->executeCreateDiscountTransaction($discountConfiguratorTransfer);
        });
    }

    protected function executeCreateDiscountTransaction(DiscountConfiguratorTransfer $discountConfiguratorTransfer): DiscountConfiguratorTransfer
    {
        $idDiscountVoucherPool = $this->discountVoucherPoolCreator->createDiscountVoucherPool($discountConfiguratorTransfer);

        $discountTransfer = $this->discountMapper->mapDiscountConfiguratorTransferToDiscountTransfer(
            $discountConfiguratorTransfer,
            new DiscountTransfer(),
        );
        $discountTransfer->setFkDiscountVoucherPool($idDiscountVoucherPool);
        $discountTransfer = $this->discountEntityManager->createDiscount($discountTransfer);

        return $this->discountMapper->mapDiscountTransferToDiscountConfiguratorTransfer(
            $discountTransfer,
            $discountConfiguratorTransfer,
        );
    }
}
