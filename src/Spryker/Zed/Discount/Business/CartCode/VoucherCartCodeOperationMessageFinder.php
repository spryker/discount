<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\CartCode;

use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\QuoteTransfer;

class VoucherCartCodeOperationMessageFinder implements VoucherCartCodeOperationMessageFinderInterface
{
    /**
     * @var string
     */
    protected const GLOSSARY_KEY_VOUCHER_NON_APPLICABLE = 'cart.voucher.apply.non_applicable';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_VOUCHER_APPLY_SUCCESSFUL = 'cart.voucher.apply.successful';

    /**
     * @uses \Spryker\Shared\CartCode\CartCodesConfig::MESSAGE_TYPE_SUCCESS
     *
     * @var string
     */
    protected const MESSAGE_TYPE_SUCCESS = 'success';

    /**
     * @uses \Spryker\Shared\CartCode\CartCodesConfig::MESSAGE_TYPE_ERROR
     *
     * @var string
     */
    protected const MESSAGE_TYPE_ERROR = 'error';

    /**
     * @var array<\Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountVoucherApplyCheckerStrategyPluginInterface>
     */
    protected $discountVoucherApplyCheckerStrategyPlugins;

    /**
     * @param array<\Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountVoucherApplyCheckerStrategyPluginInterface> $discountVoucherApplyCheckerStrategyPlugins
     */
    public function __construct(array $discountVoucherApplyCheckerStrategyPlugins)
    {
        $this->discountVoucherApplyCheckerStrategyPlugins = $discountVoucherApplyCheckerStrategyPlugins;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param string $cartCode
     *
     * @return \Generated\Shared\Transfer\MessageTransfer|null
     */
    public function findOperationResponseMessage(QuoteTransfer $quoteTransfer, string $cartCode): ?MessageTransfer
    {
        $messageTransfer = $this->executeDiscountVoucherApplyCheckerStrategyPlugins($quoteTransfer, $cartCode);
        if ($messageTransfer) {
            return $messageTransfer;
        }

        $voucherApplySuccessMessageTransfer = $this->findVoucherApplySuccessMessage($quoteTransfer, $cartCode);
        if ($voucherApplySuccessMessageTransfer) {
            return $voucherApplySuccessMessageTransfer;
        }

        $nonApplicableErrorMessageTransfer = $this->findNonApplicableErrorMessage($quoteTransfer, $cartCode);
        if ($nonApplicableErrorMessageTransfer) {
            return $nonApplicableErrorMessageTransfer;
        }

        return null;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param string $code
     *
     * @return \Generated\Shared\Transfer\MessageTransfer|null
     */
    protected function findVoucherApplySuccessMessage(QuoteTransfer $quoteTransfer, string $code): ?MessageTransfer
    {
        if (
            in_array($code, $quoteTransfer->getUsedNotAppliedVoucherCodes(), true)
            || !$this->isVoucherCodeApplied($quoteTransfer, $code)
        ) {
            return null;
        }

        return (new MessageTransfer())
            ->setValue(static::GLOSSARY_KEY_VOUCHER_APPLY_SUCCESSFUL)
            ->setType(static::MESSAGE_TYPE_SUCCESS);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param string $code
     *
     * @return bool
     */
    protected function isVoucherCodeApplied(QuoteTransfer $quoteTransfer, string $code): bool
    {
        foreach ($quoteTransfer->getVoucherDiscounts() as $discountTransfer) {
            if ($discountTransfer->getVoucherCode() === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param string $code
     *
     * @return \Generated\Shared\Transfer\MessageTransfer|null
     */
    protected function findNonApplicableErrorMessage(QuoteTransfer $quoteTransfer, string $code): ?MessageTransfer
    {
        if (in_array($code, $quoteTransfer->getUsedNotAppliedVoucherCodes(), true)) {
            $messageTransfer = new MessageTransfer();
            $messageTransfer->setValue(static::GLOSSARY_KEY_VOUCHER_NON_APPLICABLE);
            $messageTransfer->setType(static::MESSAGE_TYPE_ERROR);

            return $messageTransfer;
        }

        return null;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param string $cartCode
     *
     * @return \Generated\Shared\Transfer\MessageTransfer|null
     */
    protected function executeDiscountVoucherApplyCheckerStrategyPlugins(
        QuoteTransfer $quoteTransfer,
        string $cartCode
    ): ?MessageTransfer {
        foreach ($this->discountVoucherApplyCheckerStrategyPlugins as $discountVoucherApplyCheckerStrategyPlugin) {
            if (!$discountVoucherApplyCheckerStrategyPlugin->isApplicable($quoteTransfer, $cartCode)) {
                continue;
            }

            return $discountVoucherApplyCheckerStrategyPlugin->check($quoteTransfer, $cartCode)->getMessage();
        }

        return null;
    }
}
