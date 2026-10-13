<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\Discount\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\DiscountCalculatorTransfer;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;
use Spryker\Zed\Discount\Business\Exception\CalculatorException;
use Spryker\Zed\Discount\DiscountDependencyProvider;
use SprykerTest\Zed\Discount\DiscountBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group Facade
 * @group TransformDiscountCalculatorAmountFacadeTest
 * Add your own group annotations below this line
 */
class TransformDiscountCalculatorAmountFacadeTest extends Unit
{
    protected const string UNKNOWN_CALCULATOR_PLUGIN = 'PLUGIN_CALCULATOR_DOES_NOT_EXIST';

    protected const int API_PERCENTAGE_AMOUNT = 10;

    protected const int PERSISTED_PERCENTAGE_AMOUNT = 1000;

    protected const float API_FIXED_AMOUNT = 25.0;

    protected const int PERSISTED_FIXED_AMOUNT = 2500;

    protected DiscountBusinessTester $tester;

    /**
     * @dataProvider provideAmountTransformations
     */
    public function testTransformsTheAmountForPersistence(
        ?string $calculatorPlugin,
        int|float|null $apiAmount,
        ?int $expectedPersistedAmount
    ): void {
        // Act
        $persistedDiscountCalculatorTransfer = $this->createDiscountFacade()->transformDiscountCalculatorAmountForPersistence(
            $this->createDiscountCalculator($calculatorPlugin, $apiAmount),
        );

        // Assert
        $this->assertSame($expectedPersistedAmount, $persistedDiscountCalculatorTransfer->getAmount());
    }

    /**
     * @return array<string, array{string|null, int|float|null, int|null}>
     */
    public function provideAmountTransformations(): array
    {
        return [
            'given the percentage calculator, when the amount is transformed, then the percent value is persisted as hundredths' => [
                DiscountDependencyProvider::PLUGIN_CALCULATOR_PERCENTAGE,
                static::API_PERCENTAGE_AMOUNT,
                static::PERSISTED_PERCENTAGE_AMOUNT,
            ],
            'given the fixed calculator, when the amount is transformed, then the unit value is persisted as cents' => [
                DiscountDependencyProvider::PLUGIN_CALCULATOR_FIXED,
                static::API_FIXED_AMOUNT,
                static::PERSISTED_FIXED_AMOUNT,
            ],
            'given no calculator plugin, when the amount is transformed, then it is left untouched' => [
                null,
                static::API_PERCENTAGE_AMOUNT,
                static::API_PERCENTAGE_AMOUNT,
            ],
            'given an empty calculator plugin, when the amount is transformed, then it is left untouched' => [
                '',
                static::API_PERCENTAGE_AMOUNT,
                static::API_PERCENTAGE_AMOUNT,
            ],
            'given no amount and a known calculator, when it is transformed, then the column default of zero is written' => [
                DiscountDependencyProvider::PLUGIN_CALCULATOR_PERCENTAGE,
                null,
                0,
            ],
        ];
    }

    /**
     * @dataProvider provideUnknownCalculatorPluginTransformations
     */
    public function testRejectsACalculatorPluginOutsideTheAvailableStack(callable $transform): void
    {
        // Arrange
        $discountCalculatorTransfer = $this->createDiscountCalculator(
            static::UNKNOWN_CALCULATOR_PLUGIN,
            static::API_PERCENTAGE_AMOUNT,
        );

        // Assert
        $this->expectException(CalculatorException::class);
        $this->expectExceptionMessageMatches('/' . static::UNKNOWN_CALCULATOR_PLUGIN . '/');

        // Act
        $transform($this->createDiscountFacade(), $discountCalculatorTransfer);
    }

    /**
     * @return array<string, array{callable}>
     */
    public function provideUnknownCalculatorPluginTransformations(): array
    {
        return [
            'given an unregistered calculator plugin, when the amount is transformed for persistence, then a calculator exception is thrown' => [
                static fn (
                    DiscountFacadeInterface $discountFacade,
                    DiscountCalculatorTransfer $discountCalculatorTransfer
                ): DiscountCalculatorTransfer => $discountFacade->transformDiscountCalculatorAmountForPersistence(
                    $discountCalculatorTransfer,
                ),
            ],
        ];
    }

    protected function createDiscountCalculator(?string $calculatorPlugin, int|float|null $amount): DiscountCalculatorTransfer
    {
        return (new DiscountCalculatorTransfer())->setCalculatorPlugin($calculatorPlugin)->setAmount($amount);
    }

    protected function createDiscountFacade(): DiscountFacadeInterface
    {
        return $this->tester->getLocator()->discount()->facade();
    }
}
