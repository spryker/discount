<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount;

use Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountRuleWithValueOptionsPluginInterface;

abstract class DiscountRuleWithValueOptionsPlugin implements DiscountRuleWithValueOptionsPluginInterface
{
    abstract public function getFieldName(): string;
}
