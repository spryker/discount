<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\QueryString;

use Generated\Shared\Transfer\ClauseTransfer;
use Spryker\Zed\Discount\Business\Exception\QueryStringException;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaDataProviderInterface;
use Spryker\Zed\Discount\Business\QueryString\Specification\SpecificationProviderInterface;

class SpecificationBuilder implements SpecificationBuilderInterface
{

    const OPEN_PARENTHESIS = '(';
    const CLOSE_PARENTHESIS = ')';

    /**
     * @var \Spryker\Zed\Discount\Business\QueryString\TokenizerInterface
     */
    protected $tokenizer;

    /**
     * @var \Spryker\Zed\Discount\Business\QueryString\Specification\SpecificationProviderInterface
     */
    protected $specificationProvider;

    /**
     * @var \Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface
     */
    protected $comparatorOperators;

    /**
     * @var array|string[]
     */
    protected $compoundComparatorExpressions = [];

    /**
     * @var \Spryker\Zed\Discount\Business\QueryString\ClauseValidatorInterface
     */
    protected $clauseValidator;

    /**
     * @var \Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaDataProviderInterface
     */
    protected $metaDataProvider;

    /**
     * @var array|string[]
     */
    protected $availableFields;

    /**
     * @param \Spryker\Zed\Discount\Business\QueryString\TokenizerInterface $tokenizer
     * @param \Spryker\Zed\Discount\Business\QueryString\Specification\SpecificationProviderInterface $specificationProvider
     * @param \Spryker\Zed\Discount\Business\QueryString\ComparatorOperatorsInterface $comparatorOperators
     * @param \Spryker\Zed\Discount\Business\QueryString\ClauseValidatorInterface $clauseValidator
     * @param \Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaDataProviderInterface $metaDataProvider
     */
    public function __construct(
        TokenizerInterface $tokenizer,
        SpecificationProviderInterface $specificationProvider,
        ComparatorOperatorsInterface $comparatorOperators,
        ClauseValidatorInterface $clauseValidator,
        MetaDataProviderInterface $metaDataProvider
    ) {
        $this->tokenizer = $tokenizer;
        $this->specificationProvider = $specificationProvider;
        $this->comparatorOperators = $comparatorOperators;
        $this->clauseValidator = $clauseValidator;
        $this->metaDataProvider = $metaDataProvider;
    }

    /**
     * @param string $queryString
     *
     * @return \Spryker\Zed\Discount\Business\QueryString\Specification\CollectorSpecification\CollectorSpecificationInterface|\Spryker\Zed\Discount\Business\QueryString\Specification\DecisionRuleSpecification\DecisionRuleSpecificationInterface
     */
    public function buildFromQueryString($queryString)
    {
        $tokens = $this->tokenizer->tokenizeQueryString($queryString);

        return $this->buildTree($tokens);
    }

    /**
     * @param array|string[] $tokens
     * @param int $currentTokenIndex
     *
     * @throws \Spryker\Zed\Discount\Business\Exception\QueryStringException
     *
     * @return \Spryker\Zed\Discount\Business\QueryString\Specification\CollectorSpecification\CollectorSpecificationInterface|\Spryker\Zed\Discount\Business\QueryString\Specification\DecisionRuleSpecification\DecisionRuleSpecificationInterface
     */
    protected function buildTree(array $tokens, &$currentTokenIndex = 0)
    {
        static $parenthesisDepth = 0;

        $leftNode = null;
        $compositeNode = null;
        $lastLogicalComparator = null;
        $clauseTransfer = new ClauseTransfer();

        $countTokens = count($tokens);

        while ($countTokens > $currentTokenIndex) {

            $token = $this->cleanToken($tokens[$currentTokenIndex]);

            switch (true) {
                case self::OPEN_PARENTHESIS === $token:
                    $parenthesisDepth++;
                    $currentTokenIndex++;
                    $childTree = $this->buildTree($tokens, $currentTokenIndex);

                    if ($leftNode === null) {
                        $leftNode = $childTree;
                    } else {
                        $compositeNode = $this->createCompositeNode($lastLogicalComparator, $leftNode, $childTree);
                    }
                    break;

                case self::CLOSE_PARENTHESIS === $token:
                    $parenthesisDepth--;

                    if ($compositeNode == null) {
                        return $leftNode;
                    }

                    return $compositeNode;

                case $this->isLogicalComparator($token):
                    $lastLogicalComparator = $token;
                    break;

                case $this->isField($token):
                    $clauseTransfer = new ClauseTransfer();
                    $this->setClauseField($token, $clauseTransfer);
                    break;

                case $this->isComparator($token):
                    if ($clauseTransfer->getOperator()) {
                        $token = $clauseTransfer->getOperator() . ' ' . $token;
                    }

                    $clauseTransfer->setOperator($token);
                    break;

                case $this->isValue($token):
                     $value = $this->clearQuotes($token);
                     $clauseTransfer->setValue($value);

                     $this->clauseValidator->validateClause($clauseTransfer);

                    if ($leftNode === null) {
                        $leftNode = $this->specificationProvider->getSpecificationContext($clauseTransfer);
                    } else {
                        $rightNode = $this->specificationProvider->getSpecificationContext($clauseTransfer);
                        $compositeNode = $this->createCompositeNode($lastLogicalComparator, $leftNode, $rightNode);
                    }

                    break;
                default:
                    throw new QueryStringException(
                        sprintf(
                            "Token '%s' could not be identified by specification builder.",
                            $token
                        )
                    );
            }

            $currentTokenIndex++;
        }

        if ($parenthesisDepth !== 0) {
            throw new QueryStringException('Parenthesis not matching.');
        }

        if ($compositeNode == null) {
            return $leftNode;
        }

        return $compositeNode;
    }

