<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Communication\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

class CalculatorAmountTransformer implements DataTransformerInterface
{

    /**
     * @var \Spryker\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginInterface[]
     */
    protected $calculatorPlugins = [];

    /**
     * @param array $calculatorPlugins
     */
    public function __construct(array $calculatorPlugins)
    {
        $this->calculatorPlugins = $calculatorPlugins;
    }

    /**
     * @param \Generated\Shared\Transfer\DiscountCalculatorTransfer|null $value
     *
     * @return \Generated\Shared\Transfer\DiscountCalculatorTransfer|null
     */
    public function transform($value)
    {
        if (!$value || !$value->getCalculatorPlugin()) {
            return null;
        }

        $calculatorPlugin = $this->getCalculatorPlugin($value->getCalculatorPlugin());
        $transformedAmount = $calculatorPlugin->transformFromPersistence($value->getAmount());
        $value->setAmount($transformedAmount);

        return $value;
    }

    /**
     * @param \Generated\Shared\Transfer\DiscountCalculatorTransfer|null $value
     *
     * @return \Generated\Shared\Transfer\DiscountCalculatorTransfer|null
     */
    public function reverseTransform($value)
    {
        if (!$value || !$value->getCalculatorPlugin()) {
            return null;
        }

        $calculatorPlugin = $this->getCalculatorPlugin($value->getCalculatorPlugin());
        $transformedAmount = $calculatorPlugin->transformForPersistence($value->getAmount());
        $value->setAmount($transformedAmount);

        return $value;
    }

    /**
     * @param string $pluginName
     *
     * @return \Spryker\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginInterface
     */
    protected function getCalculatorPlugin($pluginName)
    {
        if (isset($this->calculatorPlugins[$pluginName])) {
            return $this->calculatorPlugins[$pluginName];
        }

        throw new \InvalidArgumentException(sprintf(
            'Calculator plugin with name "%s" not found',
            $pluginName
        ));
    }

}
