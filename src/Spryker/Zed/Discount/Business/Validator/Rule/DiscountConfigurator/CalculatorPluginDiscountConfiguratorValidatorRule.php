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

class CalculatorPluginDiscountConfiguratorValidatorRule implements DiscountConfiguratorValidatorRuleInterface
{
    public const string ENTITY_IDENTIFIER_CALCULATOR_PLUGIN = 'discountCalculator.calculatorPlugin';

    protected const string TRANSLATION_PARAMETER_KEY = '%key%';

    /**
     * @param array<string, \Spryker\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginInterface> $calculatorPlugins
     */
    public function __construct(protected array $calculatorPlugins)
    {
    }

    public function validate(DiscountConfiguratorTransfer $discountConfiguratorTransfer): ErrorCollectionTransfer
    {
        $errorCollectionTransfer = new ErrorCollectionTransfer();
        $calculatorPlugin = $discountConfiguratorTransfer->getDiscountCalculator()?->getCalculatorPlugin();

        if ($calculatorPlugin === null || $calculatorPlugin === '') {
            return $errorCollectionTransfer;
        }

        if (isset($this->calculatorPlugins[$calculatorPlugin])) {
            return $errorCollectionTransfer;
        }

        return $errorCollectionTransfer->addError(
            (new ErrorTransfer())
                ->setMessage(DiscountConfig::TRANSLATION_KEY_CALCULATOR_PLUGIN_INVALID)
                ->setEntityIdentifier(static::ENTITY_IDENTIFIER_CALCULATOR_PLUGIN)
                ->setParameters([static::TRANSLATION_PARAMETER_KEY => $calculatorPlugin]),
        );
    }
}
