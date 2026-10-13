<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator;

use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\ErrorCollectionTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Spryker\Shared\Discount\DiscountConfig;
use Spryker\Zed\Discount\Dependency\Facade\DiscountToStoreFacadeInterface;

class StoreRelationDiscountConfiguratorValidatorRule implements DiscountConfiguratorValidatorRuleInterface
{
    public const string ENTITY_IDENTIFIER_STORES = 'discountGeneral.storeRelation.stores';

    protected const string ENTITY_IDENTIFIER_STORE_PATTERN = '%s.%d';

    protected const string TRANSLATION_PARAMETER_NAME = '%name%';

    public function __construct(protected DiscountToStoreFacadeInterface $storeFacade)
    {
    }

    public function validate(DiscountConfiguratorTransfer $discountConfiguratorTransfer): ErrorCollectionTransfer
    {
        $errorCollectionTransfer = new ErrorCollectionTransfer();
        $storeRelationTransfer = $discountConfiguratorTransfer->getDiscountGeneral()?->getStoreRelation();

        if ($storeRelationTransfer === null) {
            return $errorCollectionTransfer;
        }

        $existingStoreNames = $this->getExistingStoreNames();
        $index = 0;

        foreach ($storeRelationTransfer->getStores() as $storeTransfer) {
            $storeName = $storeTransfer->getName();

            if ($storeName === null || !in_array($storeName, $existingStoreNames, true)) {
                $errorCollectionTransfer->addError(
                    (new ErrorTransfer())
                        ->setMessage(DiscountConfig::TRANSLATION_KEY_STORE_UNKNOWN)
                        ->setEntityIdentifier(
                            sprintf(static::ENTITY_IDENTIFIER_STORE_PATTERN, static::ENTITY_IDENTIFIER_STORES, $index),
                        )
                        ->setParameters([static::TRANSLATION_PARAMETER_NAME => (string)$storeName]),
                );
            }

            $index++;
        }

        return $errorCollectionTransfer;
    }

    /**
     * @return array<int, string>
     */
    protected function getExistingStoreNames(): array
    {
        $storeNames = [];

        foreach ($this->storeFacade->getAllStores() as $storeTransfer) {
            $storeNames[] = $storeTransfer->getNameOrFail();
        }

        return $storeNames;
    }
}
