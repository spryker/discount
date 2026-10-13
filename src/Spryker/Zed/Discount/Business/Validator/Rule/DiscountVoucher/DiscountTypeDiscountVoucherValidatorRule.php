<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Business\Validator\Rule\DiscountVoucher;

use Generated\Shared\Transfer\DiscountConditionsTransfer;
use Generated\Shared\Transfer\DiscountCriteriaTransfer;
use Generated\Shared\Transfer\DiscountVoucherTransfer;
use Generated\Shared\Transfer\ErrorCollectionTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Spryker\Shared\Discount\DiscountConfig;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Discount\Persistence\DiscountRepositoryInterface;

class DiscountTypeDiscountVoucherValidatorRule implements DiscountVoucherValidatorRuleInterface
{
    public const string ENTITY_IDENTIFIER_ID_DISCOUNT = 'discountVoucher.idDiscount';

    protected const string TRANSLATION_PARAMETER_ID_DISCOUNT = '%idDiscount%';

    public function __construct(protected DiscountRepositoryInterface $discountRepository)
    {
    }

    public function validate(DiscountVoucherTransfer $discountVoucherTransfer): ErrorCollectionTransfer
    {
        $errorCollectionTransfer = new ErrorCollectionTransfer();
        $idDiscount = $discountVoucherTransfer->getIdDiscount();

        if ($idDiscount === null) {
            return $errorCollectionTransfer;
        }

        if ($this->findDiscountType((int)$idDiscount) === DiscountConstants::TYPE_VOUCHER) {
            return $errorCollectionTransfer;
        }

        return $errorCollectionTransfer->addError(
            (new ErrorTransfer())
                ->setMessage(DiscountConfig::TRANSLATION_KEY_VOUCHER_DISCOUNT_TYPE)
                ->setEntityIdentifier(static::ENTITY_IDENTIFIER_ID_DISCOUNT)
                ->setParameters([static::TRANSLATION_PARAMETER_ID_DISCOUNT => (int)$idDiscount]),
        );
    }

    protected function findDiscountType(int $idDiscount): ?string
    {
        $discountConfiguratorCollectionTransfer = $this->discountRepository->getDiscountConfiguratorCollection(
            (new DiscountCriteriaTransfer())->setDiscountConditions(
                (new DiscountConditionsTransfer())->addIdDiscount($idDiscount),
            ),
        );

        foreach ($discountConfiguratorCollectionTransfer->getDiscountConfigurators() as $discountConfiguratorTransfer) {
            return $discountConfiguratorTransfer->getDiscountGeneralOrFail()->getDiscountType();
        }

        return null;
    }
}
