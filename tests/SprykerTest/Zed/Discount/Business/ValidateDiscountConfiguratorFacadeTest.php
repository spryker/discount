<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\Discount\Business;

use Codeception\Stub;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\DiscountCalculatorTransfer;
use Generated\Shared\Transfer\DiscountConditionTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorResponseTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountGeneralTransfer;
use Generated\Shared\Transfer\ErrorCollectionTransfer;
use Generated\Shared\Transfer\ErrorTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Shared\Discount\DiscountConfig;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;
use Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\CalculatorPluginDiscountConfiguratorValidatorRule;
use Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\MoneyAmountCurrencyDiscountConfiguratorValidatorRule;
use Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\QueryStringDiscountConfiguratorValidatorRule;
use Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\StoreRelationDiscountConfiguratorValidatorRule;
use Spryker\Zed\Discount\Business\Validator\Rule\DiscountConfigurator\UniqueDisplayNameDiscountConfiguratorValidatorRule;
use Spryker\Zed\Discount\DiscountDependencyProvider;
use Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountConfiguratorValidatorRulePluginInterface;
use SprykerTest\Zed\Discount\DiscountBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group Facade
 * @group ValidateDiscountConfiguratorFacadeTest
 * Add your own group annotations below this line
 */
class ValidateDiscountConfiguratorFacadeTest extends Unit
{
    protected const string STORE_DE = 'DE';

    protected const string UNKNOWN_STORE = 'ValidateDiscountConfiguratorUnknownStore';

    protected const string UNKNOWN_CURRENCY = 'XTS';

    protected const string CURRENCY_EUR = 'EUR';

    protected const string UNKNOWN_CALCULATOR_PLUGIN = 'PLUGIN_CALCULATOR_DOES_NOT_EXIST';

    protected const string COLLECTOR_QUERY_STRING = "sku = '*'";

    protected const string DECISION_RULE_QUERY_STRING = "sub-total >= '500'";

    protected const string INVALID_QUERY_STRING = "unknown-field = '1'";

    protected const string VALID_FROM = '2026-01-01 00:00:00';

    protected const string VALID_TO = '2036-12-31 23:59:59';

    protected const int API_PERCENTAGE_AMOUNT = 10;

    protected const int PERSISTED_PERCENTAGE_AMOUNT = 1000;

    protected const int UNKNOWN_ID_DISCOUNT = 99999999;

    protected const int NET_AMOUNT = 500;

    protected const int GROSS_AMOUNT = 595;

    protected const string PLUGIN_ERROR_MESSAGE = 'The configurator is rejected by a project rule.';

    protected const string PLUGIN_ERROR_ENTITY_IDENTIFIER = 'discountGeneral.description';

    protected DiscountBusinessTester $tester;

    /**
     * @dataProvider provideDiscountConfigurators
     *
     * @param array<int, array<int, string>> $expectedErrors
     */
    public function testReportsEveryBusinessRuleErrorOfTheGivenConfigurator(
        callable $arrangeConfigurator,
        bool $expectedIsSuccessful,
        array $expectedErrors
    ): void {
        // Arrange
        $this->tester->haveStore([StoreTransfer::NAME => static::STORE_DE]);
        $discountConfiguratorTransfer = $this->createValidDiscountConfigurator();
        $arrangeConfigurator($discountConfiguratorTransfer, $this->tester);

        // Act
        $discountConfiguratorResponseTransfer = $this->createDiscountFacade()->validateDiscountConfigurator($discountConfiguratorTransfer);

        // Assert
        $this->assertSame($expectedIsSuccessful, $discountConfiguratorResponseTransfer->getIsSuccessful());
        $this->assertSame($expectedErrors, $this->mapErrorsToKeyAndIdentifier($discountConfiguratorResponseTransfer));
    }

