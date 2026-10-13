<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Business\QueryString\Converter;

use Generated\Shared\Transfer\DiscountRuleGroupResponseTransfer;

interface DiscountRuleGroupBuilderInterface
{
    public function buildDiscountRuleGroupFromQueryString(string $type, string $queryString): DiscountRuleGroupResponseTransfer;
}
