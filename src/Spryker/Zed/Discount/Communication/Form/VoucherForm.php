<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Communication\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class VoucherForm extends AbstractType
{

    const FIELD_QUANTITY = 'quantity';
    const FIELD_CUSTOM_CODE = 'custom_code';
    const FIELD_RANDOM_GENERATED_CODE_LENGTH = 'random_generated_code_length';
    const FIELD_MAX_NUMBER_OF_USES = 'max_number_of_uses';
    const FIELD_ID_DISCOUNT = 'id_discount';

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array|string[] $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addQuantityField($builder)
            ->addCustomCodeField($builder)
            ->addRandomGeneratedCodeLength($builder)
            ->addMaxNumberOfUsesField($builder)
            ->addIdDiscount($builder)
            ->addSubmitButton($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addQuantityField(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_QUANTITY, 'text', [
            'label' => 'Quantity*',
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
    protected function addCustomCodeField(FormBuilderInterface $builder)
    {
        $builder->add(
            self::FIELD_CUSTOM_CODE,
            'text',
            [
                'required' => false
            ]
        );

        return $this;

    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addRandomGeneratedCodeLength(FormBuilderInterface $builder)
    {
        $builder->add(
            self::FIELD_RANDOM_GENERATED_CODE_LENGTH,
            'choice',
            [
                'label' => 'Random generated code length',
                'placeholder' => 'No additional random characters',
                'required' => false,
                'choices' => $this->createCodeLengthRangeList()
            ]
        );

        return $this;

    }


    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addMaxNumberOfUsesField(FormBuilderInterface $builder)
    {
        $builder->add(
            self::FIELD_MAX_NUMBER_OF_USES,
            'text',
            [
                'label' => 'Max number of uses (0 = Infinite usage)'
            ]
        );

        return $this;

    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addIdDiscount(FormBuilderInterface $builder)
    {
        $builder->add(self::FIELD_ID_DISCOUNT, 'hidden');

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addSubmitButton(FormBuilderInterface $builder)
    {
        $builder->add('generate', 'submit');

        return $this;
    }

        /**
         * Returns the name of this type.
         *
         * @return string The name of this type
         */
    public function getName()
    {
        return 'discount_voucher';
    }

    /**
     * @return array|int[]
     */
    protected function createCodeLengthRangeList()
    {
        $range = range(3, 10);
        return array_combine(array_values($range), $range);
    }

}
