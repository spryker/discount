<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\QuoteDiscountValidator;

use ArrayObject;
use Generated\Shared\Transfer\CheckoutErrorTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Discount\Business\Voucher\VoucherValidator;
use Spryker\Zed\Discount\Persistence\DiscountRepositoryInterface;

class QuoteDiscountMaxUsageValidator implements QuoteDiscountValidatorInterface
{
    /**
     * @var int
     */
    protected const ERROR_VOUCHER_CODE_LIMIT_REACHED = 399;

    /**
     * @var \Spryker\Zed\Discount\Persistence\DiscountRepositoryInterface
     */
    protected $discountRepository;

    public function __construct(DiscountRepositoryInterface $discountRepository)
    {
        $this->discountRepository = $discountRepository;
    }

    public function validate(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): bool
    {
        $voucherDiscounts = $quoteTransfer->getVoucherDiscounts();
        if ($voucherDiscounts->count() === 0) {
            return true;
        }

        $voucherCodesExceedingUsageLimit = $this->findVoucherCodesExceedingUsageLimit($voucherDiscounts);
        foreach ($voucherCodesExceedingUsageLimit as $voucherCode) {
            $this->addError(
                $this->createVoucherCodeLimitReachedMessageTransfer($voucherCode),
                static::ERROR_VOUCHER_CODE_LIMIT_REACHED,
                $checkoutResponseTransfer,
            );
        }

        return $voucherCodesExceedingUsageLimit === [];
    }

    protected function createVoucherCodeLimitReachedMessageTransfer(string $voucherCode): MessageTransfer
    {
        return (new MessageTransfer())
            ->setValue(VoucherValidator::REASON_VOUCHER_CODE_LIMIT_REACHED)
            ->setParameters([
                '%code%' => $voucherCode,
            ]);
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\DiscountTransfer> $voucherDiscounts
     *
     * @return array<string>
     */
    protected function findVoucherCodesExceedingUsageLimit(ArrayObject $voucherDiscounts): array
    {
        return $this->discountRepository
            ->findVoucherCodesExceedingUsageLimit(
                $this->getVoucherCodes($voucherDiscounts),
            );
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\DiscountTransfer> $voucherDiscounts
     *
     * @return array<string>
     */
    protected function getVoucherCodes(ArrayObject $voucherDiscounts)
    {
        $codes = [];
        foreach ($voucherDiscounts as $voucherDiscount) {
            $codes[] = $voucherDiscount->getVoucherCode();
        }

        return $codes;
    }

    protected function addError(MessageTransfer $message, int $errorCode, CheckoutResponseTransfer $checkoutResponseTransfer): void
    {
        $checkoutErrorTransfer = (new CheckoutErrorTransfer())
            ->setMessage($message->getValue())
            ->setErrorCode($errorCode);

        $checkoutResponseTransfer
            ->addError($checkoutErrorTransfer)
            ->setIsSuccess(false)
            ->setIsExternalRedirect(false);
    }
}
