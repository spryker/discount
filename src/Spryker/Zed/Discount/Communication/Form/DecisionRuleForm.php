<?php

namespace Spryker\Zed\Discount\Communication\Form;

use Symfony\Component\Form\FormBuilderInterface;

class DecisionRuleForm extends AbstractRuleForm
{

    const FIELD_DECISION_RULE_PLUGIN = 'decision_rule_plugin';
    const FIELD_VALUE = 'value';
    const FIELD_REMOVE = 'remove';
    const FIELD_ID_DISCOUNT_DECISION_RULE = 'id_discount_decision_rule';

    /**
     * @return string
     */
    public function getName()
    {
        return 'decision_rule';
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
            ->addIdDiscountDecisionRuleField($builder)
            ->addDecisionRulePluginField($builder)
            ->addValueField($builder)
            ->addRemoveButton($builder);
    }

    /**
     * @param FormBuilderInterface $builder
     *
     * @return self
     */
    protected function addDecisionRulePluginField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_DECISION_RULE_PLUGIN, 'choice', [
            'label' => 'Decision Rule',
            'multiple' => false,
            'choices' => $this->getAvailableDecisionRulePlugins(),
        ]);

        return $this;
    }

    /**
     * @param FormBuilderInterface $builder
     *
     * @return self
     */
    protected function addValueField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_VALUE, 'text', [
            'label' => 'Value',
        ]);

        return $this;
    }

    /**
     * @param FormBuilderInterface $builder
     *
     * @return self
     */
    protected function addRemoveButton(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_REMOVE, 'button', [
            'attr' => [
                'class' => 'btn btn-xs btn-danger remove-form-collection',
            ],
        ]);

        return $this;
    }

    /**
     * @param FormBuilderInterface $builder
     *
     * @return self
     */
    protected function addIdDiscountDecisionRuleField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_ID_DISCOUNT_DECISION_RULE, 'hidden');

        return $this;
    }

}
