<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Persistence\Propel\QueryBuilder;

use Generated\Shared\Transfer\DiscountVoucherConditionsTransfer;
use Orm\Zed\Discount\Persistence\Map\SpyDiscountVoucherTableMap;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucherQuery;

class DiscountVoucherQueryBuilder
{
    protected const string DISCOUNT_VOUCHER_CODE_LIKE_PATTERN = '%%%s%%';

    public function applyDiscountVoucherConditions(
        SpyDiscountVoucherQuery $discountVoucherQuery,
        DiscountVoucherConditionsTransfer $discountVoucherConditionsTransfer
    ): SpyDiscountVoucherQuery {
        if ($discountVoucherConditionsTransfer->getDiscountVoucherIds() !== []) {
            $discountVoucherQuery->filterByIdDiscountVoucher_In($discountVoucherConditionsTransfer->getDiscountVoucherIds());
        }

        if ($discountVoucherConditionsTransfer->getCodes() !== []) {
            $discountVoucherQuery->filterByCode_In($discountVoucherConditionsTransfer->getCodes());
        }

        if ($discountVoucherConditionsTransfer->getCode() !== null) {
            $discountVoucherQuery->where(
                'LOWER(' . SpyDiscountVoucherTableMap::COL_CODE . ') LIKE ?',
                sprintf(static::DISCOUNT_VOUCHER_CODE_LIKE_PATTERN, mb_strtolower($discountVoucherConditionsTransfer->getCodeOrFail())),
            );
        }

        if ($discountVoucherConditionsTransfer->getVoucherBatches() !== []) {
            $discountVoucherQuery->filterByVoucherBatch_In($discountVoucherConditionsTransfer->getVoucherBatches());
        }

        if ($discountVoucherConditionsTransfer->getIsActive() !== null) {
            $discountVoucherQuery->filterByIsActive($discountVoucherConditionsTransfer->getIsActiveOrFail());
        }

        return $discountVoucherQuery;
    }
}
