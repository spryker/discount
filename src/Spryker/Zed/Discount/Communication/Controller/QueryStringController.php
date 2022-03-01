<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Communication\Controller;

use Spryker\Zed\Kernel\Communication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\Discount\Communication\DiscountCommunicationFactory getFactory()
 * @method \Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\Discount\Business\DiscountFacadeInterface getFacade()
 * @method \Spryker\Zed\Discount\Persistence\DiscountRepositoryInterface getRepository()
 */
class QueryStringController extends AbstractController
{
    /**
     * @var string
     */
    public const URL_PARAM_TYPE = 'type';

    /**
     * @var string
     */
    public const URL_PARAM_FIELD = 'field';

    /**
     * @var string
     */
    public const URL_PARAM_QUERY_STRING = 'query-string';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function ruleFieldsAction(Request $request)
    {
        /** @var string $type */
        $type = $request->query->get(static::URL_PARAM_TYPE);

        $transformer = $this->getFactory()->createJavascriptQueryBuilderTransformer();

        return new JsonResponse(
            $transformer->getFilters($type),
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function ruleFieldExpressionsAction(Request $request)
    {
        /** @var string $type */
        $type = $request->query->get(static::URL_PARAM_TYPE);
        /** @var string $field */
        $field = $request->query->get(static::URL_PARAM_FIELD);

        return new JsonResponse(
            $this->getFacade()
                ->getQueryStringFieldExpressionsForField($type, $field),
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function logicalComparatorsAction(Request $request)
    {
        /** @var string $type */
        $type = $request->query->get(static::URL_PARAM_TYPE);

        return new JsonResponse(
            $this->getFacade()
                ->getQueryStringLogicalComparators($type),
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function validateQueryStringAction(Request $request)
    {
        /** @var string $type */
        $type = $request->query->get(static::URL_PARAM_TYPE);
        /** @var string $queryString */
        $queryString = $request->query->get(static::URL_PARAM_QUERY_STRING);

        return new JsonResponse(
            $this->getFacade()
                ->validateQueryStringByType($type, $queryString),
        );
    }
}
