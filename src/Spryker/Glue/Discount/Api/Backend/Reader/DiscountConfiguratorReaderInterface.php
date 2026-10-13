<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Reader;

use Generated\Shared\Transfer\DiscountConfiguratorCollectionTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountCriteriaTransfer;

interface DiscountConfiguratorReaderInterface
{
    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    public function getDiscountConfiguratorByUuid(string $uuid): DiscountConfiguratorTransfer;

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     * @throws \Spryker\Zed\Discount\Exception\DiscountUuidNotEnabledException
     * @throws \Spryker\Zed\Discount\Exception\DiscountUuidMissingException
     */
    public function getDiscountConfiguratorCollection(DiscountCriteriaTransfer $discountCriteriaTransfer): DiscountConfiguratorCollectionTransfer;

    /**
     * @throws \Spryker\Zed\Discount\Exception\DiscountUuidNotEnabledException
     * @throws \Spryker\Zed\Discount\Exception\DiscountUuidMissingException
     */
    public function assertDiscountUuidsAvailable(): void;

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    public function getDiscountConfiguratorByIdDiscount(int $idDiscount): DiscountConfiguratorTransfer;

    /**
     * @return array<string, \Generated\Shared\Transfer\DiscountRuleGroupTransfer|null>
     */
    public function getDiscountRuleGroupTransfersByType(DiscountConfiguratorTransfer $discountConfiguratorTransfer): array;
}
