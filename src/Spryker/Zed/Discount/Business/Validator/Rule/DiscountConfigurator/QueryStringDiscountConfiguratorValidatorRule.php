<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator;

use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\ErrorCollectionTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Spryker\Shared\Discount\DiscountConfig;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaProviderFactory;
use Spryker\Zed\Discount\Business\QueryString\ValidatorInterface;

class QueryStringDiscountConfiguratorValidatorRule implements DiscountConfiguratorValidatorRuleInterface
{
    public const string ENTITY_IDENTIFIER_COLLECTOR_QUERY_STRING = 'discountCalculator.collectorQueryString';

    public const string ENTITY_IDENTIFIER_DECISION_RULE_QUERY_STRING = 'discountCondition.decisionRuleQueryString';

    protected const string TRANSLATION_PARAMETER_MESSAGE = '%message%';

    protected const string MESSAGE_COLLECTOR_QUERY_STRING_REQUIRED = 'A collector rule is required for the query string collector strategy.';

    public function __construct(protected ValidatorInterface $queryStringValidator)
    {
    }

    public function validate(DiscountConfiguratorTransfer $discountConfiguratorTransfer): ErrorCollectionTransfer
    {
        $errorCollectionTransfer = new ErrorCollectionTransfer();
        $discountCalculatorTransfer = $discountConfiguratorTransfer->getDiscountCalculator();
        $collectorQueryString = trim((string)$discountCalculatorTransfer?->getCollectorQueryString());

        if ($discountCalculatorTransfer?->getCollectorStrategyType() === DiscountConstants::DISCOUNT_COLLECTOR_STRATEGY_QUERY_STRING) {
            if ($collectorQueryString === '') {
                $errorCollectionTransfer->addError(
                    (new ErrorTransfer())
                        ->setMessage(DiscountConfig::TRANSLATION_KEY_QUERY_STRING_INVALID)
                        ->setEntityIdentifier(static::ENTITY_IDENTIFIER_COLLECTOR_QUERY_STRING)
                        ->setParameters([
                            static::TRANSLATION_PARAMETER_MESSAGE => static::MESSAGE_COLLECTOR_QUERY_STRING_REQUIRED,
                        ]),
                );
            }

            $this->addQueryStringErrors(
                $errorCollectionTransfer,
                MetaProviderFactory::TYPE_COLLECTOR,
                $collectorQueryString,
                static::ENTITY_IDENTIFIER_COLLECTOR_QUERY_STRING,
            );
        }

        return $this->addQueryStringErrors(
            $errorCollectionTransfer,
            MetaProviderFactory::TYPE_DECISION_RULE,
            trim((string)$discountConfiguratorTransfer->getDiscountCondition()?->getDecisionRuleQueryString()),
            static::ENTITY_IDENTIFIER_DECISION_RULE_QUERY_STRING,
        );
    }

    protected function addQueryStringErrors(
        ErrorCollectionTransfer $errorCollectionTransfer,
        string $type,
        string $queryString,
        string $entityIdentifier
    ): ErrorCollectionTransfer {
        if ($queryString === '') {
            return $errorCollectionTransfer;
        }

        foreach ($this->queryStringValidator->validateByType($type, $queryString) as $message) {
            $errorCollectionTransfer->addError(
                (new ErrorTransfer())
                    ->setMessage(DiscountConfig::TRANSLATION_KEY_QUERY_STRING_INVALID)
                    ->setEntityIdentifier($entityIdentifier)
                    ->setParameters([static::TRANSLATION_PARAMETER_MESSAGE => $message]),
            );
        }

        return $errorCollectionTransfer;
    }
}
