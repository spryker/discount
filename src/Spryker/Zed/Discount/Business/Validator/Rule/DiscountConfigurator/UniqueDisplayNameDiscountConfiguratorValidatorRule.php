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
use Spryker\Zed\Discount\Persistence\DiscountRepositoryInterface;

class UniqueDisplayNameDiscountConfiguratorValidatorRule implements DiscountConfiguratorValidatorRuleInterface
{
    public const string ENTITY_IDENTIFIER_DISPLAY_NAME = 'discountGeneral.displayName';

    public function __construct(protected DiscountRepositoryInterface $discountRepository)
    {
    }

    public function validate(DiscountConfiguratorTransfer $discountConfiguratorTransfer): ErrorCollectionTransfer
    {
        $errorCollectionTransfer = new ErrorCollectionTransfer();
        $discountGeneralTransfer = $discountConfiguratorTransfer->getDiscountGeneral();
        $displayName = $discountGeneralTransfer?->getDisplayName();

        if ($displayName === null || $displayName === '') {
            return $errorCollectionTransfer;
        }

        $idDiscount = $this->discountRepository->findIdDiscountByDisplayName($displayName);

        if ($idDiscount === null || $idDiscount === $discountGeneralTransfer->getIdDiscount()) {
            return $errorCollectionTransfer;
        }

        return $errorCollectionTransfer->addError(
            (new ErrorTransfer())
                ->setMessage(DiscountConfig::TRANSLATION_KEY_DISPLAY_NAME_NOT_UNIQUE)
                ->setEntityIdentifier(static::ENTITY_IDENTIFIER_DISPLAY_NAME),
        );
    }
}
