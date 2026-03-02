<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Persistence;

use Generated\Shared\Transfer\StoreRelationTransfer;

interface DiscountRepositoryInterface
{
    /**
     * @param array<string> $codes
     *
     * @return array<string>
     */
    public function findVoucherCodesExceedingUsageLimit(array $codes): array;

    /**
     * @deprecated Will be removed in the next major without replacement.
     *
     * @return bool
     */
    public function hasPriorityField(): bool;

    /**
     * @param int $idDiscount
     *
     * @return array<\Generated\Shared\Transfer\MoneyValueTransfer>
     */
    public function getDiscountAmountCollectionForDiscount(int $idDiscount): array;

    public function getDiscountStoreRelations(int $idDiscount): StoreRelationTransfer;

    public function discountExists(int $idDiscount): bool;

    public function discountVoucherPoolExists(int $idDiscount): bool;

    /**
     * @param array<int> $salesOrderIds
     *
     * @return array<string>
     */
    public function getUsedSalesDiscountCodesBySalesOrderIds(array $salesOrderIds): array;

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
    ): array;
}
