<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Dependency\Plugin;

interface DiscountRuleWithAttributesPluginInterface
{

    /**
     * @api
     *
     * @return string[]
     */
    public function getAttributeTypes();

}
