<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Provider;

use Generated\Api\Backend\DiscountOptionsBackendResource;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\Glue\Discount\Api\Backend\Mapper\DiscountOptionsResourceMapperInterface;
use Spryker\Glue\Discount\DiscountConfig;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;

class DiscountOptionsBackendProvider extends AbstractBackendProvider
{
    public function __construct(
        protected DiscountFacadeInterface $discountFacade,
        protected DiscountOptionsResourceMapperInterface $discountOptionsResourceMapper
    ) {
    }

    protected function provideItem(): ?object
    {
        $discountOptionsBackendResource = $this->discountOptionsResourceMapper
            ->mapDiscountConfigurationOptionsTransferToDiscountOptionsBackendResource(
                $this->discountFacade->getDiscountConfigurationOptions(),
                new DiscountOptionsBackendResource(),
            );

        return $discountOptionsBackendResource->setUuid(DiscountConfig::RESOURCE_DISCOUNT_OPTIONS);
    }
}
