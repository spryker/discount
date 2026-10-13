<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator;

use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\ErrorCollectionTransfer;

interface DiscountConfiguratorValidatorRuleInterface
{
    public function validate(DiscountConfiguratorTransfer $discountConfiguratorTransfer): ErrorCollectionTransfer;
}
