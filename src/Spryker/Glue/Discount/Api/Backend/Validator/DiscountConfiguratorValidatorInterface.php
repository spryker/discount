<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Validator;

use Generated\Shared\Transfer\DiscountConfiguratorTransfer;

interface DiscountConfiguratorValidatorInterface
{
    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    public function assertIsValid(DiscountConfiguratorTransfer $discountConfiguratorTransfer): void;
}
