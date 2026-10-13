<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Communication\Form\Constraint;

use Generated\Shared\Transfer\DiscountCalculatorTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\CalculatorPluginDiscountConfiguratorValidatorRule;
use Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\MoneyAmountCurrencyDiscountConfiguratorValidatorRule;
use Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\QueryStringDiscountConfiguratorValidatorRule;
use Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\StoreRelationDiscountConfiguratorValidatorRule;
use Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\UniqueDisplayNameDiscountConfiguratorValidatorRule;
use Spryker\Zed\Discount\Communication\Form\CalculatorForm;
use Spryker\Zed\Discount\Communication\Form\ConditionsForm;
use Spryker\Zed\Discount\Communication\Form\GeneralForm;
use Spryker\Zed\Money\Communication\Form\Type\MoneyType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DiscountConfiguratorValidValidator extends ConstraintValidator
{
    protected const string FORM_PATH_PATTERN = '%s.%s';

    /**
     * @var non-empty-string
     */
    protected const string ENTITY_IDENTIFIER_SEPARATOR = '.';

    protected const string INDEX_PROPERTY_PATH_PATTERN = '[%s]';

    /**
     * @var array<string>
     */
    protected const array MONEY_VALUE_AMOUNT_FIELDS = [
        MoneyType::FIELD_GROSS_AMOUNT,
        MoneyType::FIELD_NET_AMOUNT,
    ];

    /**
     * @param mixed $value
     *
     * @throws \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof DiscountConfiguratorValid) {
            throw new UnexpectedTypeException($constraint, DiscountConfiguratorValid::class);
        }

        $discountFacade = $constraint->getDiscountFacade();

        if (!$value instanceof DiscountConfiguratorTransfer || $discountFacade === null) {
            return;
        }

        $discountConfiguratorResponseTransfer = $discountFacade->validateDiscountConfiguratorRules($value);

        if ($discountConfiguratorResponseTransfer->getIsSuccessful() === true) {
            return;
        }

        foreach ($discountConfiguratorResponseTransfer->getErrors() as $errorTransfer) {
            $this->addErrorViolation($errorTransfer, $constraint);
        }
    }

    protected function addErrorViolation(ErrorTransfer $errorTransfer, DiscountConfiguratorValid $constraint): void
    {
        $translatorFacade = $constraint->getTranslatorFacade();
        $glossaryKey = (string)$errorTransfer->getMessage();
        $parameters = $errorTransfer->getParameters();

        $message = $translatorFacade === null
            ? $glossaryKey
            : $translatorFacade->trans($glossaryKey, $parameters);

        foreach ($this->resolveFormPaths((string)$errorTransfer->getEntityIdentifier()) as $formPath) {
            $this->context
                ->buildViolation($message)
                ->atPath($formPath)
                ->addViolation();
        }
    }

    /**
     * @return array<string>
     */
    protected function resolveFormPaths(string $entityIdentifier): array
    {
        $form = $this->context->getObject();

        if (!$form instanceof FormInterface) {
            return [$this->getFormPath($entityIdentifier)];
        }

        $formPath = '';

        foreach (explode(static::ENTITY_IDENTIFIER_SEPARATOR, $entityIdentifier) as $segment) {
            $childForm = $this->findChildForm($form, $segment);

            if ($childForm === null) {
                break;
            }

            $formPath = $this->appendFormPath($formPath, $childForm);
            $form = $childForm;
        }

        if ($form->getData() instanceof MoneyValueTransfer) {
            return $this->resolveMoneyValueFormPaths($form, $formPath);
        }

        return [$formPath];
    }

    protected function findChildForm(FormInterface $form, string $segment): ?FormInterface
    {
        foreach ($form->all() as $childForm) {
            $propertyPath = trim((string)$childForm->getPropertyPath(), '[]');

            if (
                $childForm->getName() === $segment
                || $propertyPath === $segment
                || $this->camelize($childForm->getName()) === $segment
            ) {
                return $childForm;
            }
        }

        return null;
    }

    /**
     * @return array<string>
     */
    protected function resolveMoneyValueFormPaths(FormInterface $moneyValueForm, string $formPath): array
    {
        $amountFormPaths = [];
        $fallbackFormPath = $formPath;

        foreach (static::MONEY_VALUE_AMOUNT_FIELDS as $fieldName) {
            if (!$moneyValueForm->has($fieldName)) {
                continue;
            }

            $amountForm = $moneyValueForm->get($fieldName);
            $amountFormPath = $this->appendFormPath($formPath, $amountForm);
            $fallbackFormPath = $fallbackFormPath === $formPath ? $amountFormPath : $fallbackFormPath;

            if ($amountForm->getData() !== null) {
                $amountFormPaths[] = $amountFormPath;
            }
        }

        return $amountFormPaths === [] ? [$fallbackFormPath] : $amountFormPaths;
    }

    protected function appendFormPath(string $formPath, FormInterface $childForm): string
    {
        $propertyPath = (string)$childForm->getPropertyPath();

        if (is_numeric($childForm->getName())) {
            return $formPath . sprintf(static::INDEX_PROPERTY_PATH_PATTERN, $childForm->getName());
        }

        if ($formPath === '') {
            return $propertyPath;
        }

        return sprintf(static::FORM_PATH_PATTERN, $formPath, $propertyPath);
    }

    protected function camelize(string $name): string
    {
        return lcfirst(str_replace('_', '', ucwords($name, '_')));
    }

    protected function getFormPath(string $entityIdentifier): string
    {
        $matchedPrefixLength = 0;
        $formPath = '';

        foreach ($this->getFormPathByEntityIdentifier() as $prefix => $candidateFormPath) {
            if (strlen($prefix) > $matchedPrefixLength && str_starts_with($entityIdentifier, $prefix)) {
                $matchedPrefixLength = strlen($prefix);
                $formPath = $candidateFormPath;
            }
        }

        return $formPath;
    }

    /**
     * @return array<string, string>
     */
    protected function getFormPathByEntityIdentifier(): array
    {
        return [
            UniqueDisplayNameDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_DISPLAY_NAME => sprintf(
                static::FORM_PATH_PATTERN,
                DiscountConfiguratorTransfer::DISCOUNT_GENERAL,
                GeneralForm::FIELD_DISPLAY_NAME,
            ),
            StoreRelationDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_STORES => sprintf(
                static::FORM_PATH_PATTERN,
                DiscountConfiguratorTransfer::DISCOUNT_GENERAL,
                GeneralForm::FIELD_STORE_RELATION,
            ),
            CalculatorPluginDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_CALCULATOR_PLUGIN => sprintf(
                static::FORM_PATH_PATTERN,
                DiscountConfiguratorTransfer::DISCOUNT_CALCULATOR,
                CalculatorForm::FIELD_CALCULATOR_PLUGIN,
            ),
            MoneyAmountCurrencyDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_MONEY_VALUE_COLLECTION => sprintf(
                static::FORM_PATH_PATTERN,
                DiscountConfiguratorTransfer::DISCOUNT_CALCULATOR,
                DiscountCalculatorTransfer::MONEY_VALUE_COLLECTION,
            ),
            QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_COLLECTOR_QUERY_STRING => sprintf(
                static::FORM_PATH_PATTERN,
                DiscountConfiguratorTransfer::DISCOUNT_CALCULATOR,
                CalculatorForm::FIELD_COLLECTOR_QUERY_STRING,
            ),
            QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_DECISION_RULE_QUERY_STRING => sprintf(
                static::FORM_PATH_PATTERN,
                DiscountConfiguratorTransfer::DISCOUNT_CONDITION,
                ConditionsForm::FIELD_DECISION_RULE_QUERY_STRING,
            ),
        ];
    }
}
