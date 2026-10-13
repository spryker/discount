<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Mapper;

use Generated\Api\Backend\DiscountOptionsBackendResource;
use Generated\Shared\Transfer\DiscountConfigurationOptionsTransfer;

interface DiscountOptionsResourceMapperInterface
{
    public function mapDiscountConfigurationOptionsTransferToDiscountOptionsBackendResource(
        DiscountConfigurationOptionsTransfer $discountConfigurationOptionsTransfer,
        DiscountOptionsBackendResource $discountOptionsBackendResource
    ): DiscountOptionsBackendResource;
}
