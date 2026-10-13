<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Business\Reader;

use Generated\Shared\Transfer\DiscountConfiguratorCollectionTransfer;
use Generated\Shared\Transfer\DiscountCriteriaTransfer;

interface DiscountReaderInterface
{
    public function getDiscountConfiguratorCollection(DiscountCriteriaTransfer $discountCriteriaTransfer): DiscountConfiguratorCollectionTransfer;
}
