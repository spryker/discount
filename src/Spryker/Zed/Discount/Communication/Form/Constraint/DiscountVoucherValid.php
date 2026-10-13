<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Communication\Form\Constraint;

use Spryker\Zed\Discount\Business\DiscountFacadeInterface;
use Spryker\Zed\Discount\Dependency\Facade\DiscountToTranslatorFacadeInterface;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

class DiscountVoucherValid extends SymfonyConstraint
{
    public const string OPTION_DISCOUNT_FACADE = 'discountFacade';

    public const string OPTION_TRANSLATOR_FACADE = 'translatorFacade';

    protected ?DiscountFacadeInterface $discountFacade = null;

    protected ?DiscountToTranslatorFacadeInterface $translatorFacade = null;

    public function getDiscountFacade(): ?DiscountFacadeInterface
    {
        return $this->discountFacade;
    }

    public function getTranslatorFacade(): ?DiscountToTranslatorFacadeInterface
    {
        return $this->translatorFacade;
    }
}
