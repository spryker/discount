<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\Discount\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\DiscountRuleGroupTransfer;
use Generated\Shared\Transfer\DiscountRuleTransfer;
use Spryker\Shared\Discount\DiscountConfig;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;
use Spryker\Zed\Discount\Business\QueryString\Converter\DiscountQueryStringBuilder;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaProviderFactory;
use Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\QueryStringDiscountConfiguratorValidatorRule;
use SprykerTest\Zed\Discount\DiscountBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group Facade
 * @group BuildQueryStringFromDiscountRuleGroupFacadeTest
 * Add your own group annotations below this line
 */
class BuildQueryStringFromDiscountRuleGroupFacadeTest extends Unit
{
    protected const string FIELD_SUB_TOTAL = 'sub-total';

    protected const string FIELD_SKU = 'sku';

    protected const string FIELD_ITEM_QUANTITY = 'item-quantity';

    protected const string UNKNOWN_TYPE = 'unknown-type';

    protected const string FIELD_ATTRIBUTE = 'attribute';

    /**
     * @uses \Spryker\Zed\Discount\Business\QueryString\Converter\DiscountQueryStringBuilder::TRANSLATION_PARAMETER_MESSAGE
     */
    protected const string TRANSLATION_PARAMETER_MESSAGE = '%message%';

    protected DiscountBusinessTester $tester;

    /**
     * @dataProvider provideConvertibleRuleGroups
     */
    public function testBuildsTheQueryStringFromTheRuleGroup(
        string $type,
        DiscountRuleGroupTransfer $discountRuleGroupTransfer,
        string $expectedQueryString
    ): void {
        // Act
        $discountQueryStringResponseTransfer = $this->createDiscountFacade()
            ->buildQueryStringFromDiscountRuleGroup($type, $discountRuleGroupTransfer);

        // Assert
        $this->assertTrue($discountQueryStringResponseTransfer->getIsSuccessful());
        $this->assertSame($expectedQueryString, $discountQueryStringResponseTransfer->getQueryString());
        $this->assertCount(0, $discountQueryStringResponseTransfer->getErrors());
    }

