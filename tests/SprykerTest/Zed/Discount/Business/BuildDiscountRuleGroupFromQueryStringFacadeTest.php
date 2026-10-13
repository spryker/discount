<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\Discount\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\DiscountRuleGroupTransfer;
use Spryker\Zed\Discount\Business\DiscountBusinessFactory;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaProviderFactory;
use Spryker\Zed\Discount\DiscountDependencyProvider;
use Spryker\Zed\DiscountExtension\Dependency\Plugin\DecisionRulePluginInterface;
use Spryker\Zed\Kernel\Container;
use SprykerTest\Zed\Discount\DiscountBusinessTester;
use SprykerTest\Zed\Discount\DiscountRuleWithAttributesPlugin;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group Facade
 * @group BuildDiscountRuleGroupFromQueryStringFacadeTest
 * Add your own group annotations below this line
 */
class BuildDiscountRuleGroupFromQueryStringFacadeTest extends Unit
{
    protected const string CONDITION_AND = 'and';

    protected const string CONDITION_OR = 'or';

    protected const string ATTRIBUTE_FIELD_NAME = 'product-attribute';

    protected const string ATTRIBUTE_TYPE = 'color';

    protected const string UNKNOWN_TYPE = 'unknown-type';

    protected DiscountBusinessTester $tester;

    /**
     * @dataProvider provideConvertibleQueryStrings
     *
     * @param array<string, mixed> $expectedRuleGroup
     */
    public function testConvertsAQueryStringIntoANestedRuleGroup(
        string $type,
        string $queryString,
        array $expectedRuleGroup
    ): void {
        // Act
        $discountRuleGroupResponseTransfer = $this->createDiscountFacade()
            ->buildDiscountRuleGroupFromQueryString($type, $queryString);

        // Assert
        $this->assertTrue($discountRuleGroupResponseTransfer->getIsSuccessful());
        $this->assertSame(
            $expectedRuleGroup,
            $this->mapRuleGroupToArray($discountRuleGroupResponseTransfer->getDiscountRuleGroupOrFail()),
        );
    }

