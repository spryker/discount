<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\Discount\Communication\Form;

use Codeception\Stub;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\ErrorCollectionTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Discount\Communication\Form\CalculatorForm;
use Spryker\Zed\Discount\Communication\Form\GeneralForm;
use Spryker\Zed\Discount\DiscountDependencyProvider;
use Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountConfiguratorValidatorRulePluginInterface;
use SprykerTest\Zed\Discount\DiscountCommunicationTester;
use Symfony\Component\Form\FormInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Communication
 * @group Form
 * @group DiscountFormTest
 * Add your own group annotations below this line
 */
class DiscountFormTest extends Unit
{
    protected const string PLUGIN_ERROR_MESSAGE = 'The description is rejected by a project rule.';

    protected const string PLUGIN_ERROR_ENTITY_IDENTIFIER = 'discountGeneral.description';

    protected const string INVALID_QUERY_STRING = "foo = '1'";

    protected const string DISCOUNT_NAME = 'Discount form test discount';

    protected const string PERCENTAGE_OUT_OF_RANGE = '150';

    protected DiscountCommunicationTester $tester;

    protected function _before(): void
    {
        parent::_before();

        $this->tester->registerStoreRelationToggleFormTypePlugin();
        $this->tester->registerMoneyCollectionFormTypePlugin();
    }

    /**
     * The validator rule plugins serve the API. The form validates through its form types instead: a sub-form another
     * module contributes carries its own constraints, so a plugin error must not reach the form a second time.
     */
    public function testGivenAValidatorRulePluginErrorWhenTheFormIsSubmittedThenThePluginIsLeftToTheApi(): void
    {
        // Arrange
        $this->tester->setDependency(
            DiscountDependencyProvider::PLUGINS_DISCOUNT_CONFIGURATOR_VALIDATOR_RULE,
            [$this->createValidatorRulePluginStub()],
        );
        $discountForm = $this->createDiscountForm();

        // Act
        $discountForm->submit([
            DiscountConfiguratorTransfer::DISCOUNT_GENERAL => [
                GeneralForm::FIELD_DESCRIPTION => 'Any description',
            ],
        ], false);

        // Assert
        $this->assertNotContains(
            static::PLUGIN_ERROR_MESSAGE,
            array_map(static fn ($formError) => $formError->getMessage(), iterator_to_array($discountForm->getErrors(true), false)),
        );
    }

    public function testGivenAnInvalidCollectorRuleWhenTheFormIsSubmittedThenExactlyOneErrorIsShownUnderTheField(): void
    {
        // Arrange
        $discountForm = $this->createDiscountForm();

        // Act
        $discountForm->submit([
            DiscountConfiguratorTransfer::DISCOUNT_CALCULATOR => [
                CalculatorForm::FIELD_COLLECTOR_QUERY_STRING => static::INVALID_QUERY_STRING,
            ],
        ], false);

        // Assert
        $collectorErrors = $discountForm
            ->get(DiscountConfiguratorTransfer::DISCOUNT_CALCULATOR)
            ->get(CalculatorForm::FIELD_COLLECTOR_QUERY_STRING)
            ->getErrors();

        $this->assertFalse($discountForm->isValid());
        $this->assertCount(1, $collectorErrors);
    }

    public function testGivenABlankNameWhenTheDiscountFormIsSubmittedThenTheNameFieldIsRejected(): void
    {
        // Arrange
        $discountForm = $this->createDiscountForm();

        // Act
        $discountForm->submit($this->createSubmittedData('', DiscountDependencyProvider::PLUGIN_CALCULATOR_FIXED, ''), false);

        // Assert
        $this->assertFalse($discountForm->isValid());
        $this->assertGreaterThan(0, $this->getGeneralField($discountForm, GeneralForm::FIELD_DISPLAY_NAME)->getErrors()->count());
    }

    public function testGivenAPercentageCalculatorAndABlankAmountWhenTheDiscountFormIsSubmittedThenTheAmountFieldIsRejected(): void
    {
        // Arrange
        $discountForm = $this->createDiscountForm();

        // Act
        $discountForm->submit($this->createSubmittedData(static::DISCOUNT_NAME, DiscountDependencyProvider::PLUGIN_CALCULATOR_PERCENTAGE, ''), false);

        // Assert
        $this->assertFalse($discountForm->isValid());
        $this->assertGreaterThan(0, $this->getCalculatorField($discountForm, CalculatorForm::FIELD_AMOUNT)->getErrors()->count());
    }

    public function testGivenAPercentageCalculatorAndAnAmountAboveOneHundredWhenTheDiscountFormIsSubmittedThenTheAmountFieldIsRejected(): void
    {
        // Arrange
        $discountForm = $this->createDiscountForm();

        // Act
        $discountForm->submit(
            $this->createSubmittedData(static::DISCOUNT_NAME, DiscountDependencyProvider::PLUGIN_CALCULATOR_PERCENTAGE, static::PERCENTAGE_OUT_OF_RANGE),
            false,
        );

        // Assert
        $this->assertFalse($discountForm->isValid());
        $this->assertGreaterThan(0, $this->getCalculatorField($discountForm, CalculatorForm::FIELD_AMOUNT)->getErrors()->count());
    }

    protected function createDiscountForm(): FormInterface
    {
        return $this->tester->getFactory()->getDiscountForm();
    }

    protected function createValidatorRulePluginStub(): DiscountConfiguratorValidatorRulePluginInterface
    {
        return Stub::makeEmpty(DiscountConfiguratorValidatorRulePluginInterface::class, [
            'validate' => (new ErrorCollectionTransfer())->addError(
                (new ErrorTransfer())
                    ->setMessage(static::PLUGIN_ERROR_MESSAGE)
                    ->setEntityIdentifier(static::PLUGIN_ERROR_ENTITY_IDENTIFIER),
            ),
        ]);
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function createSubmittedData(string $displayName, string $calculatorPlugin, string $amount): array
    {
        return [
            DiscountConfiguratorTransfer::DISCOUNT_GENERAL => [
                GeneralForm::FIELD_DISCOUNT_TYPE => DiscountConstants::TYPE_CART_RULE,
                GeneralForm::FIELD_DISPLAY_NAME => $displayName,
            ],
            DiscountConfiguratorTransfer::DISCOUNT_CALCULATOR => [
                CalculatorForm::FIELD_CALCULATOR_PLUGIN => $calculatorPlugin,
                CalculatorForm::FIELD_AMOUNT => $amount,
            ],
        ];
    }

    protected function getGeneralField(FormInterface $discountForm, string $fieldName): FormInterface
    {
        return $discountForm->get(DiscountConfiguratorTransfer::DISCOUNT_GENERAL)->get($fieldName);
    }

    protected function getCalculatorField(FormInterface $discountForm, string $fieldName): FormInterface
    {
        return $discountForm->get(DiscountConfiguratorTransfer::DISCOUNT_CALCULATOR)->get($fieldName);
    }
}
