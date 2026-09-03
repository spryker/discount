<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Communication\Form;

use DateTime;
use Generated\Shared\Transfer\DiscountTableCriteriaTransfer;
use Spryker\Zed\Discount\Communication\Form\DataProvider\TableFilterFormDataProvider;
use Spryker\Zed\Gui\Communication\Form\Type\DateTimePickerType;
use Spryker\Zed\Kernel\Communication\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @method \Spryker\Zed\Discount\Business\DiscountFacadeInterface getFacade()
 * @method \Spryker\Zed\Discount\Communication\DiscountCommunicationFactory getFactory()
 * @method \Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\Discount\DiscountConfig getConfig()
 * @method \Spryker\Zed\Discount\Persistence\DiscountRepositoryInterface getRepository()
 */
class TableFilterForm extends AbstractType
{
    /**
     * @var string
     */
    protected const FIELD_STATUS = 'status';

    /**
     * @var string
     */
    protected const FIELD_TYPES = 'types';

    /**
     * @var string
     */
    protected const FIELD_STORES = 'stores';

    /**
     * @var string
     */
    protected const FIELD_VALID_FROM = 'validFrom';

    /**
     * @var string
     */
    protected const FIELD_VALID_TO = 'validTo';

    /**
     * @var string
     */
    protected const PLACEHOLDER_STATUS = 'Select Status';

    /**
     * @var string
     */
    protected const PLACEHOLDER_TYPES = 'Select Types';

    /**
     * @var string
     */
    protected const PLACEHOLDER_STORES = 'Select Stores';

    /**
     * @var string
     */
    protected const LABEL_STATUS = 'Status';

    /**
     * @var string
     */
    protected const LABEL_TYPE = 'Type';

    /**
     * @var string
     */
    protected const LABEL_STORE = 'Store';

    /**
     * @var string
     */
    protected const LABEL_VALID_FROM = 'Valid from';

    /**
     * @var string
     */
    protected const LABEL_VALID_TO = 'Valid to';

    /**
     * @var string
     */
    protected const DATE_TIME_FORMAT = 'Y-m-d\TH:i';

    /**
     * @var string
     */
    protected const RANGE_ROLE_START = 'start';

    /**
     * @var string
     */
    protected const RANGE_ROLE_END = 'end';

    /**
     * @var string
     */
    protected const RANGE_GROUP_VALIDITY = 'discount-validity';

    public function getBlockPrefix(): string
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            TableFilterFormDataProvider::OPTION_STATUSES,
            TableFilterFormDataProvider::OPTION_TYPES,
            TableFilterFormDataProvider::OPTION_STORES,
        ]);

        $resolver->setDefaults([
            'data_class' => DiscountTableCriteriaTransfer::class,
            'csrf_protection' => false,
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod(Request::METHOD_GET);

        $this
            ->addStatusField($builder, $options)
            ->addTypeField($builder, $options)
            ->addStoreField($builder, $options)
            ->addValidFromField($builder)
            ->addValidToField($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return $this
     */
    protected function addStatusField(FormBuilderInterface $builder, array $options = [])
    {
        $builder->add(static::FIELD_STATUS, ChoiceType::class, [
            'label' => static::LABEL_STATUS,
            'placeholder' => static::PLACEHOLDER_STATUS,
            'required' => false,
            'expanded' => false,
            'choices' => $options[TableFilterFormDataProvider::OPTION_STATUSES] ?? [],
            'attr' => [
                'class' => 'spryker-form-select2combobox',
                'data-placeholder' => $this->getFactory()->getTranslatorFacade()->trans(static::PLACEHOLDER_STATUS),
                'data-clearable' => true,
                'data-disable-search' => true,
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return $this
     */
    protected function addTypeField(FormBuilderInterface $builder, array $options = [])
    {
        $builder->add(static::FIELD_TYPES, ChoiceType::class, [
            'label' => static::LABEL_TYPE,
            'placeholder' => static::PLACEHOLDER_TYPES,
            'required' => false,
            'multiple' => true,
            'expanded' => false,
            'choices' => $options[TableFilterFormDataProvider::OPTION_TYPES] ?? [],
            'attr' => [
                'class' => 'spryker-form-select2combobox',
                'data-placeholder' => $this->getFactory()->getTranslatorFacade()->trans(static::PLACEHOLDER_TYPES),
                'data-clearable' => true,
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return $this
     */
    protected function addStoreField(FormBuilderInterface $builder, array $options = [])
    {
        $builder->add(static::FIELD_STORES, ChoiceType::class, [
            'label' => static::LABEL_STORE,
            'placeholder' => static::PLACEHOLDER_STORES,
            'required' => false,
            'multiple' => true,
            'expanded' => false,
            'choices' => $options[TableFilterFormDataProvider::OPTION_STORES] ?? [],
            'attr' => [
                'class' => 'spryker-form-select2combobox',
                'data-placeholder' => $this->getFactory()->getTranslatorFacade()->trans(static::PLACEHOLDER_STORES),
                'data-clearable' => true,
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
        $builder->add(
            static::FIELD_VALID_FROM,
            $this->getDateTimeFieldType(),
            $this->getDateTimeFieldOptions(static::LABEL_VALID_FROM, static::RANGE_ROLE_START),
        );

        $builder->get(static::FIELD_VALID_FROM)
            ->addModelTransformer($this->createDateTimeTransformer());

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addValidToField(FormBuilderInterface $builder)
    {
        $builder->add(
            static::FIELD_VALID_TO,
            $this->getDateTimeFieldType(),
            $this->getDateTimeFieldOptions(static::LABEL_VALID_TO, static::RANGE_ROLE_END),
        );

        $builder->get(static::FIELD_VALID_TO)
            ->addModelTransformer($this->createDateTimeTransformer());

        return $this;
    }

    protected function getDateTimeFieldType(): string
    {
        if ($this->isGuiDateTimePickerTypeAvailable()) {
            return DateTimePickerType::class;
        }

        return DateTimeType::class;
    }

    /**
     * @param string $label
     * @param string $rangeRole
     *
     * @return array<string, mixed>
     */
    protected function getDateTimeFieldOptions(string $label, string $rangeRole): array
    {
        $options = [
            'label' => $label,
            'required' => false,
        ];

        // The picker keeps both ends of the range consistent, which the native control cannot do.
        if ($this->isGuiDateTimePickerTypeAvailable()) {
            return $options + [
                'range_group' => static::RANGE_GROUP_VALIDITY,
                'range_role' => $rangeRole,
            ];
        }

        return $options + [
            'widget' => 'single_text',
            'html5' => true,
        ];
    }

    protected function isGuiDateTimePickerTypeAvailable(): bool
    {
        return class_exists(DateTimePickerType::class);
    }

    /**
     * @return \Symfony\Component\Form\DataTransformerInterface<\DateTimeInterface|null, string|null>
     */
    protected function createDateTimeTransformer(): DataTransformerInterface
    {
        return new CallbackTransformer(
            function ($dateAsString) {
                if (!$dateAsString) {
                    return null;
                }

                if ($dateAsString instanceof DateTime) {
                    return $dateAsString;
                }

                return new DateTime($dateAsString);
            },
            function ($dateAsObject) {
                if (!$dateAsObject) {
                    return null;
                }

                if ($dateAsObject instanceof DateTime) {
                    return $dateAsObject->format(static::DATE_TIME_FORMAT);
                }

                return $dateAsObject;
            },
        );
    }
}
