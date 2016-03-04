<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Communication\Form;

use Spryker\Zed\Gui\Communication\Form\Type\AutosuggestType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class VoucherCodesForm extends AbstractRuleForm
{

    const FIELD_NAME = 'name';
    const FIELD_VOUCHER_POOL_CATEGORY = 'voucher_pool_category';
    const FIELD_IS_ACTIVE = 'is_active';
    const FIELD_IS_PRIVILEGED = 'is_privileged';
    const FIELD_DESCRIPTION = 'description';
    const FIELD_AMOUNT = 'amount';
    const FIELD_VALID_FROM = 'valid_from';
    const FIELD_VALID_TO = 'valid_to';
    const FIELD_CALCULATOR_PLUGIN = 'calculator_plugin';
    const FIELD_COLLECTOR_PLUGINS = 'collector_plugins';
    const FIELD_DECISION_RULES = 'decision_rules';
    const FIELD_COLLECTOR_LOGICAL_OPERATOR = 'collector_logical_operator';

    /**
     * @var \Spryker\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginInterface[]
     */
    protected $calculatorPlugins;

    /**
     * @var \Spryker\Zed\Discount\Dependency\Plugin\DiscountCollectorPluginInterface[]
     */
    protected $collectorPlugins;

    /**
     * @var \Spryker\Zed\Discount\Dependency\Plugin\DiscountDecisionRulePluginInterface[]
     */
    protected $decisionRulePlugins;

    /**
     * @var \Symfony\Component\Form\DataTransformerInterface
     */
    protected $decisionRulesFormTransformer;

    /**
     * @param \Symfony\Component\Form\DataTransformerInterface $decisionRulesFormTransformer
     * @param \Spryker\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginInterface[] $calculatorPlugins
     * @param \Spryker\Zed\Discount\Dependency\Plugin\DiscountCollectorPluginInterface[] $collectorPlugins
     * @param \Spryker\Zed\Discount\Dependency\Plugin\DiscountDecisionRulePluginInterface[] $decisionRulePlugins
     */
    public function __construct(
        DataTransformerInterface $decisionRulesFormTransformer,
        array $calculatorPlugins,
        array $collectorPlugins,
        array $decisionRulePlugins
    ) {
        parent::__construct($calculatorPlugins, $collectorPlugins, $decisionRulePlugins);

        $this->calculatorPlugins = $calculatorPlugins;
        $this->collectorPlugins = $collectorPlugins;
        $this->decisionRulePlugins = $decisionRulePlugins;
        $this->decisionRulesFormTransformer = $decisionRulesFormTransformer;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'voucher_codes';
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this
            ->addNameField($builder)
            ->addVoucherPoolCategoryField($builder)
            ->addDescriptionField($builder)
            ->addAmountField($builder)
            ->addValidFromField($builder)
            ->addValidToField($builder)
            ->addIsPrivilegedField($builder)
            ->addIsActiveField($builder)
            ->addCollectorPluginsField($builder)
            ->addCollectorLogicalOperatorField($builder)
            ->addDecisionRulesField($builder)
            ->addCalculatorPluginField($builder);

        $builder->addModelTransformer($this->decisionRulesFormTransformer);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addNameField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_NAME, 'text', [
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addVoucherPoolCategoryField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_VOUCHER_POOL_CATEGORY, new AutosuggestType(), [
            'label' => 'Pool Category',
            'url' => '/discount/pool/category-suggest',
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addIsActiveField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_IS_ACTIVE, 'checkbox', [
            'label' => 'Active',
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addIsPrivilegedField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_IS_PRIVILEGED, 'checkbox', [
            'label' => 'Is Combinable with other discounts',
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addDescriptionField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_DESCRIPTION, 'textarea');

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addAmountField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_AMOUNT, 'text', [
            'label' => 'Amount (Please enter a valid amount. Eg. 5 or 5.55)',
            'constraints' => [
                new NotBlank(),
                new GreaterThan([
                    'value' => 0,
                ]),
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addValidFromField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_VALID_FROM, 'date', [
            'label' => 'Valid From',
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addValidToField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_VALID_TO, 'date', [
            'label' => 'Valid Until',
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addCalculatorPluginField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_CALCULATOR_PLUGIN, 'choice', [
            'label' => 'Calculator Plugin',
            'choices' => $this->getAvailableCalculatorPlugins(),
            'empty_data' => null,
            'required' => false,
            'placeholder' => false,
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addCollectorPluginsField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_COLLECTOR_PLUGINS, 'collection', [
            'type' => new CollectorPluginForm(
                $this->calculatorPlugins,
                $this->collectorPlugins,
                $this->decisionRulePlugins
            ),
            'label' => null,
            'allow_add' => true,
            'allow_delete' => true,
            'allow_extra_fields' => true,
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addDecisionRulesField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_DECISION_RULES, 'collection', [
            'type' => new DecisionRuleForm(
                $this->calculatorPlugins,
                $this->collectorPlugins,
                $this->decisionRulePlugins
            ),
            'label' => null,
            'allow_add' => true,
            'allow_delete' => true,
            'allow_extra_fields' => true,
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addCollectorLogicalOperatorField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_COLLECTOR_LOGICAL_OPERATOR, 'choice', [
            'label' => 'Logical operator for combining multiple collectors',
            'choices' => $this->getCollectorLogicalOperators(),
            'required' => true,
        ]);

        return $this;
    }

}
