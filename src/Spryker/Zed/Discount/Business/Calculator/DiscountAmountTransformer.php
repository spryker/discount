<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Business\Calculator;

use Generated\Shared\Transfer\DiscountCalculatorTransfer;
use Spryker\Zed\Discount\Business\Exception\CalculatorException;
use Spryker\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginInterface;

class DiscountAmountTransformer implements DiscountAmountTransformerInterface
{
    protected const string EXCEPTION_MESSAGE_CALCULATOR_PLUGIN_NOT_FOUND = 'Calculator plugin with name "%s" not found. Have you added it to DiscountDependencyProvider::getAvailableCalculatorPlugins plugin stack?';

    /**
     * @param array<string, \Spryker\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginInterface> $calculatorPlugins
     */
    public function __construct(protected array $calculatorPlugins)
    {
    }

    public function transformAmountFromPersistence(
        DiscountCalculatorTransfer $discountCalculatorTransfer
    ): DiscountCalculatorTransfer {
        $calculatorPlugin = $this->findCalculatorPlugin($discountCalculatorTransfer);

        if ($calculatorPlugin === null || $discountCalculatorTransfer->getAmount() === null) {
            return $discountCalculatorTransfer;
        }

        return $discountCalculatorTransfer->setAmount(
            $calculatorPlugin->transformFromPersistence((int)$discountCalculatorTransfer->getAmount()),
        );
    }

    public function transformAmountForPersistence(
        DiscountCalculatorTransfer $discountCalculatorTransfer
    ): DiscountCalculatorTransfer {
        $calculatorPlugin = $this->findCalculatorPlugin($discountCalculatorTransfer);

        if ($calculatorPlugin === null) {
            return $discountCalculatorTransfer;
        }

        return $discountCalculatorTransfer->setAmount(
            $calculatorPlugin->transformForPersistence((float)$discountCalculatorTransfer->getAmount()),
        );
    }

    /**
     * @throws \Spryker\Zed\Discount\Business\Exception\CalculatorException
     */
    protected function findCalculatorPlugin(
        DiscountCalculatorTransfer $discountCalculatorTransfer
    ): ?DiscountCalculatorPluginInterface {
        $calculatorPluginKey = $discountCalculatorTransfer->getCalculatorPlugin();

        if ($calculatorPluginKey === null || $calculatorPluginKey === '') {
            return null;
        }

        if (!isset($this->calculatorPlugins[$calculatorPluginKey])) {
            throw new CalculatorException(
                sprintf(static::EXCEPTION_MESSAGE_CALCULATOR_PLUGIN_NOT_FOUND, $calculatorPluginKey),
            );
        }

        return $this->calculatorPlugins[$calculatorPluginKey];
    }
}
