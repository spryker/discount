<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Persistence;

use Orm\Zed\Discount\Persistence\SpyDiscountCollectorQuery;
use Orm\Zed\Discount\Persistence\SpyDiscountDecisionRuleQuery;
use Orm\Zed\Discount\Persistence\SpyDiscountQuery;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucherPoolCategoryQuery;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucherPoolQuery;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucherQuery;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;

/**
 * @method \Spryker\Zed\Discount\DiscountConfig getConfig()
 * @method \Spryker\Zed\Discount\Persistence\DiscountQueryContainer getQueryContainer()
 */
class DiscountPersistenceFactory extends AbstractPersistenceFactory
{

    /**
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountVoucherQuery
     */
    public function createDiscountVoucherQuery()
    {
        return SpyDiscountVoucherQuery::create();
    }

    /**
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountDecisionRuleQuery
     */
    public function createDiscountDecisionRuleQuery()
    {
        return SpyDiscountDecisionRuleQuery::create();
    }

    /**
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountQuery
     */
    public function createDiscountQuery()
    {
        return SpyDiscountQuery::create();
    }

    /**
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountVoucherPoolQuery
     */
    public function createDiscountVoucherPoolQuery()
    {
        return SpyDiscountVoucherPoolQuery::create();
    }

    /**
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountCollectorQuery
     */
    public function createDiscountCollectorQuery()
    {
        return SpyDiscountCollectorQuery::create();
    }

    /**
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountVoucherPoolCategoryQuery
     */
    public function createDiscountVoucherPoolCategoryQuery()
    {
        return SpyDiscountVoucherPoolCategoryQuery::create();
    }

}