    /**
     * @return array|string[]
     */
    protected function getCompoundComparatorExpressions()
    {
        if (!$this->compoundComparatorExpressions) {
            $this->compoundComparatorExpressions = $this->comparatorOperators->getCompoundComparatorExpressions();
        }

        return $this->compoundComparatorExpressions;
    }

    /**
     * @param string $logicalComparator
     * @param \Spryker\Zed\Discount\Business\QueryString\Specification\CollectorSpecification\CollectorSpecificationInterface|\Spryker\Zed\Discount\Business\QueryString\Specification\DecisionRuleSpecification\DecisionRuleSpecificationInterface $leftNode
     * @param \Spryker\Zed\Discount\Business\QueryString\Specification\CollectorSpecification\CollectorSpecificationInterface|\Spryker\Zed\Discount\Business\QueryString\Specification\DecisionRuleSpecification\DecisionRuleSpecificationInterface $rightNode
     *
     * @return \Spryker\Zed\Discount\Business\QueryString\Specification\CollectorSpecification\CollectorSpecificationInterface|\Spryker\Zed\Discount\Business\QueryString\Specification\DecisionRuleSpecification\DecisionRuleSpecificationInterface
     */
    protected function createCompositeNode(
        $logicalComparator,
        $leftNode,
        $rightNode
    ) {

        $compositeNode = $leftNode;
        if ($logicalComparator === LogicalComparators::COMPARATOR_AND) {
            $compositeNode = $this->specificationProvider->createAnd($leftNode, $rightNode);
        } elseif ($logicalComparator === LogicalComparators::COMPARATOR_OR) {
            $compositeNode = $this->specificationProvider->createOr($leftNode, $rightNode);
        }

        return $compositeNode;
    }

    /**
     * @param string $token
     *
     * @return string
     */
    protected function cleanToken($token)
    {
        return strtolower($token);
    }

    /**
     * @param string $fieldName
     * @param \Generated\Shared\Transfer\ClauseTransfer $clauseTransfer
     *
     * @return void
     */
    protected function setClauseField($fieldName, ClauseTransfer $clauseTransfer)
    {
        if (strpos($fieldName, '.') !== false) {
            list($fieldName, $attribute) = explode('.', $fieldName);
            $clauseTransfer->setAttribute($attribute);
        }

        $clauseTransfer->setField(trim($fieldName));
    }

    /**
     * @param string $value
     *
     * @return string
     */
    protected function clearQuotes($value)
    {
        return preg_replace('/["\']/', '', $value);
    }

    /**
     * @param string $token
     *
     * @return bool
     */
    protected function isField($token)
    {
        $availableFields = $this->metaDataProvider->getAvailableFields();
        foreach ($availableFields as $field) {
            if ($field === $token) {
                return true;
            }
        }

        return false;

    }

    /**
     * @param string $token
     *
     * @return bool
     */
    protected function isLogicalComparator($token)
    {
        return in_array($token, [LogicalComparators::COMPARATOR_AND, LogicalComparators::COMPARATOR_OR], true);

    }

    /**
     * @param string $token
     *
     * @return bool
     */
    protected function isComparator($token)
    {
        if (in_array($token, $this->getCompoundComparatorExpressions(), true)) {
            return true;
        }

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator($token);
        return $this->comparatorOperators->isValidComparator($clauseTransfer);
    }


    /**
     * @param string $token
     *
     * @return bool
     */
    protected function isValue($token)
    {
         $first = substr($token, 0, 1);
         $last = substr($token, -1);

        if ($first === '"' && $last === '"') {
            return true;
        }

        if ($first === "'" && $last === "'") {
            return true;
        }

         return false;
    }

}
