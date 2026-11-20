<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\Facade;

use Codeception\Test\Unit;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaProviderFactory;
use Spryker\Zed\Discount\DiscountDependencyProvider;
use SprykerTest\Zed\Discount\DiscountBusinessTester;
use SprykerTest\Zed\Discount\Plugin\CollectorWithValueValidationStubPlugin;
use SprykerTest\Zed\Discount\Plugin\DecisionRuleWithValueValidationStubPlugin;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group Facade
 * @group ValidateQueryStringByTypeTest
 * Add your own group annotations below this line
 */
class ValidateQueryStringByTypeTest extends Unit
{
    protected const string FIELD_NAME = 'test-field';

    protected const string VALID_VALUE = 'existing-value';

    protected const string INVALID_VALUE = 'missing-value';

    protected const string MIXED_CASE_VALUE = 'Existing-Value';

    protected DiscountBusinessTester $tester;

    /**
     * @dataProvider provideExactValueQueryStrings
     */
    public function testValidateQueryStringByTypeReturnsAnErrorNamingTheValueTheFieldPluginDoesNotKnow(string $queryString): void
    {
        // Arrange
        $this->setValueValidationPlugin();

        // Act
        $errors = $this->validateDecisionRuleQueryString($queryString);

        // Assert
        $this->assertCount(1, $errors);
        $this->assertStringContainsString(static::INVALID_VALUE, $errors[0]);
        $this->assertStringNotContainsString(static::VALID_VALUE, $errors[0]);
    }

    /**
     * @return array<string, array<string>>
     */
    protected function provideExactValueQueryStrings(): array
    {
        return [
            'equal' => [sprintf('%s = "%s"', static::FIELD_NAME, static::INVALID_VALUE)],
            'not equal' => [sprintf('%s != "%s"', static::FIELD_NAME, static::INVALID_VALUE)],
            'is in' => [sprintf('%s is in "%s;%s"', static::FIELD_NAME, static::VALID_VALUE, static::INVALID_VALUE)],
            'is not in' => [sprintf('%s is not in "%s;%s"', static::FIELD_NAME, static::VALID_VALUE, static::INVALID_VALUE)],
        ];
    }

    public function testValidateQueryStringByTypePassesEveryListEntryToTheFieldPluginSeparately(): void
    {
        // Arrange
        $valueValidationStubPlugin = $this->setValueValidationPlugin();

        // Act
        $this->validateDecisionRuleQueryString(sprintf('%s is in "%s; %s"', static::FIELD_NAME, static::VALID_VALUE, static::INVALID_VALUE));

        // Assert
        $this->assertSame([[static::VALID_VALUE, static::INVALID_VALUE]], $valueValidationStubPlugin->receivedValues);
    }

    public function testValidateQueryStringByTypePassesTheValueLowercasedToTheFieldPlugin(): void
    {
        // Arrange
        $valueValidationStubPlugin = $this->setValueValidationPlugin();

        // Act
        $errors = $this->validateDecisionRuleQueryString(sprintf('%s is in "%s"', static::FIELD_NAME, static::MIXED_CASE_VALUE));

        // Assert
        $this->assertSame([], $errors);
        $this->assertSame([[static::VALID_VALUE]], $valueValidationStubPlugin->receivedValues);
    }

    public function testValidateQueryStringByTypeReturnsAnErrorNamingTheValueTheCollectorFieldPluginDoesNotKnow(): void
    {
        // Arrange
        $valueValidationStubPlugin = new CollectorWithValueValidationStubPlugin(static::FIELD_NAME, [static::VALID_VALUE]);
        $this->tester->setDependency(DiscountDependencyProvider::COLLECTOR_PLUGINS, [$valueValidationStubPlugin]);

        // Act
        $errors = $this->tester->getFacade()->validateQueryStringByType(
            MetaProviderFactory::TYPE_COLLECTOR,
            sprintf('%s is in "%s;%s"', static::FIELD_NAME, static::MIXED_CASE_VALUE, static::INVALID_VALUE),
        );

        // Assert
        $this->assertCount(1, $errors);
        $this->assertStringContainsString(static::INVALID_VALUE, $errors[0]);
        $this->assertSame([[static::VALID_VALUE, static::INVALID_VALUE]], $valueValidationStubPlugin->receivedValues);
    }

    public function testValidateQueryStringByTypeReturnsNoErrorWhenTheFieldPluginKnowsEveryValue(): void
    {
        // Arrange
        $this->setValueValidationPlugin();

        // Act
        $errors = $this->validateDecisionRuleQueryString(sprintf('%s = "%s"', static::FIELD_NAME, static::VALID_VALUE));

        // Assert
        $this->assertSame([], $errors);
    }

    /**
     * @dataProvider provideQueryStringsWithoutExactValue
     */
    public function testValidateQueryStringByTypeDoesNotValidateValuesThatAreNotComparedExactly(string $queryString): void
    {
        // Arrange
        $valueValidationStubPlugin = $this->setValueValidationPlugin();

        // Act
        $errors = $this->validateDecisionRuleQueryString($queryString);

        // Assert
        $this->assertSame([], $errors);
        $this->assertSame([], $valueValidationStubPlugin->receivedValues);
    }

    /**
     * @return array<string, array<string>>
     */
    protected function provideQueryStringsWithoutExactValue(): array
    {
        return [
            'contains' => [sprintf('%s contains "%s"', static::FIELD_NAME, static::INVALID_VALUE)],
            'does not contain' => [sprintf('%s does not contain "%s"', static::FIELD_NAME, static::INVALID_VALUE)],
            'match all' => [sprintf('%s = "*"', static::FIELD_NAME)],
        ];
    }

    protected function setValueValidationPlugin(): DecisionRuleWithValueValidationStubPlugin
    {
        $valueValidationStubPlugin = new DecisionRuleWithValueValidationStubPlugin(static::FIELD_NAME, [static::VALID_VALUE]);
        $this->tester->setDependency(DiscountDependencyProvider::DECISION_RULE_PLUGINS, [$valueValidationStubPlugin]);

        return $valueValidationStubPlugin;
    }

    /**
     * @return array<string>
     */
    protected function validateDecisionRuleQueryString(string $queryString): array
    {
        return $this->tester->getFacade()->validateQueryStringByType(MetaProviderFactory::TYPE_DECISION_RULE, $queryString);
    }
}
