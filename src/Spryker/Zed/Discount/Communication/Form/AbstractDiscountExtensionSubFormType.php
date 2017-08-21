<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Communication\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

abstract class AbstractDiscountExtensionSubFormType extends AbstractType
{

    const TEMPLATE_PATH = 'template_path';

    /**
     * Return path to template you want to use for custom discount form type, this template will receive
     * "form" - is a parent form view object and "child" - is a current form type form view object.
     *
     * @return string
     */
    abstract protected function getTemplatePath();

    /**
     * @param \Symfony\Component\Form\FormView $view The view
     * @param \Symfony\Component\Form\FormInterface $form The form
     * @param array $options The options
     *
     * @return void
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars[self::TEMPLATE_PATH] = $this->getTemplatePath();
    }

}
