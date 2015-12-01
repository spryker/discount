<?php

namespace SprykerFeature\Zed\Discount\Communication\Form;

use Generated\Shared\Transfer\CartRuleTransfer;
use SprykerEngine\Shared\Transfer\TransferInterface;
use Symfony\Component\Form\FormBuilderInterface;

class CartRuleForm extends AbstractRuleForm
{

    const FIELD_DISPLAY_NAME = 'display_name';
    const FIELD_DESCRIPTION = 'description';
    const FIELD_AMOUNT = 'amount';
    const FIELD_TYPE = 'type';
    const FIELD_VALID_FROM = 'valid_from';
    const FIELD_VALID_TO = 'valid_to';
    const FIELD_IS_PRIVILEGED = 'is_privileged';
    const FIELD_IS_ACTIVE = 'is_active';
    const FIELD_CALCULATOR_PLUGIN = 'calculator_plugin';
    const FIELD_COLLECTOR_PLUGINS = 'collector_plugins';
    const FIELD_DECISION_RULES = 'decision_rules';
    const FIELD_COLLECTOR_LOGICAL_OPERATOR = 'collector_logical_operator';

    const DATE_NOW = 'now';

    /**
     * @return CartRuleTransfer|TransferInterface
     */
    public function populateFormFields()
    {
        return [
            'decision_rules' => [
                'rule_1' => [
                    'value' => '',
                    'rules' => '',
                ],
            ],
            'collector_plugins' => [
                'plugin_1' => [
                    'collector_plugin' => '',
                    'value' => '',
                ],
            ],
            'group' => [],
        ];
    }

    /**
     * @return CartRuleTransfer
     */
    protected function getDataClass()
    {
        //return new CartRuleTransfer();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(self::FIELD_DISPLAY_NAME, 'text', [
                'constraints' => [
                    $this->getConstraints()->createConstraintNotBlank(),
                ],
            ])
            ->add(self::FIELD_DESCRIPTION, 'textarea')
            ->add(self::FIELD_AMOUNT, 'text', [
                'label' => 'Amount',
                'constraints' => [
                    $this->getConstraints()->createConstraintNotBlank(),
                ],
            ])
            ->add(self::FIELD_CALCULATOR_PLUGIN, 'choice', [
                'label' => 'Calculator Plugin',
                'choices' => $this->getAvailableCalculatorPlugins(),
                'empty_data' => null,
                'required' => false,
                'placeholder' => 'Default',
            ])
            ->add(self::FIELD_COLLECTOR_PLUGINS, 'collection', [
                'type' => new CollectorPluginForm($this->availableCollectorPlugins),
                'label' => null,
                'allow_add' => true,
                'allow_delete' => true,
                'allow_extra_fields' => true,
            ])
            ->add(self::FIELD_COLLECTOR_LOGICAL_OPERATOR, 'choice', [
                'label' => 'Logical operator for combining multiple collectors',
                'choices' => $this->getCollectorLogicalOperators(),
                'required' => true,
            ])
            ->add(self::FIELD_VALID_FROM, 'date')
            ->add(self::FIELD_VALID_TO, 'date')
            ->add(self::FIELD_IS_PRIVILEGED, 'checkbox', [
                'label' => 'Is Combinable with other discounts',
            ])
            ->add(self::FIELD_IS_ACTIVE, 'checkbox', [
                'label' => 'Is Active',
            ])
            ->add(self::FIELD_DECISION_RULES, 'collection', [
                'type' => new DecisionRuleForm($this->availableDecisionRulePlugins),
                'label' => null,
                'allow_add' => true,
                'allow_delete' => true,
                'allow_extra_fields' => true,
            ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cart_rule';
    }

}
