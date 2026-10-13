<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Zed\Discount\Business\QueryString\Converter;

use Generated\Shared\Transfer\ClauseTransfer;
use Generated\Shared\Transfer\DiscountRuleGroupResponseTransfer;
use Generated\Shared\Transfer\DiscountRuleGroupTransfer;
use Generated\Shared\Transfer\DiscountRuleTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Spryker\Zed\Discount\Business\Exception\QueryStringException;
use Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface;
use Spryker\Zed\Discount\Business\QueryString\LogicalComparators;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaDataProviderInterface;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaProviderFactory;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaProviderFactoryInterface;
use Spryker\Zed\Discount\Business\QueryString\SpecificationBuilder;
use Spryker\Zed\Discount\Business\QueryString\TokenizerInterface;

class DiscountRuleGroupBuilder implements DiscountRuleGroupBuilderInterface
{
    /**
     * @var array<int, string>
     */
    public const array SUPPORTED_TYPES = [MetaProviderFactory::TYPE_COLLECTOR, MetaProviderFactory::TYPE_DECISION_RULE];

    public const string ERROR_TYPE_NOT_SUPPORTED = 'Query string type "%s" is not supported.';

    /**
     * @var array<int, string>
     */
    protected const array LOGICAL_OPERATORS = [LogicalComparators::COMPARATOR_AND, LogicalComparators::COMPARATOR_OR];

    /**
     * @var non-empty-string
     */
    protected const string ATTRIBUTE_SEPARATOR = '.';

    protected const string ERROR_TOKEN_NOT_IDENTIFIED = 'Token "%s" could not be identified.';

    protected const string ERROR_PARENTHESIS_NOT_MATCHING = 'Parenthesis not matching.';

    protected const string ERROR_OPERATOR_WITHOUT_FIELD = 'Operator "%s" is not preceded by a field.';

    protected const string ERROR_VALUE_WITHOUT_OPERATOR = 'Value "%s" is not preceded by a field and an operator.';

    protected const string ENTITY_IDENTIFIER_QUERY_STRING = 'queryString';

    public function __construct(
        protected TokenizerInterface $tokenizer,
        protected MetaProviderFactoryInterface $metaProviderFactory,
        protected ComparatorOperatorsInterface $comparatorOperators
    ) {
    }

    public function buildDiscountRuleGroupFromQueryString(string $type, string $queryString): DiscountRuleGroupResponseTransfer
    {
        $discountRuleGroupResponseTransfer = new DiscountRuleGroupResponseTransfer();

        if (!in_array(strtolower($type), static::SUPPORTED_TYPES, true)) {
            return $discountRuleGroupResponseTransfer
                ->setIsSuccessful(false)
                ->addError($this->createErrorTransfer(sprintf(static::ERROR_TYPE_NOT_SUPPORTED, $type)));
        }

        if (trim($queryString) === '') {
            return $discountRuleGroupResponseTransfer
                ->setIsSuccessful(true)
                ->setDiscountRuleGroup(new DiscountRuleGroupTransfer());
        }

        $metaDataProvider = $this->metaProviderFactory->createMetaProviderByType($type);
        $currentTokenIndex = 0;

        try {
            $tokens = $this->tokenizer->tokenizeQueryString($queryString);
            $discountRuleGroupTransfer = $this->buildDiscountRuleGroup($tokens, $currentTokenIndex, $metaDataProvider, false);
            $this->assertAllTokensConsumed($tokens, $currentTokenIndex);
        } catch (QueryStringException $queryStringException) {
            return $discountRuleGroupResponseTransfer
                ->setIsSuccessful(false)
                ->addError($this->createErrorTransfer($queryStringException->getMessage()));
        }

        return $discountRuleGroupResponseTransfer
            ->setIsSuccessful(true)
            ->setDiscountRuleGroup($discountRuleGroupTransfer);
    }

    protected function createErrorTransfer(string $message): ErrorTransfer
    {
        return (new ErrorTransfer())
            ->setMessage($message)
            ->setEntityIdentifier(static::ENTITY_IDENTIFIER_QUERY_STRING);
    }

    /**
     * @param array<int, string> $tokens
     *
     * @throws \Spryker\Zed\Discount\Business\Exception\QueryStringException
     */
    protected function buildDiscountRuleGroup(
        array $tokens,
        int &$currentTokenIndex,
        MetaDataProviderInterface $metaDataProvider,
        bool $isNested
    ): DiscountRuleGroupTransfer {
        $discountRuleGroupTransfer = new DiscountRuleGroupTransfer();
        $discountRuleTransfer = null;

        while (isset($tokens[$currentTokenIndex])) {
            $rawToken = $tokens[$currentTokenIndex];
            $token = strtolower($rawToken);
            $currentTokenIndex++;

            if ($token === SpecificationBuilder::OPEN_PARENTHESIS) {
                $discountRuleGroupTransfer->addGroup(
                    $this->buildDiscountRuleGroup($tokens, $currentTokenIndex, $metaDataProvider, true),
                );

                continue;
            }

            if ($token === SpecificationBuilder::CLOSE_PARENTHESIS) {
                return $discountRuleGroupTransfer;
            }

            if (in_array($token, static::LOGICAL_OPERATORS, true)) {
                $discountRuleGroupTransfer = $this->applyGroupCondition($discountRuleGroupTransfer, $token);

                continue;
            }

            $discountRuleTransfer = $this->applyToken($discountRuleGroupTransfer, $discountRuleTransfer, $metaDataProvider, $rawToken, $token);
        }

        if ($isNested) {
            throw new QueryStringException(static::ERROR_PARENTHESIS_NOT_MATCHING);
        }

        return $discountRuleGroupTransfer;
    }

