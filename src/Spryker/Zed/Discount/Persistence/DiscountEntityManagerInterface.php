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

interface DiscountEntityManagerInterface
{
    public function createDiscount(DiscountTransfer $discountTransfer): DiscountTransfer;

    public function updateDiscount(DiscountTransfer $discountTransfer): DiscountTransfer;

    public function createDiscountAmount(DiscountMoneyAmountTransfer $discountMoneyAmountTransfer): DiscountMoneyAmountTransfer;

    public function updateDiscountAmount(DiscountMoneyAmountTransfer $discountMoneyAmountTransfer): void;

    public function createDiscountVoucherPool(DiscountGeneralTransfer $discountGeneralTransfer): int;

    /**
     * @param int $idDiscount
     * @param array<int> $storeIds
     *
     * @return void
     */
    public function createDiscountStoreRelations(int $idDiscount, array $storeIds): void;

    public function updateDiscountVoucherPool(DiscountGeneralTransfer $discountGeneralTransfer): int;

    public function deleteDiscountAmounts(DiscountAmountCriteriaTransfer $discountAmountCriteriaTransfer): void;

    /**
     * @param int $idDiscount
     * @param array<int> $storeIds
     *
     * @return void
     */
    public function deleteDiscountStoreRelations(int $idDiscount, array $storeIds): void;

    /**
     * @param array<int> $salesDiscountIds
     *
     * @return void
     */
    public function deleteSalesDiscountsBySalesDiscountIds(array $salesDiscountIds): void;

    /**
     * @param array<int> $salesDiscountIds
     *
     * @return void
     */
    public function deleteSalesDiscountCodesBySalesDiscountIds(array $salesDiscountIds): void;

    public function deleteDiscountVouchersByIdDiscountVoucherPool(int $idDiscountVoucherPool): void;

    public function deleteDiscountVoucherPoolByIdDiscountVoucherPool(int $idDiscountVoucherPool): void;
}
