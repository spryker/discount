<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Communication\Controller;

use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\VoucherCreateInfoTransfer;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Gui\Communication\Table\TableParameters;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\Discount\Communication\DiscountCommunicationFactory getFactory()
 * @method \Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\Discount\Business\DiscountFacadeInterface getFacade()
 * @method \Spryker\Zed\Discount\Persistence\DiscountRepositoryInterface getRepository()
 */
class IndexController extends AbstractController
{
    /**
     * @var string
     */
    public const URL_PARAM_ID_DISCOUNT = 'id-discount';

    /**
     * @var string
     */
    public const URL_PARAM_BATCH_PARAMETER = 'batch';

    /**
     * @var string
     */
    public const URL_PARAM_ID_POOL = 'id-pool';

    /**
     * @var string
     */
    public const URL_PARAM_VISIBILITY = 'visibility';

    /**
     * @var string
     */
    public const URL_PARAM_REDIRECT_URL = 'redirect-url';

    /**
     * @uses \Spryker\Zed\Http\Communication\Plugin\Application\HttpApplicationPlugin::SERVICE_SUB_REQUEST
     *
     * @var string
     */
    protected const SERVICE_SUB_REQUEST = 'sub_request';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function createAction(Request $request)
    {
        $discountForm = $this->getFactory()->getDiscountForm();
        $discountForm->handleRequest($request);

        if ($discountForm->isSubmitted() && $discountForm->isValid()) {
            $discountConfiguratorResponseTransfer = $this->getFacade()
                ->createDiscount($discountForm->getData());
            $idDiscount = $discountConfiguratorResponseTransfer->getDiscountConfiguratorOrFail()
                ->getDiscountGeneralOrFail()
                ->getIdDiscountOrFail();

            /** @var \Generated\Shared\Transfer\DiscountConfiguratorTransfer $discountConfiguratorTransfer */
            $discountConfiguratorTransfer = $discountForm->getData();
            $discountType = $discountConfiguratorTransfer->getDiscountGeneral()->getDiscountType();

            $this->addSuccessMessage('Discount successfully created, but not activated.');

            return new RedirectResponse(
                $this->createEditRedirectUrl($idDiscount, $discountType),
            );
        }

        $discountFormTabs = $this
            ->getFactory()
            ->createDiscountFormTabs($discountForm);

        return [
            'discountForm' => $discountForm->createView(),
            'discountFormTabs' => $discountFormTabs->createView(),
        ];
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function editAction(Request $request)
    {
        $idDiscount = $this->castId($request->query->get(static::URL_PARAM_ID_DISCOUNT));

        $discountConfiguratorTransfer = $this->getFactory()
            ->createDiscountFormDataProvider()
            ->getData($idDiscount);

        if ($discountConfiguratorTransfer === null) {
            $this->addErrorMessage("Discount with id %s doesn't exist", ['%s' => $idDiscount]);

            return $this->redirectResponse($this->getFactory()->getConfig()->getDefaultRedirectUrl());
        }

        $discountForm = $this->getFactory()->getDiscountForm($idDiscount, $discountConfiguratorTransfer);
        $isDiscountFormSubmittedSuccessfully = $this->isDiscountFormSubmittedSuccessfully($request, $discountForm);

        $voucherFormDataProvider = $this->getFactory()->createVoucherFormDataProvider();
        $voucherForm = $this->getFactory()->getVoucherForm(
            $voucherFormDataProvider->getData($idDiscount),
            $voucherFormDataProvider->getOptions(),
        );
        $isVoucherFormSubmittedSuccessfully = $this->isVoucherFormSubmittedSuccessfully($request, $voucherForm);

        if ($isDiscountFormSubmittedSuccessfully || $isVoucherFormSubmittedSuccessfully) {
            return $this->redirectResponse($this->createEditRedirectUrl($idDiscount));
        }

        $voucherCodesTable = $this->renderVoucherCodeTable($request, $discountConfiguratorTransfer);

        $discountFormTabs = $this
            ->getFactory()
            ->createDiscountFormTabs($discountForm, $voucherForm, $discountConfiguratorTransfer);

        $discountVisibilityForm = $this->getFactory()->createDiscountVisibilityForm();

        return [
            'discountForm' => $discountForm->createView(),
            'idDiscount' => $idDiscount,
            'voucherCodesTable' => $voucherCodesTable,
            'voucherForm' => $voucherForm->createView(),
            'discountConfigurator' => $discountConfiguratorTransfer,
            'discountFormTabs' => $discountFormTabs->createView(),
            'discountVisibilityForm' => $discountVisibilityForm->createView(),
        ];
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Form\FormInterface $voucherForm
     *
     * @return bool
     */
    protected function isVoucherFormSubmittedSuccessfully(Request $request, FormInterface $voucherForm): bool
    {
        $voucherForm->handleRequest($request);

        if ($voucherForm->isSubmitted() && $voucherForm->isValid()) {
            $voucherCreateInfoTransfer = $this->getFacade()->saveVoucherCodes($voucherForm->getData());

            return $this->addVoucherCreateMessage($voucherCreateInfoTransfer);
        }

        /** @var \Symfony\Component\Form\FormError $formError */
        foreach ($voucherForm->getErrors(true) as $formError) {
            $this->addErrorMessage($formError->getMessage());
        }

        return false;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    public function viewAction(Request $request)
    {
        $idDiscount = $this->castId($request->query->get(static::URL_PARAM_ID_DISCOUNT));

        $discountConfiguratorTransfer = $this->getFactory()
            ->createDiscountFormDataProvider()
            ->getData($idDiscount);

        if ($discountConfiguratorTransfer === null) {
            $this->addErrorMessage("Discount with id %s doesn't exist", ['%s' => $idDiscount]);

            return $this->redirectResponse($this->getFactory()->getConfig()->getDefaultRedirectUrl());
        }

        $voucherCodesTable = $this->renderVoucherCodeTable($request, $discountConfiguratorTransfer);

        $discountConfiguratorTransfer = $this->getFactory()
            ->createDiscountAmountFormatter()
            ->format($discountConfiguratorTransfer);

        return [
            'discountConfigurator' => $discountConfiguratorTransfer,
            'voucherCodesTable' => $voucherCodesTable,
            'renderedBlocks' => $this->renderBlocks($request, $idDiscount),
        ];
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $idDiscount
     *
     * @return array
     */
    protected function renderBlocks(Request $request, $idDiscount)
    {
        $discountViewBlockPlugins = $this->getFactory()->getDiscountViewBlockProviderPlugins();

        $subRequest = clone $request;
        $subRequest->setMethod(Request::METHOD_POST);
        $subRequest->request->set(static::URL_PARAM_ID_DISCOUNT, (string)$idDiscount);

        $renderedBlocks = [];
        foreach ($discountViewBlockPlugins as $discountViewBlockPlugin) {
            $renderedBlocks[] = $this->getSubRequestHandler()
                ->handleSubRequest($subRequest, $discountViewBlockPlugin->getUrl())
                ->getContent();
        }

        return $renderedBlocks;
    }

    /**
     * @return \Spryker\Zed\Application\Business\Model\Request\SubRequestHandlerInterface
     */
    protected function getSubRequestHandler()
    {
        return $this->getApplication()->get(static::SERVICE_SUB_REQUEST);
    }

    /**
     * @return array
     */
    public function listAction()
    {
        $table = $this->getFactory()->createDiscountsTable();

        return [
            'discountsTable' => $table->render(),
        ];
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function listTableAction()
    {
        $table = $this->getFactory()->createDiscountsTable();

        return $this->jsonResponse(
            $table->fetchData(),
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function tableAction(Request $request)
    {
        $idPool = $this->castId($request->query->get(static::URL_PARAM_ID_POOL));
        $idDiscount = $this->castId($request->query->get(static::URL_PARAM_ID_DISCOUNT));
        $table = $this->getGeneratedCodesTable($request, $idPool, $idDiscount);

        return $this->jsonResponse(
            $table->fetchData(),
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function toggleDiscountVisibilityAction(Request $request)
    {
        $idDiscount = $this->castId($request->query->get(static::URL_PARAM_ID_DISCOUNT));

        $form = $this->getFactory()->createDiscountVisibilityForm()->handleRequest($request);
        $redirectUrl = (string)$request->query->get(static::URL_PARAM_REDIRECT_URL);
        if (!$redirectUrl) {
            $redirectUrl = $this->createEditRedirectUrl($idDiscount);
        }

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addErrorMessage('CSRF token is not valid.');

            return $this->redirectResponse($redirectUrl);
        }

        $visibility = $request->get(static::URL_PARAM_VISIBILITY);

        $isActive = mb_convert_case($visibility, MB_CASE_LOWER, 'UTF-8') == 'activate' ? true : false;

        $visibilityChanged = $this->getFacade()->toggleDiscountVisibility($idDiscount, $isActive);

        if ($visibilityChanged === false) {
            $this->addErrorMessage('Could not change discount visibility.');
        } else {
            $this->addSuccessMessage(sprintf(
                'Discount successfully %s.',
                $isActive ? 'activated' : 'deactivated',
            ));
        }

        return new RedirectResponse($redirectUrl);
    }

    /**
     * @param \Generated\Shared\Transfer\VoucherCreateInfoTransfer $voucherCreateInfoInterface
     *
     * @return bool
     */
    protected function addVoucherCreateMessage(VoucherCreateInfoTransfer $voucherCreateInfoInterface): bool
    {
        if ($voucherCreateInfoInterface->getType() === DiscountConstants::MESSAGE_TYPE_SUCCESS) {
            $this->addSuccessMessage($voucherCreateInfoInterface->getMessage());

            return true;
        }
        if ($voucherCreateInfoInterface->getType() === DiscountConstants::MESSAGE_TYPE_ERROR) {
            $this->addErrorMessage($voucherCreateInfoInterface->getMessage());

            return false;
        }

        $this->addInfoMessage($voucherCreateInfoInterface->getMessage());

        return true;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $idPool
     * @param int $idDiscount
     *
     * @return \Spryker\Zed\Discount\Communication\Table\DiscountVoucherCodesTable
     */
    protected function getGeneratedCodesTable(Request $request, $idPool, $idDiscount)
    {
        $batch = $request->query->getInt(static::URL_PARAM_BATCH_PARAMETER);
        $tableParameters = TableParameters::getTableParameters($request);

        return $this->getFactory()->createDiscountVoucherCodesTable(
            $tableParameters,
            $idPool,
            $idDiscount,
            $batch,
        );
    }

    /**
     * @param int $idDiscount
     * @param string $discountType
     *
     * @return string
     */
    protected function createEditRedirectUrl($idDiscount, $discountType = '')
    {
        $hash = '';
        if ($discountType === DiscountConstants::TYPE_VOUCHER) {
            $hash = '#codes';
        }
        $redirectUrl = Url::generate(
            '/discount/index/edit',
            [
                    static::URL_PARAM_ID_DISCOUNT => $idDiscount,
                ],
        )->build() . $hash;

        return $redirectUrl;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Generated\Shared\Transfer\DiscountConfiguratorTransfer $discountConfiguratorTransfer
     *
     * @return string
     */
    protected function renderVoucherCodeTable(
        Request $request,
        DiscountConfiguratorTransfer $discountConfiguratorTransfer
    ) {
        $voucherCodesTable = '';
        if ($discountConfiguratorTransfer->getDiscountVoucher()) {
            $voucherCodesTable = $this->getGeneratedCodesTable(
                $request,
                $discountConfiguratorTransfer->getDiscountVoucher()->getFkDiscountVoucherPool(),
                $discountConfiguratorTransfer->getDiscountGeneral()->getIdDiscount(),
            )->render();
        }

        return $voucherCodesTable;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Form\FormInterface $discountForm
     *
     * @return bool
     */
    protected function isDiscountFormSubmittedSuccessfully(Request $request, FormInterface $discountForm): bool
    {
        $discountForm->handleRequest($request);

        if ($discountForm->isSubmitted()) {
            if ($discountForm->isValid()) {
                $discountConfiguratorResponseTransfer = $this->getFacade()->updateDiscountWithValidation($discountForm->getData());
                if ($discountConfiguratorResponseTransfer->getIsSuccessfulOrFail()) {
                    $this->addSuccessMessage('Discount successfully updated.');
                }

                return true;
            }

            $this->addErrorMessage('Please fill all required fields.');
        }

        return false;
    }
}
