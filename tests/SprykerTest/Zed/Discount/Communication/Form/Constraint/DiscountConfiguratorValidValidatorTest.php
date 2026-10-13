<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\Discount\Communication\Form\Constraint;

use Codeception\Stub;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\DiscountCalculatorTransfer;
use Generated\Shared\Transfer\DiscountConditionTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorResponseTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountGeneralTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;
use Spryker\Zed\Discount\Communication\Form\Constraint\DiscountConfiguratorValid;
use Spryker\Zed\Discount\Communication\Form\Constraint\DiscountConfiguratorValidValidator;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Communication
 * @group Form
 * @group Constraint
 * @group DiscountConfiguratorValidValidatorTest
 * Add your own group annotations below this line
 */
class DiscountConfiguratorValidValidatorTest extends Unit
{
    protected const string ERROR_MESSAGE = 'error message';

    /**
     * @var array<string>
     */
    protected array $violationPaths = [];

    /**
     * @dataProvider entityIdentifierToFormPathDataProvider
     *
     * @param array<string> $expectedFormPaths
     */
    public function testGivenAFormWhenAnErrorIsReportedThenItIsAttachedToTheFormOfTheEntityIdentifier(
        string $entityIdentifier,
        array $expectedFormPaths,
    ): void {
        // Arrange
        $validator = $this->createValidator($this->createDiscountForm());

        // Act
        $validator->validate(new DiscountConfiguratorTransfer(), $this->createConstraint($entityIdentifier));

        // Assert
        $this->assertSame($expectedFormPaths, $this->violationPaths);
    }

    /**
     * @return array<string, array<int, string|array<string>>>
     */
    public static function entityIdentifierToFormPathDataProvider(): array
    {
        return [
            'core rule with a snake_case field' => ['discountGeneral.displayName', ['discountGeneral.display_name']],
            'core rule with a nested identifier stops at the deepest form' => ['discountGeneral.storeRelation.stores.0', ['discountGeneral.store_relation']],
            'plugin rule on an expanded sub form' => ['discountCalculator.discountPromotion.abstractSkus', ['discountCalculator.discountPromotion.abstractSkus']],
            'collection level money error' => ['discountCalculator.moneyValueCollection', ['discountCalculator.moneyValueCollection']],
            'money row with one filled amount goes to that amount' => ['discountCalculator.moneyValueCollection.0', ['discountCalculator.moneyValueCollection[0].gross_amount']],
            'money row with both amounts goes to both' => ['discountCalculator.moneyValueCollection.1', ['discountCalculator.moneyValueCollection[1].gross_amount', 'discountCalculator.moneyValueCollection[1].net_amount']],
            'money row without an amount goes to the first amount field' => ['discountCalculator.moneyValueCollection.2', ['discountCalculator.moneyValueCollection[2].gross_amount']],
            'unknown identifier goes to the root form' => ['somethingElse.field', ['']],
        ];
    }

    public function testGivenNoFormInTheContextWhenAnErrorIsReportedThenTheStaticMappingIsUsed(): void
    {
        // Arrange
        $validator = $this->createValidator(null);

        // Act
        $validator->validate(new DiscountConfiguratorTransfer(), $this->createConstraint('discountCondition.decisionRuleQueryString'));

        // Assert
        $this->assertSame(['discountCondition.decision_rule_query_string'], $this->violationPaths);
    }

    public function testGivenASuccessfulValidationWhenValidatedThenNoViolationIsAdded(): void
    {
        // Arrange
        $validator = $this->createValidator($this->createDiscountForm());
        $constraint = new DiscountConfiguratorValid([
            DiscountConfiguratorValid::OPTION_DISCOUNT_FACADE => Stub::makeEmpty(DiscountFacadeInterface::class, [
                'validateDiscountConfiguratorRules' => (new DiscountConfiguratorResponseTransfer())->setIsSuccessful(true),
            ]),
        ]);

        // Act
        $validator->validate(new DiscountConfiguratorTransfer(), $constraint);

        // Assert
        $this->assertSame([], $this->violationPaths);
    }

