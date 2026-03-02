<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Dependency\External;

use Symfony\Component\Validator\Validator\ValidatorInterface;

interface DiscountToValidationAdapterInterface
{
    public function createValidator(): ValidatorInterface;
}
