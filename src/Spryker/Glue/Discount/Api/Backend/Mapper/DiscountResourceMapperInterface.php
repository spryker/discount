<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Mapper;

use Generated\Api\Backend\DiscountsBackendResource;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountRuleGroupTransfer;

interface DiscountResourceMapperInterface
{
    /**
     * @param array<string, \Generated\Shared\Transfer\DiscountRuleGroupTransfer|null> $discountRuleGroupTransfersByType Keyed by `\Spryker\Glue\Discount\DiscountConfig::QUERY_STRING_TYPE_*`.
     */
    public function mapDiscountConfiguratorTransferToDiscountsBackendResource(
        DiscountConfiguratorTransfer $discountConfiguratorTransfer,
        array $discountRuleGroupTransfersByType,
        DiscountsBackendResource $discountsBackendResource
    ): DiscountsBackendResource;

    /**
     * @param array<int, \Generated\Shared\Transfer\StoreTransfer> $storeTransfers
     * @param array<int, \Generated\Shared\Transfer\CurrencyTransfer> $currencyTransfers
     * @param array<string, string|null> $queryStringsByType Keyed by `\Spryker\Glue\Discount\DiscountConfig::QUERY_STRING_TYPE_*`.
     */
    public function mapDiscountsBackendResourceToDiscountConfiguratorTransfer(
        DiscountsBackendResource $discountsBackendResource,
        DiscountConfiguratorTransfer $discountConfiguratorTransfer,
        array $storeTransfers,
        array $currencyTransfers,
        array $queryStringsByType
    ): DiscountConfiguratorTransfer;

    /**
     * @param array<string, mixed> $ruleGroupData
     */
    public function mapRuleGroupDataToDiscountRuleGroupTransfer(
        array $ruleGroupData,
        DiscountRuleGroupTransfer $discountRuleGroupTransfer
    ): DiscountRuleGroupTransfer;
}
