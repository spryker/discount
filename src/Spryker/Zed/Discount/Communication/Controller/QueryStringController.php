<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Communication\Controller;

use Spryker\Zed\Application\Communication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


/**
 * @method \Spryker\Zed\Discount\Communication\DiscountCommunicationFactory getFactory()
 * @method \Spryker\Zed\Discount\Persistence\DiscountQueryContainer getQueryContainer()
 * @method \Spryker\Zed\Discount\Business\DiscountFacade getFacade()
 */
class QueryStringController extends AbstractController
{
    const URL_PARAM_TYPE = 'type';
    const URL_PARAM_FIELD = 'field';
    const URL_PARAM_QUERY_STRING = 'query-string';

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ruleFieldsAction(Request $request)
    {
        $type = $request->query->get(self::URL_PARAM_TYPE);

        return new JsonResponse(
            $this->getFacade()
                ->getQueryStringFieldsByType($type)
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ruleFieldExpressionsAction(Request $request)
    {
        $type = $request->query->get(self::URL_PARAM_TYPE);
        $field = $request->query->get(self::URL_PARAM_FIELD);

        return new JsonResponse(
            $this->getFacade()
                ->getQueryStringFieldExpressionsForField($type, $field)
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function logicalComparatorsAction(Request $request)
    {
        $type = $request->query->get(self::URL_PARAM_TYPE);

        return new JsonResponse(
            $this->getFacade()
                ->getQueryStringLogicalComparators($type)
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     */
    public function validateQueryStringAction(Request $request)
    {
        $type = $request->query->get(self::URL_PARAM_TYPE);
        $queryString = $request->query->get(self::URL_PARAM_QUERY_STRING);

        return new JsonResponse(
            $this->getFacade()
                ->validateQueryStringByType($type, $queryString)
        );
    }

}
