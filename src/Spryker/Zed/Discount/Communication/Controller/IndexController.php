<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Communication\Controller;

use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\VoucherCreateInfoTransfer;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Shared\Url\Url;
use Spryker\Zed\Application\Communication\Controller\AbstractController;
use Spryker\Zed\Discount\Business\QueryString\SpecificationBuilder;
use Spryker\Zed\Gui\Communication\Table\TableParameters;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\Discount\Communication\DiscountCommunicationFactory getFactory()
 * @method \Spryker\Zed\Discount\Persistence\DiscountQueryContainer getQueryContainer()
 * @method \Spryker\Zed\Discount\Business\DiscountFacade getFacade()
 */
class IndexController extends AbstractController
{

    const URL_PARAM_ID_DISCOUNT = 'id-discount';
    const URL_PARAM_BATCH_PARAMETER = 'batch';
    const URL_PARAM_ID_POOL = 'id-pool';
    const URL_PARAM_VISIBILITY = 'visibility';
    const URL_PARAM_REDIRECT_URL = 'redirect-url';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $discountForm = $this->getFactory()->createDiscountForm();
        $discountForm->handleRequest($request);

        if ($discountForm->isValid()) {
            $idDiscount = $this->getFacade()
                ->saveDiscount($discountForm->getData());

            $this->addSuccessMessage('Discount successfully created, but not activated.');

            return new RedirectResponse(
                $this->createEditRedirectUrl($idDiscount)
            );
        }

        return array_merge([
            'discountForm' => $discountForm->createView(),
        ], $this->getQueryStringMetData());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Request $request)
    {
        $idDiscount = $this->castId($request->query->get(self::URL_PARAM_ID_DISCOUNT));

        $discountConfiguratorTransfer = $this->getFacade()
            ->getHydratedDiscountConfiguratorByIdDiscount($idDiscount);

        $discountForm = $this->getFactory()->createDiscountForm();
        $discountForm->setData($discountConfiguratorTransfer);
        $discountForm->handleRequest($request);

        if ($discountForm->isValid()) {
            $isUpdated = $this->getFacade()->updateDiscount($discountForm->getData());
            if ($isUpdated === true) {
                $this->addSuccessMessage('Discount successfully updated.');
            }
        }

        $voucherFormDataProvider = $this->getFactory()->createVoucherFormDataProvider();
        $voucherForm = $this->getFactory()->createVoucherForm(
            $voucherFormDataProvider->getData($idDiscount)
        );
        $voucherForm->handleRequest($request);

        if ($voucherForm->isValid()) {
            $voucherCreateInfoTransfer = $this->getFacade()->saveVoucherCodes($voucherForm->getData());
            $this->addVoucherCreateMessage($voucherCreateInfoTransfer);

            return new RedirectResponse(
                $this->createEditRedirectUrl($idDiscount)
            );
        }

        $voucherCodesTable = $this->renderVoucherCodeTable($request, $discountConfiguratorTransfer);

        return array_merge([
            'discountForm' => $discountForm->createView(),
            'idDiscount' => $idDiscount,
            'voucherCodesTable' => $voucherCodesTable,
            'voucherForm' => $voucherForm->createView(),
            'discountConfigurator' => $discountConfiguratorTransfer,
        ], $this->getQueryStringMetData());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function viewAction(Request $request)
    {
        $idDiscount = $this->castId($request->query->get(self::URL_PARAM_ID_DISCOUNT));

        $discountConfiguratorTransfer = $this->getFacade()
            ->getHydratedDiscountConfiguratorByIdDiscount($idDiscount);

        $voucherCodesTable = $this->renderVoucherCodeTable($request, $discountConfiguratorTransfer);

        return [
            'discountConfigurator' => $discountConfiguratorTransfer,
            'voucherCodesTable' => $voucherCodesTable,
        ];
    }

    /**
     * @return array
     */
    protected function getQueryStringMetData()
    {
        return [
            'collectorTypes' => $this->getFacade()->getQueryStringFieldsByType(SpecificationBuilder::TYPE_COLLECTOR),
            'decisionRuleTypes' => $this->getFacade()->getQueryStringFieldsByType(SpecificationBuilder::TYPE_DECISION_RULE),
            'booleanComparators' => $this->getFacade()->getQueryStringLogicalComparators(SpecificationBuilder::TYPE_COLLECTOR),
            'comparatorExpressions' => $this->getFacade()->getQueryStringComparatorExpressions(SpecificationBuilder::TYPE_COLLECTOR),
        ];
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
            $table->fetchData()
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function tableAction(Request $request)
    {
        $idPool = $this->castId($request->query->get(self::URL_PARAM_ID_POOL));
        $idDiscount = $this->castId($request->query->get(self::URL_PARAM_ID_DISCOUNT));
        $table = $this->getGeneratedCodesTable($request, $idPool, $idDiscount);

        return $this->jsonResponse(
            $table->fetchData()
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function toggleDiscountVisibilityAction(Request $request)
    {
        $idDiscount = $this->castId($request->query->get(self::URL_PARAM_ID_DISCOUNT));
        $visibility = $request->query->get(self::URL_PARAM_VISIBILITY);
        $redirectUrl = $request->query->get(self::URL_PARAM_REDIRECT_URL);

        $isActive = $visibility == 'activate' ? true : false;

        $visibilityChanged = $this->getFacade()->toggleDiscountVisibility($idDiscount, $isActive);

        if ($visibilityChanged === false) {
            $this->addErrorMessage('Could not change discount visibility.');
        } else {
            $this->addSuccessMessage(sprintf(
                'Discount successfully %s.',
                $isActive ? 'activated' : 'deactivated'
            ));
        }

        if (!$redirectUrl) {
            $redirectUrl = $this->createEditRedirectUrl($idDiscount);
        }

        return new RedirectResponse($redirectUrl);
    }

    /**
     * @param \Generated\Shared\Transfer\VoucherCreateInfoTransfer $voucherCreateInfoInterface
     *
     * @return $this
     */
    protected function addVoucherCreateMessage(VoucherCreateInfoTransfer $voucherCreateInfoInterface)
    {
        if ($voucherCreateInfoInterface->getType() === DiscountConstants::MESSAGE_TYPE_SUCCESS) {
            return $this->addSuccessMessage($voucherCreateInfoInterface->getMessage());
        }
        if ($voucherCreateInfoInterface->getType() === DiscountConstants::MESSAGE_TYPE_ERROR) {
            return $this->addErrorMessage($voucherCreateInfoInterface->getMessage());
        }

        return $this->addInfoMessage($voucherCreateInfoInterface->getMessage());
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
        $batch = $request->query->get(self::URL_PARAM_BATCH_PARAMETER);
        $tableParameters = TableParameters::getTableParameters($request);

        return $this->getFactory()->createDiscountVoucherCodesTable(
            $tableParameters,
            $idPool,
            $idDiscount,
            $batch
        );
    }

    /**
     * @param int $idDiscount
     *
     * @return string
     */
    protected function createEditRedirectUrl($idDiscount)
    {
        $redirectUrl = Url::generate(
            '/discount/index/edit',
            [
                self::URL_PARAM_ID_DISCOUNT => $idDiscount
            ]
        )->build();

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
                $discountConfiguratorTransfer->getDiscountGeneral()->getIdDiscount()
            )->render();
        }
        return $voucherCodesTable;
    }

}