    /**
     * @throws \Spryker\Zed\Discount\Business\Exception\QueryStringException
     */
    protected function applyToken(
        DiscountRuleGroupTransfer $discountRuleGroupTransfer,
        ?DiscountRuleTransfer $discountRuleTransfer,
        MetaDataProviderInterface $metaDataProvider,
        string $rawToken,
        string $token
    ): ?DiscountRuleTransfer {
        if ($metaDataProvider->isFieldAvailable($token)) {
            $discountRuleTransfer = $this->createDiscountRule($token);
            $discountRuleGroupTransfer->addRule($discountRuleTransfer);

            return $discountRuleTransfer;
        }

        if ($this->isComparator($token)) {
            $this->applyRuleOperator($discountRuleTransfer, $token);

            return $discountRuleTransfer;
        }

        if ($this->isValue($rawToken)) {
            $this->applyRuleValue($discountRuleTransfer, $rawToken);

            return $discountRuleTransfer;
        }

        throw new QueryStringException(sprintf(static::ERROR_TOKEN_NOT_IDENTIFIED, $rawToken));
    }

    /**
     * @param array<int, string> $tokens
     *
     * @throws \Spryker\Zed\Discount\Business\Exception\QueryStringException
     */
    protected function assertAllTokensConsumed(array $tokens, int $currentTokenIndex): void
    {
        if ($currentTokenIndex < count($tokens)) {
            throw new QueryStringException(static::ERROR_PARENTHESIS_NOT_MATCHING);
        }
    }

    protected function applyGroupCondition(
        DiscountRuleGroupTransfer $discountRuleGroupTransfer,
        string $logicalOperator
    ): DiscountRuleGroupTransfer {
        $condition = $discountRuleGroupTransfer->getCondition();

        if ($condition === null) {
            return $discountRuleGroupTransfer->setCondition($logicalOperator);
        }

        if ($condition === $logicalOperator) {
            return $discountRuleGroupTransfer;
        }

        return (new DiscountRuleGroupTransfer())
            ->setCondition($logicalOperator)
            ->addGroup($discountRuleGroupTransfer);
    }

    protected function createDiscountRule(string $token): DiscountRuleTransfer
    {
        $discountRuleTransfer = new DiscountRuleTransfer();

        if (!str_contains($token, static::ATTRIBUTE_SEPARATOR)) {
            return $discountRuleTransfer->setField($token);
        }

        [$field, $attribute] = explode(static::ATTRIBUTE_SEPARATOR, $token, 2);

        return $discountRuleTransfer->setField($field)->setAttribute($attribute);
    }

    /**
     * @throws \Spryker\Zed\Discount\Business\Exception\QueryStringException
     */
    protected function applyRuleOperator(?DiscountRuleTransfer $discountRuleTransfer, string $token): void
    {
        if ($discountRuleTransfer === null || $discountRuleTransfer->getValue() !== null) {
            throw new QueryStringException(sprintf(static::ERROR_OPERATOR_WITHOUT_FIELD, $token));
        }

        $operator = $discountRuleTransfer->getOperator();

        $discountRuleTransfer->setOperator($operator === null ? $token : sprintf('%s %s', $operator, $token));
    }

    /**
     * @throws \Spryker\Zed\Discount\Business\Exception\QueryStringException
     */
    protected function applyRuleValue(?DiscountRuleTransfer $discountRuleTransfer, string $rawToken): void
    {
        if ($discountRuleTransfer === null || $discountRuleTransfer->getOperator() === null) {
            throw new QueryStringException(sprintf(static::ERROR_VALUE_WITHOUT_OPERATOR, $rawToken));
        }

        $discountRuleTransfer->setValue($this->clearQuotes($rawToken));
    }

    protected function isComparator(string $token): bool
    {
        if (in_array($token, $this->comparatorOperators->getCompoundComparatorExpressions(), true)) {
            return true;
        }

        return $this->comparatorOperators->isExistingComparator((new ClauseTransfer())->setOperator($token));
    }

    protected function isValue(string $rawToken): bool
    {
        return (bool)preg_match('/^(".*"|\'.*\')$/s', $rawToken);
    }

    protected function clearQuotes(string $rawToken): string
    {
        return substr($rawToken, 1, -1);
    }
}
