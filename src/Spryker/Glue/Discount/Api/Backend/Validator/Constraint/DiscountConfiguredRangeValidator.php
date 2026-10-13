<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Validator\Constraint;

use Generated\Shared\Transfer\DiscountConfigurationOptionsTransfer;
use Generated\Shared\Transfer\DiscountIntegerRangeTransfer;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class DiscountConfiguredRangeValidator extends ConstraintValidator
{
    protected ?DiscountConfigurationOptionsTransfer $discountConfigurationOptionsTransfer = null;

    public function __construct(protected DiscountFacadeInterface $discountFacade)
    {
    }

    /**
     * @throws \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof DiscountConfiguredRange) {
            throw new UnexpectedTypeException($constraint, DiscountConfiguredRange::class);
        }

        if ($value === null || !is_numeric($value)) {
            // Absence and type are reported by the NotNull / Type constraints declared next to this one.
            return;
        }

        $rangeTransfer = $this->getRange($constraint->range);
        $min = $rangeTransfer->getMinOrFail();
        $max = $rangeTransfer->getMaxOrFail();

        if ($value >= $min && $value <= $max) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', (string)$value)
            ->setParameter('{{ min }}', (string)$min)
            ->setParameter('{{ max }}', (string)$max)
            ->setCode(DiscountConfiguredRange::NOT_IN_RANGE_ERROR)
            ->addViolation();
    }

    /**
     * @throws \Symfony\Component\Validator\Exception\UnexpectedValueException
     */
    protected function getRange(string $range): DiscountIntegerRangeTransfer
    {
        $this->discountConfigurationOptionsTransfer ??= $this->discountFacade->getDiscountConfigurationOptions();

        $rangeTransfer = match ($range) {
            DiscountConfiguredRange::RANGE_PRIORITY => $this->discountConfigurationOptionsTransfer->getPriorityRange(),
            DiscountConfiguredRange::RANGE_VOUCHER_CODES_QUANTITY => $this->discountConfigurationOptionsTransfer->getVoucherCodesQuantityRange(),
            DiscountConfiguredRange::RANGE_VOUCHER_CODE_LENGTH => $this->discountConfigurationOptionsTransfer->getVoucherCodeLengthRange(),
            default => null,
        };

        if ($rangeTransfer === null) {
            throw new UnexpectedValueException($range, implode('|', [
                DiscountConfiguredRange::RANGE_PRIORITY,
                DiscountConfiguredRange::RANGE_VOUCHER_CODES_QUANTITY,
                DiscountConfiguredRange::RANGE_VOUCHER_CODE_LENGTH,
            ]));
        }

        return $rangeTransfer;
    }
}
