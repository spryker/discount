<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Communication\QueryBuilderTransformer;

use Codeception\Test\Unit;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaProviderFactory;
use Spryker\Zed\Discount\DiscountDependencyProvider;
use Spryker\Zed\DiscountExtension\Dependency\Plugin\DecisionRulePluginInterface;
use SprykerTest\Zed\Discount\DiscountCommunicationTester;
use SprykerTest\Zed\Discount\Plugin\DecisionRuleStubPlugin;
use SprykerTest\Zed\Discount\Plugin\DecisionRuleWithValueOptionsAndSuggestUrlStubPlugin;
use SprykerTest\Zed\Discount\Plugin\DecisionRuleWithValueOptionsStubPlugin;
use SprykerTest\Zed\Discount\Plugin\DecisionRuleWithValueSuggestUrlStubPlugin;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Communication
 * @group QueryBuilderTransformer
 * @group JavascriptQueryBuilderTransformerTest
 * Add your own group annotations below this line
 */
class JavascriptQueryBuilderTransformerTest extends Unit
{
    protected const string FIELD_NAME = 'test-field';

    protected const string SUGGEST_URL = '/test-module/suggest';

    protected DiscountCommunicationTester $tester;

    public function testGetFiltersRendersAFieldWithSuggestUrlAsSelect2WithTheSuggestUrl(): void
    {
        // Arrange
        $this->setDecisionRulePlugin(new DecisionRuleWithValueSuggestUrlStubPlugin(static::FIELD_NAME, static::SUGGEST_URL));

        // Act
        $filter = $this->getFilterByFieldName(static::FIELD_NAME);

        // Assert
        $this->assertSame('select', $filter['input']);
        $this->assertSame('select2', $filter['plugin']);
        $this->assertSame(static::SUGGEST_URL, $filter['suggest_url']);
        $this->assertArrayNotHasKey('values', $filter);
    }

    public function testGetFiltersPrefersTheSuggestUrlOverPreloadedValueOptions(): void
    {
        // Arrange
        $this->setDecisionRulePlugin(
            new DecisionRuleWithValueOptionsAndSuggestUrlStubPlugin(static::FIELD_NAME, ['a' => 'A'], static::SUGGEST_URL),
        );

        // Act
        $filter = $this->getFilterByFieldName(static::FIELD_NAME);

        // Assert
        $this->assertSame(static::SUGGEST_URL, $filter['suggest_url']);
        $this->assertArrayNotHasKey('values', $filter);
    }

    public function testGetFiltersKeepsPreloadedValueOptionsWithoutSuggestUrl(): void
    {
        // Arrange
        $this->setDecisionRulePlugin(new DecisionRuleWithValueOptionsStubPlugin(static::FIELD_NAME, ['a' => 'A']));

        // Act
        $filter = $this->getFilterByFieldName(static::FIELD_NAME);

        // Assert
        $this->assertSame(['a' => 'A'], $filter['values']);
        $this->assertArrayNotHasKey('suggest_url', $filter);
    }

    public function testGetFiltersKeepsATextInputForAFieldWithoutValueSource(): void
    {
        // Arrange
        $this->setDecisionRulePlugin(new DecisionRuleStubPlugin(static::FIELD_NAME));

        // Act
        $filter = $this->getFilterByFieldName(static::FIELD_NAME);

        // Assert
        $this->assertArrayNotHasKey('input', $filter);
        $this->assertArrayNotHasKey('suggest_url', $filter);
    }

    protected function setDecisionRulePlugin(DecisionRulePluginInterface $decisionRulePlugin): void
    {
        $this->tester->setDependency(DiscountDependencyProvider::DECISION_RULE_PLUGINS, [$decisionRulePlugin]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getFilterByFieldName(string $fieldName): array
    {
        $filters = $this->tester->getFactory()
            ->createJavascriptQueryBuilderTransformer()
            ->getFilters(MetaProviderFactory::TYPE_DECISION_RULE);

        return array_column($filters, null, 'id')[$fieldName];
    }
}