    /**
     * @return array<string, array{string, \Generated\Shared\Transfer\DiscountRuleGroupTransfer, string}>
     */
    public function provideConvertibleRuleGroups(): array
    {
        return [
            'given an empty root group, when it is converted, then the query string is empty' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                new DiscountRuleGroupTransfer(),
                '',
            ],
            'given a single rule, when it is converted, then the field operator and quoted value are rendered' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                static::buildGroup([static::buildRule(static::FIELD_SUB_TOTAL, '>=', '500')]),
                "sub-total >= '500'",
            ],
            'given a compound operator, when it is converted, then it is rendered verbatim' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                static::buildGroup([static::buildRule(static::FIELD_SUB_TOTAL, 'is in', '100;200;300')]),
                "sub-total is in '100;200;300'",
            ],
            'given the match all value on a collector rule, when it is converted, then the asterisk is quoted' => [
                MetaProviderFactory::TYPE_COLLECTOR,
                static::buildGroup([static::buildRule(static::FIELD_SKU, '=', '*')]),
                "sku = '*'",
            ],
            'given two rules in an or group, when it is converted, then they are joined by the condition' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                static::buildGroup(
                    [
                        static::buildRule(static::FIELD_SUB_TOTAL, '>=', '500'),
                        static::buildRule(static::FIELD_ITEM_QUANTITY, '>', '2'),
                    ],
                    [],
                    'or',
                ),
                "sub-total >= '500' or item-quantity > '2'",
            ],
            'given no condition on a group of two rules, when it is converted, then the and operator is the default' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                static::buildGroup(
                    [
                        static::buildRule(static::FIELD_SUB_TOTAL, '>=', '500'),
                        static::buildRule(static::FIELD_ITEM_QUANTITY, '>', '2'),
                    ],
                    [],
                    null,
                ),
                "sub-total >= '500' and item-quantity > '2'",
            ],
            'given three nesting levels, when it is converted, then every nested group is parenthesised' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                static::buildGroup(
                    [static::buildRule(static::FIELD_SUB_TOTAL, '>=', '500')],
                    [
                        static::buildGroup(
                            [static::buildRule(static::FIELD_ITEM_QUANTITY, '>', '2')],
                            [static::buildGroup([static::buildRule(static::FIELD_SUB_TOTAL, '<', '900')])],
                            'or',
                        ),
                    ],
                ),
                "sub-total >= '500' and (item-quantity > '2' or (sub-total < '900'))",
            ],
            'given four nesting levels, when it is converted, then no depth limit applies' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                static::buildGroup([], [
                    static::buildGroup([], [
                        static::buildGroup([], [
                            static::buildGroup([static::buildRule(static::FIELD_SUB_TOTAL, '>=', '500')]),
                        ]),
                    ]),
                ]),
                "(((sub-total >= '500')))",
            ],
        ];
    }

    /**
     * @dataProvider provideRejectedRuleGroups
     */
    public function testRejectsARuleGroupItCannotConvert(
        string $type,
        DiscountRuleGroupTransfer $discountRuleGroupTransfer,
        string $expectedGlossaryKey,
        string $expectedEntityIdentifier
    ): void {
        // Act
        $discountQueryStringResponseTransfer = $this->createDiscountFacade()
            ->buildQueryStringFromDiscountRuleGroup($type, $discountRuleGroupTransfer);

        // Assert
        $this->assertFalse($discountQueryStringResponseTransfer->getIsSuccessful());
        $this->assertNull($discountQueryStringResponseTransfer->getQueryString());

        $errorTransfer = $discountQueryStringResponseTransfer->getErrors()->offsetGet(0);
        $this->assertSame($expectedGlossaryKey, $errorTransfer->getMessage());
        $this->assertSame($expectedEntityIdentifier, $errorTransfer->getEntityIdentifier());
    }

    public function testNamesTheQuoteLimitationInsteadOfTheParserError(): void
    {
        // Arrange
        $discountRuleGroupTransfer = static::buildGroup([static::buildRule(static::FIELD_SKU, '=', "Levi's")]);

        // Act
        $discountQueryStringResponseTransfer = $this->createDiscountFacade()
            ->buildQueryStringFromDiscountRuleGroup(MetaProviderFactory::TYPE_COLLECTOR, $discountRuleGroupTransfer);

        // Assert
        $this->assertFalse($discountQueryStringResponseTransfer->getIsSuccessful());
        $this->assertSame(
            DiscountQueryStringBuilder::ERROR_RULE_CONTAINS_QUOTES,
            $discountQueryStringResponseTransfer->getErrors()->offsetGet(0)->getParameters()[static::TRANSLATION_PARAMETER_MESSAGE],
        );
    }

    /**
     * @return array<string, array{string, \Generated\Shared\Transfer\DiscountRuleGroupTransfer, string, string}>
     */
    public function provideRejectedRuleGroups(): array
    {
        return [
            'given an empty nested group, when it is converted, then the structural check rejects it' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                static::buildGroup(
                    [static::buildRule(static::FIELD_SUB_TOTAL, '>=', '500')],
                    [new DiscountRuleGroupTransfer()],
                ),
                DiscountConfig::TRANSLATION_KEY_QUERY_STRING_INVALID,
                QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_DECISION_RULE_QUERY_STRING,
            ],
            'given an unsupported group condition, when it is converted, then the structural check rejects it' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                static::buildGroup([static::buildRule(static::FIELD_SUB_TOTAL, '>=', '500')], [], 'xor'),
                DiscountConfig::TRANSLATION_KEY_QUERY_STRING_INVALID,
                QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_DECISION_RULE_QUERY_STRING,
            ],
            'given a rule without an operator, when it is converted, then the structural check rejects it' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                static::buildGroup([static::buildRule(static::FIELD_SUB_TOTAL, '', '500')]),
                DiscountConfig::TRANSLATION_KEY_QUERY_STRING_INVALID,
                QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_DECISION_RULE_QUERY_STRING,
            ],
            'given a value with a single quote, when it is converted, then the structural check rejects it before the parser sees it' => [
                MetaProviderFactory::TYPE_COLLECTOR,
                static::buildGroup([static::buildRule(static::FIELD_SKU, '=', "Levi's")]),
                DiscountConfig::TRANSLATION_KEY_QUERY_STRING_INVALID,
                QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_COLLECTOR_QUERY_STRING,
            ],
            'given a value that tries to smuggle in a second rule, when it is converted, then the structural check rejects it' => [
                MetaProviderFactory::TYPE_COLLECTOR,
                static::buildGroup([static::buildRule(static::FIELD_SKU, '=', "x' or sku = '*")]),
                DiscountConfig::TRANSLATION_KEY_QUERY_STRING_INVALID,
                QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_COLLECTOR_QUERY_STRING,
            ],
            'given an attribute with a double quote, when it is converted, then the structural check rejects it' => [
                MetaProviderFactory::TYPE_COLLECTOR,
                static::buildGroup([static::buildRule(static::FIELD_ATTRIBUTE, '=', 'Levis')->setAttribute('bra"nd')]),
                DiscountConfig::TRANSLATION_KEY_QUERY_STRING_INVALID,
                QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_COLLECTOR_QUERY_STRING,
            ],
            'given an unknown field on a collector rule, when it is converted, then the query string validator rejects it' => [
                MetaProviderFactory::TYPE_COLLECTOR,
                static::buildGroup([static::buildRule('unknown-field', '=', '1')]),
                DiscountConfig::TRANSLATION_KEY_QUERY_STRING_INVALID,
                QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_COLLECTOR_QUERY_STRING,
            ],
            'given an unknown query string type, when it is converted, then the type is reported' => [
                static::UNKNOWN_TYPE,
                static::buildGroup([static::buildRule(static::FIELD_SUB_TOTAL, '>=', '500')]),
                DiscountConfig::TRANSLATION_KEY_QUERY_STRING_INVALID,
                QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_DECISION_RULE_QUERY_STRING,
            ],
        ];
    }

    /**
     * @dataProvider provideRoundTrippedQueryStrings
     */
    public function testRoundTripsAQueryStringThroughBothConverters(string $type, string $queryString): void
    {
        // Arrange
        $discountFacade = $this->createDiscountFacade();
        $discountRuleGroupResponseTransfer = $discountFacade->buildDiscountRuleGroupFromQueryString($type, $queryString);

        // Act
        $discountQueryStringResponseTransfer = $discountFacade->buildQueryStringFromDiscountRuleGroup(
            $type,
            $discountRuleGroupResponseTransfer->getDiscountRuleGroupOrFail(),
        );

        // Assert
        $this->assertTrue($discountRuleGroupResponseTransfer->getIsSuccessful());
        $this->assertTrue($discountQueryStringResponseTransfer->getIsSuccessful());
        $this->assertSame($queryString, $discountQueryStringResponseTransfer->getQueryString());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function provideRoundTrippedQueryStrings(): array
    {
        return [
            'given an equality rule, when it is parsed and rendered, then the string is unchanged' => [
                MetaProviderFactory::TYPE_DECISION_RULE, "sub-total = '500'",
            ],
            'given an inequality rule, when it is parsed and rendered, then the string is unchanged' => [
                MetaProviderFactory::TYPE_DECISION_RULE, "sub-total != '500'",
            ],
            'given the comparison operators, when they are parsed and rendered, then the string is unchanged' => [
                MetaProviderFactory::TYPE_DECISION_RULE, "sub-total >= '500' and sub-total <= '900'",
            ],
            'given the strict comparison operators, when they are parsed and rendered, then the string is unchanged' => [
                MetaProviderFactory::TYPE_DECISION_RULE, "sub-total > '500' and sub-total < '900'",
            ],
            'given the list operators, when they are parsed and rendered, then the string is unchanged' => [
                MetaProviderFactory::TYPE_DECISION_RULE, "sub-total is in '100;200' or sub-total is not in '300;400'",
            ],
            'given the containment operators, when they are parsed and rendered, then the string is unchanged' => [
                MetaProviderFactory::TYPE_COLLECTOR, "sku contains 'DE' and sku does not contain 'AT'",
            ],
            'given a nested group, when it is parsed and rendered, then the parentheses survive' => [
                MetaProviderFactory::TYPE_DECISION_RULE, "sub-total >= '500' and (item-quantity > '2' or item-quantity < '9')",
            ],
        ];
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\DiscountRuleTransfer> $discountRuleTransfers
     * @param array<int, \Generated\Shared\Transfer\DiscountRuleGroupTransfer> $nestedDiscountRuleGroupTransfers
     */
    protected static function buildGroup(
        array $discountRuleTransfers = [],
        array $nestedDiscountRuleGroupTransfers = [],
        ?string $condition = 'and'
    ): DiscountRuleGroupTransfer {
        $discountRuleGroupTransfer = (new DiscountRuleGroupTransfer())->setCondition($condition);

        foreach ($discountRuleTransfers as $discountRuleTransfer) {
            $discountRuleGroupTransfer->addRule($discountRuleTransfer);
        }

        foreach ($nestedDiscountRuleGroupTransfers as $nestedDiscountRuleGroupTransfer) {
            $discountRuleGroupTransfer->addGroup($nestedDiscountRuleGroupTransfer);
        }

        return $discountRuleGroupTransfer;
    }

    protected static function buildRule(string $field, string $operator, string $value): DiscountRuleTransfer
    {
        return (new DiscountRuleTransfer())
            ->setField($field)
            ->setOperator($operator)
            ->setValue($value);
    }

    protected function createDiscountFacade(): DiscountFacadeInterface
    {
        return $this->tester->getLocator()->discount()->facade();
    }
}