    /**
     * @return array<string, array{string, string, array<string, mixed>}>
     */
    public function provideConvertibleQueryStrings(): array
    {
        return [
            'given an empty query string, when it is converted, then the root group is empty' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                '',
                $this->buildExpectedGroup(null, []),
            ],
            'given the match all value, when it is converted, then it is kept as the rule value' => [
                MetaProviderFactory::TYPE_COLLECTOR,
                "sku = '*'",
                $this->buildExpectedGroup(null, [['sku', null, '=', '*']]),
            ],
            'given a mixed case value, when it is converted, then the case is preserved' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                "sku = 'AbC-123'",
                $this->buildExpectedGroup(null, [['sku', null, '=', 'AbC-123']]),
            ],
            'given a value in double quotes, when it is converted, then only the outer quote pair is stripped' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                'sku = "AbC-123"',
                $this->buildExpectedGroup(null, [['sku', null, '=', 'AbC-123']]),
            ],
            'given an or condition, when it is converted, then the root group condition is or' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                "sku = '1' or sku = '2'",
                $this->buildExpectedGroup(static::CONDITION_OR, [['sku', null, '=', '1'], ['sku', null, '=', '2']]),
            ],
            'given three nesting levels, when they are converted, then the deepest group is still built' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                "((sku = '1'))",
                $this->buildExpectedGroup(
                    null,
                    [],
                    [
                        $this->buildExpectedGroup(
                            null,
                            [],
                            [$this->buildExpectedGroup(null, [['sku', null, '=', '1']])],
                        ),
                    ],
                ),
            ],
            'given a fourth nesting level, when it is converted, then it is built like any other level' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                "(((sku = '1')))",
                $this->buildExpectedGroup(
                    null,
                    [],
                    [
                        $this->buildExpectedGroup(
                            null,
                            [],
                            [
                                $this->buildExpectedGroup(
                                    null,
                                    [],
                                    [$this->buildExpectedGroup(null, [['sku', null, '=', '1']])],
                                ),
                            ],
                        ),
                    ],
                ),
            ],
            'given both logical operators in one group, when it is converted, then the rules before the change become a nested group' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                "sku = '1' and sku = '2' or sku = '3'",
                $this->buildExpectedGroup(
                    static::CONDITION_OR,
                    [['sku', null, '=', '3']],
                    [$this->buildExpectedGroup(static::CONDITION_AND, [['sku', null, '=', '1'], ['sku', null, '=', '2']])],
                ),
            ],
            'given the operator changing twice, when it is converted, then the groups nest from left to right' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                "sku = '1' or sku = '2' and sku = '3' or sku = '4'",
                $this->buildExpectedGroup(
                    static::CONDITION_OR,
                    [['sku', null, '=', '4']],
                    [
                        $this->buildExpectedGroup(
                            static::CONDITION_AND,
                            [['sku', null, '=', '3']],
                            [$this->buildExpectedGroup(static::CONDITION_OR, [['sku', null, '=', '1'], ['sku', null, '=', '2']])],
                        ),
                    ],
                ),
            ],
            'given a parenthesised group before the operator changes, when it is converted, then it stays inside the nested group' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                "sku = '1' and (sku = '2' or sku = '3') or sku = '4'",
                $this->buildExpectedGroup(
                    static::CONDITION_OR,
                    [['sku', null, '=', '4']],
                    [
                        $this->buildExpectedGroup(
                            static::CONDITION_AND,
                            [['sku', null, '=', '1']],
                            [$this->buildExpectedGroup(static::CONDITION_OR, [['sku', null, '=', '2'], ['sku', null, '=', '3']])],
                        ),
                    ],
                ),
            ],
            'given an extra closing parenthesis at the end, when it is converted, then it is tolerated' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                "sku = '1')",
                $this->buildExpectedGroup(null, [['sku', null, '=', '1']]),
            ],
        ];
    }

    /**
     * @dataProvider provideRejectedQueryStrings
     */
    public function testReportsAQueryStringItCannotConvertAsUnsuccessful(string $type, string $queryString): void
    {
        // Act
        $discountRuleGroupResponseTransfer = $this->createDiscountFacade()
            ->buildDiscountRuleGroupFromQueryString($type, $queryString);

        // Assert
        $this->assertFalse($discountRuleGroupResponseTransfer->getIsSuccessful());
        $this->assertNull($discountRuleGroupResponseTransfer->getDiscountRuleGroup());
        $this->assertCount(1, $discountRuleGroupResponseTransfer->getErrors());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function provideRejectedQueryStrings(): array
    {
        return [
            'given an unbalanced opening parenthesis, when it is converted, then the mismatch is reported' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                "(sku = '1'",
            ],
            'given an unknown field, when it is converted, then the token is reported' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                "unknown-field = '1'",
            ],
            'given an unknown operator, when it is converted, then the token is reported' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                "sku ~~ '1'",
            ],
            'given a value without a preceding operator, when it is converted, then the token is reported' => [
                MetaProviderFactory::TYPE_DECISION_RULE,
                "sku '1'",
            ],
            'given a decision rule only field used as a collector field, when it is converted, then the token is reported' => [
                MetaProviderFactory::TYPE_COLLECTOR,
                "sub-total >= '500'",
            ],
            'given an unknown query string type, when it is converted, then the type is reported' => [
                static::UNKNOWN_TYPE,
                "sku = '1'",
            ],
        ];
    }

    public function testSplitsAnAttributeFieldIntoFieldAndAttribute(): void
    {
        // Arrange
        $discountFacade = $this->createDiscountFacadeWithDecisionRulePlugin($this->createAttributesPluginMock());

        // Act
        $discountRuleGroupResponseTransfer = $discountFacade->buildDiscountRuleGroupFromQueryString(
            MetaProviderFactory::TYPE_DECISION_RULE,
            sprintf("%s.%s = 'red'", static::ATTRIBUTE_FIELD_NAME, static::ATTRIBUTE_TYPE),
        );

        // Assert
        $this->assertTrue($discountRuleGroupResponseTransfer->getIsSuccessful());
        $this->assertSame(
            $this->buildExpectedGroup(null, [[static::ATTRIBUTE_FIELD_NAME, static::ATTRIBUTE_TYPE, '=', 'red']]),
            $this->mapRuleGroupToArray($discountRuleGroupResponseTransfer->getDiscountRuleGroupOrFail()),
        );
    }

    /**
     * @param array<int, array{string, string|null, string, string}> $rules
     * @param array<int, array<string, mixed>> $groups
     *
     * @return array<string, mixed>
     */
    protected function buildExpectedGroup(?string $condition, array $rules, array $groups = []): array
    {
        return ['condition' => $condition, 'rules' => $rules, 'groups' => $groups];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapRuleGroupToArray(DiscountRuleGroupTransfer $discountRuleGroupTransfer): array
    {
        return $this->buildExpectedGroup(
            $discountRuleGroupTransfer->getCondition(),
            array_map(
                static fn ($discountRuleTransfer): array => [
                    $discountRuleTransfer->getField(),
                    $discountRuleTransfer->getAttribute(),
                    $discountRuleTransfer->getOperator(),
                    $discountRuleTransfer->getValue(),
                ],
                array_values($discountRuleGroupTransfer->getRules()->getArrayCopy()),
            ),
            array_map(
                fn (DiscountRuleGroupTransfer $nestedDiscountRuleGroupTransfer): array => $this->mapRuleGroupToArray($nestedDiscountRuleGroupTransfer),
                array_values($discountRuleGroupTransfer->getGroups()->getArrayCopy()),
            ),
        );
    }

    protected function createDiscountFacade(): DiscountFacadeInterface
    {
        return $this->tester->getLocator()->discount()->facade();
    }

    protected function createDiscountFacadeWithDecisionRulePlugin(DecisionRulePluginInterface $decisionRulePlugin): DiscountFacadeInterface
    {
        $discountFacade = $this->createDiscountFacade();

        $container = new Container();
        $container->set(DiscountDependencyProvider::DECISION_RULE_PLUGINS, static fn (): array => [$decisionRulePlugin]);

        $discountBusinessFactory = new DiscountBusinessFactory();
        $discountBusinessFactory->setContainer($container);

        $discountFacade->setFactory($discountBusinessFactory);

        return $discountFacade;
    }

    protected function createAttributesPluginMock(): DecisionRulePluginInterface
    {
        $decisionRulePluginMock = $this->getMockBuilder(DiscountRuleWithAttributesPlugin::class)->getMock();
        $decisionRulePluginMock->method('getFieldName')->willReturn(static::ATTRIBUTE_FIELD_NAME);
        $decisionRulePluginMock->method('getAttributeTypes')->willReturn([static::ATTRIBUTE_TYPE]);

        /** @var \Spryker\Zed\DiscountExtension\Dependency\Plugin\DecisionRulePluginInterface $decisionRulePluginMock */
        return $decisionRulePluginMock;
    }
}
