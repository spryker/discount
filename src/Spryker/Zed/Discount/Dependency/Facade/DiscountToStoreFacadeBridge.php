<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Dependency\Facade;

use Generated\Shared\Transfer\StoreTransfer;

class DiscountToStoreFacadeBridge implements DiscountToStoreFacadeInterface
{
    /**
     * @var \Spryker\Zed\Store\Business\StoreFacadeInterface
     */
    protected $storeFacade;

    /**
     * @param \Spryker\Zed\Store\Business\StoreFacadeInterface $storeFacade
     */
    public function __construct($storeFacade)
    {
        $this->storeFacade = $storeFacade;
    }

    /**
     * @param string $storeName
     *
     * @return \Generated\Shared\Transfer\StoreTransfer
     */
    public function getStoreByName($storeName): StoreTransfer
    {
        return $this->storeFacade->getStoreByName($storeName);
    }

    /**
     * @return bool
     */
    public function isDynamicStoreEnabled(): bool
    {
        return $this->storeFacade->isDynamicStoreEnabled();
    }
}
