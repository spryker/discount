<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\Discount;

use Spryker\Zed\Discount\Dependency\Plugin\DiscountRuleWithAttributesPluginInterface;
use Spryker\Zed\DiscountExtension\Dependency\Plugin\DecisionRulePluginInterface;

abstract class DiscountRuleWithAttributesPlugin implements DecisionRulePluginInterface, DiscountRuleWithAttributesPluginInterface
{
}
