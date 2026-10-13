<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Business\QueryString\Converter;

use Generated\Shared\Transfer\DiscountRuleGroupResponseTransfer;
use Generated\Shared\Transfer\DiscountRuleGroupTransfer;
use Generated\Shared\Transfer\DiscountRuleTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Spryker\Shared\Discount\DiscountConfig;
use Spryker\Zed\Discount\Business\Exception\QueryStringException;
use Spryker\Zed\Discount\Business\QueryString\LogicalComparators;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaProviderFactory;
use Spryker\Zed\Discount\Business\QueryString\SpecificationBuilder;
use Spryker\Zed\Discount\Business\QueryString\ValidatorInterface;
use Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\QueryStringDiscountConfiguratorValidatorRule;

class DiscountQueryStringBuilder implements DiscountQueryStringBuilderInterface
{
    /**
     * @var array<int, string>
     */
    protected const array LOGICAL_OPERATORS = [LogicalComparators::COMPARATOR_AND, LogicalComparators::COMPARATOR_OR];

    protected const string TRANSLATION_PARAMETER_MESSAGE = '%message%';

    protected const string ERROR_EMPTY_GROUP = 'A nested rule group must contain at least one rule or nested group.';

    protected const string ERROR_INVALID_CONDITION = 'Rule group condition "%s" is not supported.';

    protected const string ERROR_INCOMPLETE_RULE = 'A rule requires a field, an operator and a value.';

    public const string ERROR_RULE_CONTAINS_QUOTES = 'A rule field, attribute or value must not contain quotes (\' or ").';

    protected const string QUOTE_CHARACTERS_PATTERN = '/[\'"]/';

    protected const string RULE_FORMAT = '%s %s \'%s\'';

    protected const string ATTRIBUTE_SEPARATOR = '.';

    public function __construct(protected ValidatorInterface $queryStringValidator)
    {
    }

    public function buildQueryStringFromDiscountRuleGroup(
        string $type,
        DiscountRuleGroupTransfer $discountRuleGroupTransfer
    ): DiscountRuleGroupResponseTransfer {
        $discountRuleGroupResponseTransfer = new DiscountRuleGroupResponseTransfer();
        $entityIdentifier = $this->getEntityIdentifierByType($type);

        if (!in_array(strtolower($type), DiscountRuleGroupBuilder::SUPPORTED_TYPES, true)) {
            return $discountRuleGroupResponseTransfer
                ->setIsSuccessful(false)
                ->addError($this->createQueryStringErrorTransfer(
                    sprintf(DiscountRuleGroupBuilder::ERROR_TYPE_NOT_SUPPORTED, $type),
                    $entityIdentifier,
                ));
        }

        try {
            $queryString = $this->buildGroup($discountRuleGroupTransfer);
        } catch (QueryStringException $queryStringException) {
            return $discountRuleGroupResponseTransfer
                ->setIsSuccessful(false)
                ->addError($this->createQueryStringErrorTransfer($queryStringException->getMessage(), $entityIdentifier));
        }

        if ($queryString === '') {
            return $discountRuleGroupResponseTransfer
                ->setIsSuccessful(true)
                ->setQueryString('');
        }

        $validationMessages = $this->queryStringValidator->validateByType($type, $queryString);

        if ($validationMessages === []) {
            return $discountRuleGroupResponseTransfer
                ->setIsSuccessful(true)
                ->setQueryString($queryString);
        }

        foreach ($validationMessages as $validationMessage) {
            $discountRuleGroupResponseTransfer->addError(
                $this->createQueryStringErrorTransfer($validationMessage, $entityIdentifier),
            );
        }

        return $discountRuleGroupResponseTransfer->setIsSuccessful(false);
    }

    /**
     * @throws \Spryker\Zed\Discount\Business\Exception\QueryStringException
     */
    protected function buildGroup(DiscountRuleGroupTransfer $discountRuleGroupTransfer): string
    {
        $expressions = [];

        foreach ($discountRuleGroupTransfer->getRules() as $discountRuleTransfer) {
            $expressions[] = $this->buildRule($discountRuleTransfer);
        }

        foreach ($discountRuleGroupTransfer->getGroups() as $nestedDiscountRuleGroupTransfer) {
            $nestedQueryString = $this->buildGroup($nestedDiscountRuleGroupTransfer);

            if ($nestedQueryString === '') {
                throw new QueryStringException(static::ERROR_EMPTY_GROUP);
            }

            $expressions[] = sprintf(
                '%s%s%s',
                SpecificationBuilder::OPEN_PARENTHESIS,
                $nestedQueryString,
                SpecificationBuilder::CLOSE_PARENTHESIS,
            );
        }

        if ($expressions === []) {
            return '';
        }

        return implode(sprintf(' %s ', $this->getCondition($discountRuleGroupTransfer)), $expressions);
    }

    /**
     * @throws \Spryker\Zed\Discount\Business\Exception\QueryStringException
     */
    protected function getCondition(DiscountRuleGroupTransfer $discountRuleGroupTransfer): string
    {
        $condition = $discountRuleGroupTransfer->getCondition();

        if ($condition === null) {
            return LogicalComparators::COMPARATOR_AND;
        }

        if (!in_array($condition, static::LOGICAL_OPERATORS, true)) {
            throw new QueryStringException(sprintf(static::ERROR_INVALID_CONDITION, $condition));
        }

        return $condition;
    }

    /**
     * @throws \Spryker\Zed\Discount\Business\Exception\QueryStringException
     */
    protected function buildRule(DiscountRuleTransfer $discountRuleTransfer): string
    {
        $field = $discountRuleTransfer->getField();
        $operator = $discountRuleTransfer->getOperator();
        $value = $discountRuleTransfer->getValue();

        if ($field === null || $field === '' || $operator === null || $operator === '' || $value === null) {
            throw new QueryStringException(static::ERROR_INCOMPLETE_RULE);
        }

        $attribute = $discountRuleTransfer->getAttribute();

        if (preg_match(static::QUOTE_CHARACTERS_PATTERN, $field . $attribute . $value) === 1) {
            throw new QueryStringException(static::ERROR_RULE_CONTAINS_QUOTES);
        }

        if ($attribute !== null && $attribute !== '') {
            $field .= static::ATTRIBUTE_SEPARATOR . $attribute;
        }

        return sprintf(static::RULE_FORMAT, $field, $operator, $value);
    }

    protected function createQueryStringErrorTransfer(string $message, string $entityIdentifier): ErrorTransfer
    {
        return (new ErrorTransfer())
            ->setMessage(DiscountConfig::TRANSLATION_KEY_QUERY_STRING_INVALID)
            ->setEntityIdentifier($entityIdentifier)
            ->setParameters([static::TRANSLATION_PARAMETER_MESSAGE => $message]);
    }

    protected function getEntityIdentifierByType(string $type): string
    {
        if ($type === MetaProviderFactory::TYPE_COLLECTOR) {
            return QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_COLLECTOR_QUERY_STRING;
        }

        return QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_DECISION_RULE_QUERY_STRING;
    }
}
