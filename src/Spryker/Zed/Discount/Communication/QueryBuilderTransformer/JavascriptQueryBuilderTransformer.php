<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Communication\QueryBuilderTransformer;

use Spryker\Zed\Discount\Business\DiscountFacade;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaProviderFactoryInterface;

class JavascriptQueryBuilderTransformer implements JavascriptQueryBuilderTransformerInterface
{
    /**
     * @var \Spryker\Zed\Discount\Business\DiscountFacade
     */
    protected $discountFacade;

    /**
     * @var array<string>
     */
    protected $queryOperatorMapping = [
        '=' => 'equal',
        '!=' => 'not_equal',
        '<' => 'less',
        '<=' => 'less_or_equal',
        '>' => 'greater',
        '>=' => 'greater_or_equal',
        'does not contain' => 'not_contains',
        'is in' => 'in',
        'is not in' => 'not_in',
    ];

    protected MetaProviderFactoryInterface $metaProviderFactory;

    public function __construct(DiscountFacade $discountFacade, MetaProviderFactoryInterface $metaProviderFactory)
    {
        $this->discountFacade = $discountFacade;
        $this->metaProviderFactory = $metaProviderFactory;
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getFilters($type)
    {
        $ruleFields = $this->discountFacade->getQueryStringFieldsByType($type);
        $valueOptions = $this->discountFacade->getQueryStringValueOptions($type);
        $valueSuggestUrls = $this->metaProviderFactory->createMetaProviderByType($type)->getQueryStringValueSuggestUrls();

        $transformed = [];
        foreach ($ruleFields as $ruleField) {
            $fieldTransformed = [];
            $fieldTransformed['id'] = $ruleField;
            $fieldTransformed['label'] = $ruleField;
            $fieldTransformed['type'] = 'string';
            $fieldTransformed['operators'] = $this->transformComparators($type, $ruleField);

            if (isset($valueSuggestUrls[$ruleField])) {
                $fieldTransformed['input'] = 'select';
                $fieldTransformed['plugin'] = 'select2';
                $fieldTransformed['suggest_url'] = $valueSuggestUrls[$ruleField];
                $transformed[] = $fieldTransformed;

                continue;
            }

            if (!empty($valueOptions[$ruleField])) {
                $fieldTransformed['input'] = 'select';
                $fieldTransformed['plugin'] = 'select2';
                $fieldTransformed['values'] = $valueOptions[$ruleField];
            }

            $transformed[] = $fieldTransformed;
        }

        return $transformed;
    }

    /**
     * @param string $type
     * @param string $ruleField
     *
     * @return array<string>
     */
    protected function transformComparators($type, $ruleField)
    {
        $comparators = $this->discountFacade
            ->getQueryStringFieldExpressionsForField(
                $type,
                $ruleField,
            );

        foreach ($comparators as $key => $comparator) {
            $comparators[$key] = preg_replace('/\s/i', '_', $comparator);

            if (isset($this->queryOperatorMapping[$comparator])) {
                $comparators[$key] = $this->queryOperatorMapping[$comparator];
            }
        }

        return $comparators;
    }
}
