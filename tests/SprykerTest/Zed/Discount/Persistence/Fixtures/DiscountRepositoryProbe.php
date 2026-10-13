<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\Discount\Persistence\Fixtures;

use Generated\Shared\Transfer\DiscountConditionsTransfer;
use Orm\Zed\Discount\Persistence\SpyDiscountQuery;
use Spryker\Zed\Discount\Persistence\DiscountRepository;

class DiscountRepositoryProbe extends DiscountRepository
{
    public function exposeBuildDiscountQueryByConditions(
        DiscountConditionsTransfer $discountConditionsTransfer
    ): SpyDiscountQuery {
        return $this->buildDiscountQueryByConditions($discountConditionsTransfer);
    }
}
