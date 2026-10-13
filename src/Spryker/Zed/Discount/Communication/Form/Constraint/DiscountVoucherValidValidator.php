<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Communication\Form\Constraint;

use Generated\Shared\Transfer\DiscountVoucherTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DiscountVoucherValidValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     *
     * @throws \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof DiscountVoucherValid) {
            throw new UnexpectedTypeException($constraint, DiscountVoucherValid::class);
        }

        $discountFacade = $constraint->getDiscountFacade();

        if (!$value instanceof DiscountVoucherTransfer || $discountFacade === null) {
            return;
        }

        $discountVoucherResponseTransfer = $discountFacade->validateDiscountVoucher($value);

        if ($discountVoucherResponseTransfer->getIsSuccessful() === true) {
            return;
        }

        foreach ($discountVoucherResponseTransfer->getErrors() as $errorTransfer) {
            $this->addErrorViolation($errorTransfer, $constraint);
        }
    }

    protected function addErrorViolation(ErrorTransfer $errorTransfer, DiscountVoucherValid $constraint): void
    {
        $translatorFacade = $constraint->getTranslatorFacade();
        $glossaryKey = (string)$errorTransfer->getMessage();

        $message = $translatorFacade === null
            ? $glossaryKey
            : $translatorFacade->trans($glossaryKey, $errorTransfer->getParameters());

        $this->context->buildViolation($message)->addViolation();
    }
}