    /**
     * @return array<string, array{callable, bool, array<int, array<int, string>>}>
     */
    public function provideDiscountConfigurators(): array
    {
        return [
            'given a valid configurator, when it is validated, then it is accepted without errors' => [
                static function (): void {
                },
                true,
                [],
            ],
            'given a display name another discount already uses, when it is validated, then the uniqueness rule rejects it' => [
                static function (DiscountConfiguratorTransfer $discountConfiguratorTransfer, DiscountBusinessTester $tester): void {
                    $discountConfiguratorTransfer->getDiscountGeneralOrFail()->setDisplayName(
                        (string)$tester->haveDiscountForCollection([], [self::STORE_DE])->getDisplayName(),
                    );
                },
                false,
                [[DiscountConfig::TRANSLATION_KEY_DISPLAY_NAME_NOT_UNIQUE, UniqueDisplayNameDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_DISPLAY_NAME]],
            ],
            'given a discount keeping its own display name, when it is validated, then the uniqueness rule ignores its own row' => [
                static function (DiscountConfiguratorTransfer $discountConfiguratorTransfer, DiscountBusinessTester $tester): void {
                    $discountGeneralTransfer = $tester->haveDiscountForCollection([], [self::STORE_DE]);

                    $discountConfiguratorTransfer->getDiscountGeneralOrFail()
                        ->setIdDiscount($discountGeneralTransfer->getIdDiscountOrFail())
                        ->setDisplayName((string)$discountGeneralTransfer->getDisplayName());
                },
                true,
                [],
            ],
            'given an unknown calculator plugin, when it is validated, then the calculator rule rejects it' => [
                static function (DiscountConfiguratorTransfer $discountConfiguratorTransfer): void {
                    $discountConfiguratorTransfer->getDiscountCalculatorOrFail()->setCalculatorPlugin(self::UNKNOWN_CALCULATOR_PLUGIN);
                },
                false,
                [[DiscountConfig::TRANSLATION_KEY_CALCULATOR_PLUGIN_INVALID, CalculatorPluginDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_CALCULATOR_PLUGIN]],
            ],
            'given a money input calculator without any money amount, when it is validated, then the money amount rule rejects it' => [
                static function (DiscountConfiguratorTransfer $discountConfiguratorTransfer): void {
                    $discountConfiguratorTransfer->getDiscountCalculatorOrFail()
                        ->setCalculatorPlugin(DiscountDependencyProvider::PLUGIN_CALCULATOR_FIXED)
                        ->setAmount(null);
                },
                false,
                [[DiscountConfig::TRANSLATION_KEY_MONEY_AMOUNT_INVALID, MoneyAmountCurrencyDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_MONEY_VALUE_COLLECTION]],
            ],
            'given a money amount in a currency no selected store carries, when it is validated, then the money amount rule rejects that entry' => [
                static function (DiscountConfiguratorTransfer $discountConfiguratorTransfer): void {
                    $discountConfiguratorTransfer->getDiscountCalculatorOrFail()
                        ->setCalculatorPlugin(DiscountDependencyProvider::PLUGIN_CALCULATOR_FIXED)
                        ->setAmount(null)
                        ->addMoneyValue(
                            (new MoneyValueTransfer())
                                ->setCurrency((new CurrencyTransfer())->setCode(self::UNKNOWN_CURRENCY))
                                ->setNetAmount(self::NET_AMOUNT)
                                ->setGrossAmount(self::GROSS_AMOUNT),
                        );
                },
                false,
                [[DiscountConfig::TRANSLATION_KEY_MONEY_AMOUNT_INVALID, MoneyAmountCurrencyDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_MONEY_VALUE_COLLECTION . '.0']],
            ],
            'given the Back Office money collection without a store selection, when it is validated, then the filled row is accepted and the empty rows are ignored' => [
                static function (DiscountConfiguratorTransfer $discountConfiguratorTransfer): void {
                    $discountConfiguratorTransfer->getDiscountGeneralOrFail()->setStoreRelation(new StoreRelationTransfer());
                    $discountConfiguratorTransfer->getDiscountCalculatorOrFail()
                        ->setCalculatorPlugin(DiscountDependencyProvider::PLUGIN_CALCULATOR_FIXED)
                        ->setAmount(null)
                        ->addMoneyValue(
                            (new MoneyValueTransfer())
                                ->setCurrency((new CurrencyTransfer())->setCode(self::CURRENCY_EUR))
                                ->setGrossAmount(self::GROSS_AMOUNT),
                        )
                        ->addMoneyValue((new MoneyValueTransfer())->setCurrency((new CurrencyTransfer())->setCode(self::UNKNOWN_CURRENCY)));
                },
                true,
                [],
            ],
            'given stores selected by id only, as the Back Office posts them, when it is validated, then the filled row is checked against those stores' => [
                static function (DiscountConfiguratorTransfer $discountConfiguratorTransfer, DiscountBusinessTester $tester): void {
                    $discountConfiguratorTransfer->getDiscountGeneralOrFail()->setStoreRelation(
                        (new StoreRelationTransfer())->addIdStores(
                            $tester->getLocator()->store()->facade()->getStoreByName(self::STORE_DE)->getIdStoreOrFail(),
                        ),
                    );
                    $discountConfiguratorTransfer->getDiscountCalculatorOrFail()
                        ->setCalculatorPlugin(DiscountDependencyProvider::PLUGIN_CALCULATOR_FIXED)
                        ->setAmount(null)
                        ->addMoneyValue(
                            (new MoneyValueTransfer())
                                ->setCurrency((new CurrencyTransfer())->setCode(self::CURRENCY_EUR))
                                ->setNetAmount(self::NET_AMOUNT)
                                ->setGrossAmount(self::GROSS_AMOUNT),
                        );
                },
                true,
                [],
            ],
            'given a filled row in a foreign currency behind an empty row, when it is validated, then the error names the submitted position' => [
                static function (DiscountConfiguratorTransfer $discountConfiguratorTransfer): void {
                    $discountConfiguratorTransfer->getDiscountCalculatorOrFail()
                        ->setCalculatorPlugin(DiscountDependencyProvider::PLUGIN_CALCULATOR_FIXED)
                        ->setAmount(null)
                        ->addMoneyValue((new MoneyValueTransfer())->setCurrency((new CurrencyTransfer())->setCode(self::CURRENCY_EUR)))
                        ->addMoneyValue(
                            (new MoneyValueTransfer())
                                ->setCurrency((new CurrencyTransfer())->setCode(self::UNKNOWN_CURRENCY))
                                ->setGrossAmount(self::GROSS_AMOUNT),
                        );
                },
                false,
                [[DiscountConfig::TRANSLATION_KEY_MONEY_AMOUNT_INVALID, MoneyAmountCurrencyDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_MONEY_VALUE_COLLECTION . '.1']],
            ],
            'given only empty money rows, when it is validated, then the money amount rule rejects the collection' => [
                static function (DiscountConfiguratorTransfer $discountConfiguratorTransfer): void {
                    $discountConfiguratorTransfer->getDiscountCalculatorOrFail()
                        ->setCalculatorPlugin(DiscountDependencyProvider::PLUGIN_CALCULATOR_FIXED)
                        ->setAmount(null)
                        ->addMoneyValue((new MoneyValueTransfer())->setCurrency((new CurrencyTransfer())->setCode(self::CURRENCY_EUR)))
                        ->addMoneyValue((new MoneyValueTransfer())->setCurrency((new CurrencyTransfer())->setCode(self::UNKNOWN_CURRENCY)));
                },
                false,
                [[DiscountConfig::TRANSLATION_KEY_MONEY_AMOUNT_INVALID, MoneyAmountCurrencyDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_MONEY_VALUE_COLLECTION]],
            ],
            'given an unknown store name, when it is validated, then the store relation rule rejects that entry' => [
                static function (DiscountConfiguratorTransfer $discountConfiguratorTransfer): void {
                    $discountConfiguratorTransfer->getDiscountGeneralOrFail()->setStoreRelation(
                        (new StoreRelationTransfer())->addStores((new StoreTransfer())->setName(self::UNKNOWN_STORE)),
                    );
                },
                false,
                [[DiscountConfig::TRANSLATION_KEY_STORE_UNKNOWN, StoreRelationDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_STORES . '.0']],
            ],
            'given the query string strategy without a collector string, when it is validated, then the query string rule rejects it' => [
                static function (DiscountConfiguratorTransfer $discountConfiguratorTransfer): void {
                    $discountConfiguratorTransfer->getDiscountCalculatorOrFail()->setCollectorQueryString('');
                },
                false,
                [[DiscountConfig::TRANSLATION_KEY_QUERY_STRING_INVALID, QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_COLLECTOR_QUERY_STRING]],
            ],
            'given an unparsable collector string, when it is validated, then the query string rule rejects it' => [
                static function (DiscountConfiguratorTransfer $discountConfiguratorTransfer): void {
                    $discountConfiguratorTransfer->getDiscountCalculatorOrFail()->setCollectorQueryString(self::INVALID_QUERY_STRING);
                },
                false,
                [[DiscountConfig::TRANSLATION_KEY_QUERY_STRING_INVALID, QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_COLLECTOR_QUERY_STRING]],
            ],
            'given an unparsable decision rule string, when it is validated, then the query string rule rejects it' => [
                static function (DiscountConfiguratorTransfer $discountConfiguratorTransfer): void {
                    $discountConfiguratorTransfer->getDiscountConditionOrFail()->setDecisionRuleQueryString(self::INVALID_QUERY_STRING);
                },
                false,
                [[DiscountConfig::TRANSLATION_KEY_QUERY_STRING_INVALID, QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_DECISION_RULE_QUERY_STRING]],
            ],
            'given several broken properties, when it is validated, then every rule reports its own error' => [
                static function (DiscountConfiguratorTransfer $discountConfiguratorTransfer): void {
                    $discountConfiguratorTransfer->getDiscountGeneralOrFail()->setStoreRelation(
                        (new StoreRelationTransfer())->addStores((new StoreTransfer())->setName(self::UNKNOWN_STORE)),
                    );
                    $discountConfiguratorTransfer->getDiscountCalculatorOrFail()
                        ->setCalculatorPlugin(self::UNKNOWN_CALCULATOR_PLUGIN)
                        ->setCollectorQueryString(self::INVALID_QUERY_STRING);
                },
                false,
                [
                    [DiscountConfig::TRANSLATION_KEY_CALCULATOR_PLUGIN_INVALID, CalculatorPluginDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_CALCULATOR_PLUGIN],
                    [DiscountConfig::TRANSLATION_KEY_STORE_UNKNOWN, StoreRelationDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_STORES . '.0'],
                    [DiscountConfig::TRANSLATION_KEY_QUERY_STRING_INVALID, QueryStringDiscountConfiguratorValidatorRule::ENTITY_IDENTIFIER_COLLECTOR_QUERY_STRING],
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideReusedValidatorCases
     */
    public function testKeepsReportingTheReusedPeriodAndExistenceValidatorsThroughMessages(
        callable $arrangeConfigurator,
        bool $expectedIsSuccessful,
        int $expectedMessageCount
    ): void {
        // Arrange
        $this->tester->haveStore([StoreTransfer::NAME => static::STORE_DE]);
        $discountConfiguratorTransfer = $this->createValidDiscountConfigurator();
        $arrangeConfigurator($discountConfiguratorTransfer);

        // Act
        $discountConfiguratorResponseTransfer = $this->createDiscountFacade()->validateDiscountConfigurator($discountConfiguratorTransfer);

        // Assert
        $this->assertSame($expectedIsSuccessful, $discountConfiguratorResponseTransfer->getIsSuccessful());
        $this->assertCount($expectedMessageCount, $discountConfiguratorResponseTransfer->getMessages());
        $this->assertCount(0, $discountConfiguratorResponseTransfer->getErrors());
    }

    /**
     * @return array<string, array{callable, bool, int}>
     */
    public function provideReusedValidatorCases(): array
    {
        return [
            'given no discount id, when it is validated, then the existence validator is not run' => [
                static function (): void {
                },
                true,
                0,
            ],
            'given a discount id no row carries, when it is validated, then the existence validator reports a message' => [
                static function (DiscountConfiguratorTransfer $discountConfiguratorTransfer): void {
                    $discountConfiguratorTransfer->getDiscountGeneralOrFail()->setIdDiscount(self::UNKNOWN_ID_DISCOUNT);
                },
                false,
                1,
            ],
            'given a validity window ending before it starts, when it is validated, then the period validator reports a message' => [
                static function (DiscountConfiguratorTransfer $discountConfiguratorTransfer): void {
                    $discountConfiguratorTransfer->getDiscountGeneralOrFail()
                        ->setValidFrom(self::VALID_TO)
                        ->setValidTo(self::VALID_FROM);
                },
                false,
                1,
            ],
        ];
    }

    public function testExecutesTheValidatorRulePluginsForTheConfiguratorButNotForItsRulesOnly(): void
    {
        // Arrange
        $this->tester->haveStore([StoreTransfer::NAME => static::STORE_DE]);
        $this->tester->setDependency(
            DiscountDependencyProvider::PLUGINS_DISCOUNT_CONFIGURATOR_VALIDATOR_RULE,
            [$this->createValidatorRulePluginStub()],
        );
        $discountFacade = $this->createDiscountFacade();

        // Act
        $configuratorResponseTransfer = $discountFacade->validateDiscountConfigurator($this->createValidDiscountConfigurator());
        $rulesResponseTransfer = $discountFacade->validateDiscountConfiguratorRules($this->createValidDiscountConfigurator());

        // Assert
        $this->assertFalse($configuratorResponseTransfer->getIsSuccessful());
        $this->assertSame(
            [[static::PLUGIN_ERROR_MESSAGE, static::PLUGIN_ERROR_ENTITY_IDENTIFIER]],
            $this->mapErrorsToKeyAndIdentifier($configuratorResponseTransfer),
        );
        $this->assertTrue($rulesResponseTransfer->getIsSuccessful());
        $this->assertCount(0, $rulesResponseTransfer->getErrors());
    }

    public function testLeavesTheExistingCreateAndUpdatePathsFreeOfTheNewRules(): void
    {
        // Arrange
        $this->tester->haveStore([StoreTransfer::NAME => static::STORE_DE]);

        $createConfiguratorTransfer = $this->createValidDiscountConfigurator();
        $createConfiguratorTransfer->getDiscountCalculatorOrFail()->setCalculatorPlugin(static::UNKNOWN_CALCULATOR_PLUGIN);

        $updateConfiguratorTransfer = $this->createValidDiscountConfigurator();
        $updateConfiguratorTransfer->getDiscountCalculatorOrFail()->setCalculatorPlugin(static::UNKNOWN_CALCULATOR_PLUGIN);

        $discountFacade = $this->createDiscountFacade();

        // Act
        $createResponseTransfer = $discountFacade->createDiscount($createConfiguratorTransfer);

        $updateConfiguratorTransfer->getDiscountGeneralOrFail()->setIdDiscount(
            $createResponseTransfer->getDiscountConfiguratorOrFail()->getDiscountGeneralOrFail()->getIdDiscountOrFail(),
        );
        $updateResponseTransfer = $discountFacade->updateDiscountWithValidation($updateConfiguratorTransfer);

        // Assert
        $this->assertTrue($createResponseTransfer->getIsSuccessful());
        $this->assertTrue($updateResponseTransfer->getIsSuccessful());
        $this->assertCount(0, $createResponseTransfer->getErrors());
        $this->assertCount(0, $updateResponseTransfer->getErrors());
    }

    protected function createValidDiscountConfigurator(): DiscountConfiguratorTransfer
    {
        return (new DiscountConfiguratorTransfer())
            ->setDiscountGeneral(
                (new DiscountGeneralTransfer())
                    ->setDisplayName(uniqid('ValidateDiscountConfigurator', false))
                    ->setDiscountType(DiscountConstants::TYPE_CART_RULE)
                    ->setIsActive(false)
                    ->setIsExclusive(false)
                    ->setValidFrom(static::VALID_FROM)
                    ->setValidTo(static::VALID_TO)
                    ->setStoreRelation(
                        (new StoreRelationTransfer())->addStores((new StoreTransfer())->setName(static::STORE_DE)),
                    ),
            )
            ->setDiscountCalculator(
                (new DiscountCalculatorTransfer())
                    ->setCalculatorPlugin(DiscountDependencyProvider::PLUGIN_CALCULATOR_PERCENTAGE)
                    ->setAmount(static::API_PERCENTAGE_AMOUNT)
                    ->setCollectorStrategyType(DiscountConstants::DISCOUNT_COLLECTOR_STRATEGY_QUERY_STRING)
                    ->setCollectorQueryString(static::COLLECTOR_QUERY_STRING),
            )
            ->setDiscountCondition(
                (new DiscountConditionTransfer())
                    ->setMinimumItemAmount(1)
                    ->setDecisionRuleQueryString(static::DECISION_RULE_QUERY_STRING),
            );
    }

    /**
     * @return array<int, array<int, string>>
     */
    protected function mapErrorsToKeyAndIdentifier(
        DiscountConfiguratorResponseTransfer $discountConfiguratorResponseTransfer
    ): array {
        $errors = [];

        foreach ($discountConfiguratorResponseTransfer->getErrors() as $errorTransfer) {
            $errors[] = [(string)$errorTransfer->getMessage(), (string)$errorTransfer->getEntityIdentifier()];
        }

        return $errors;
    }

    protected function createDiscountFacade(): DiscountFacadeInterface
    {
        return $this->tester->getLocator()->discount()->facade();
    }

    protected function createValidatorRulePluginStub(): DiscountConfiguratorValidatorRulePluginInterface
    {
        return Stub::makeEmpty(DiscountConfiguratorValidatorRulePluginInterface::class, [
            'validate' => (new ErrorCollectionTransfer())->addError(
                (new ErrorTransfer())
                    ->setMessage(static::PLUGIN_ERROR_MESSAGE)
                    ->setEntityIdentifier(static::PLUGIN_ERROR_ENTITY_IDENTIFIER),
            ),
        ]);
    }
}