    protected function createConstraint(string $entityIdentifier): DiscountConfiguratorValid
    {
        $discountConfiguratorResponseTransfer = (new DiscountConfiguratorResponseTransfer())
            ->setIsSuccessful(false)
            ->addError(
                (new ErrorTransfer())
                    ->setMessage(static::ERROR_MESSAGE)
                    ->setEntityIdentifier($entityIdentifier),
            );

        return new DiscountConfiguratorValid([
            DiscountConfiguratorValid::OPTION_DISCOUNT_FACADE => Stub::makeEmpty(DiscountFacadeInterface::class, [
                'validateDiscountConfiguratorRules' => $discountConfiguratorResponseTransfer,
            ]),
        ]);
    }

    protected function createValidator(?FormInterface $form): DiscountConfiguratorValidValidator
    {
        $this->violationPaths = [];

        $violationBuilder = Stub::makeEmpty(ConstraintViolationBuilderInterface::class, [
            'atPath' => function (string $path) use (&$violationBuilder): ConstraintViolationBuilderInterface {
                $this->violationPaths[] = $path;

                return $violationBuilder;
            },
        ]);

        $context = Stub::makeEmpty(ExecutionContextInterface::class, [
            'getObject' => $form,
            'buildViolation' => $violationBuilder,
        ]);

        $validator = new DiscountConfiguratorValidValidator();
        $validator->initialize($context);

        return $validator;
    }

    /**
     * Mirrors the Back Office discount form: sub forms bound to the transfers, snake_case fields, an indexed collection
     * for the money values and a plugin-expanded sub form for the promotion whose fields map to transfer properties.
     */
    protected function createDiscountForm(): FormInterface
    {
        $formBuilder = Forms::createFormFactory()->createBuilder(FormType::class, null, ['data_class' => DiscountConfiguratorTransfer::class]);

        $formBuilder->add('discountGeneral', FormType::class, ['data_class' => DiscountGeneralTransfer::class]);
        $formBuilder->get('discountGeneral')
            ->add('display_name', TextType::class)
            ->add('store_relation', TextType::class);

        $formBuilder->add('discountCalculator', FormType::class, ['data_class' => DiscountCalculatorTransfer::class]);
        $formBuilder->get('discountCalculator')
            ->add('calculator_plugin', TextType::class)
            ->add('collector_query_string', TextType::class)
            ->add('moneyValueCollection', FormType::class)
            ->add('discountPromotion', FormType::class);
        $formBuilder->get('discountCalculator')->get('discountPromotion')
            ->add('abstractSkus', TextType::class, ['property_path' => 'abstractSkus'])
            ->add('quantity', TextType::class, ['property_path' => 'quantity']);

        $moneyValueCollectionBuilder = $formBuilder->get('discountCalculator')->get('moneyValueCollection');
        $moneyValueTransfers = [
            (new MoneyValueTransfer())->setGrossAmount(100),
            (new MoneyValueTransfer())->setGrossAmount(100)->setNetAmount(90),
            new MoneyValueTransfer(),
        ];

        foreach ($moneyValueTransfers as $index => $moneyValueTransfer) {
            $moneyValueCollectionBuilder->add((string)$index, FormType::class, [
                'data_class' => MoneyValueTransfer::class,
                'data' => $moneyValueTransfer,
            ]);
            $moneyValueCollectionBuilder->get((string)$index)
                ->add('gross_amount', TextType::class)
                ->add('net_amount', TextType::class);
        }

        $formBuilder->add('discountCondition', FormType::class, ['data_class' => DiscountConditionTransfer::class]);
        $formBuilder->get('discountCondition')
            ->add('decision_rule_query_string', TextType::class);

        return $formBuilder->getForm();
    }
}
