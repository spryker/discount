<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business;

use ArrayObject;
use Codeception\Test\Unit;
use DateTime;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\ClauseTransfer;
use Generated\Shared\Transfer\CollectedDiscountTransfer;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\DiscountableItemTransfer;
use Generated\Shared\Transfer\DiscountTransfer;
use Generated\Shared\Transfer\DiscountVoucherTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use Orm\Zed\Discount\Persistence\SpyDiscountQuery;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Discount\Business\DiscountBusinessFactory;
use Spryker\Zed\Discount\Business\QueryString\ComparatorOperators;
use Spryker\Zed\Discount\Business\QueryString\Specification\MetaData\MetaProviderFactory;
use Spryker\Zed\Discount\Business\Voucher\VoucherValidator;
use Spryker\Zed\Discount\DiscountDependencyProvider;
use Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountRuleWithValueOptionsPluginInterface;
use Spryker\Zed\Kernel\Container;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group Facade
 * @group DiscountFacadeTest
 * Add your own group annotations below this line
 */
class DiscountFacadeTest extends Unit
{
    /**
     * @var string
     */
    protected const STORE_NAME_DE = 'DE';

    /**
     * @var string
     */
    protected const STORE_NAME_AT = 'AT';

    /**
     * @uses \Spryker\Zed\Discount\Persistence\Propel\Mapper\DiscountMapper::DATE_TIME_FORMAT
     *
     * @var string
     */
    protected const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var \SprykerTest\Zed\Discount\DiscountBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testIsSatisfiedBySkuShouldReturnTrueWhenGiveSkuIsInQuote(): void
    {
        $discountFacade = $this->createDiscountFacade();

        $quoteTransfer = new QuoteTransfer();
        $itemTransfer = new ItemTransfer();
        $itemTransfer->setSku('123');
        $quoteTransfer->addItem($itemTransfer);

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue('123');
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_STRING,
        ]);

        $isSatisfied = $discountFacade->isItemSkuSatisfiedBy($quoteTransfer, $itemTransfer, $clauseTransfer);

        $this->assertTrue($isSatisfied);
    }

    /**
     * @return void
     */
    public function testIsQuoteGrandTotalSatisfiedByShouldReturnTrueIfGrandTotalMatchesExpected(): void
    {
        $discountFacade = $this->createDiscountFacade();

        $quoteTransfer = new QuoteTransfer();
        $totalTransfer = new TotalsTransfer();
        $totalTransfer->setGrandTotal(1000);
        $quoteTransfer->setTotals($totalTransfer);

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue(10);
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_NUMBER,
        ]);

        $isSatisfied = $discountFacade->isQuoteGrandTotalSatisfiedBy(
            $quoteTransfer,
            new ItemTransfer(),
            $clauseTransfer,
        );

        $this->assertTrue($isSatisfied);
    }

    /**
     * @return void
     */
    public function testIsTotalQuantitySatisfiedByShouldReturnTrueWhenQuoteTotalQuantityMatchesExpected(): void
    {
        $discountFacade = $this->createDiscountFacade();

        $quoteTransfer = new QuoteTransfer();
        $itemTransfer = new ItemTransfer();
        $itemTransfer->setQuantity(2);
        $quoteTransfer->addItem($itemTransfer);

        $itemTransfer = new ItemTransfer();
        $itemTransfer->setQuantity(3);
        $quoteTransfer->addItem($itemTransfer);

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue(5);
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_NUMBER,
        ]);

        $isSatisfied = $discountFacade->isTotalQuantitySatisfiedBy(
            $quoteTransfer,
            $itemTransfer,
            $clauseTransfer,
        );

        $this->assertTrue($isSatisfied);
    }

    /**
     * @return void
     */
    public function testIsSubTotalSatisfiedByShouldReturnTrueWhenSubtotalMatchesExpected(): void
    {
        $discountFacade = $this->createDiscountFacade();

        $quoteTransfer = new QuoteTransfer();
        $totalsTransfer = new TotalsTransfer();
        $totalsTransfer->setSubtotal(5000);
        $quoteTransfer->setTotals($totalsTransfer);

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue(50);
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_NUMBER,
        ]);

        $isSatisfied = $discountFacade->isSubTotalSatisfiedBy(
            $quoteTransfer,
            new ItemTransfer(),
            $clauseTransfer,
        );

        $this->assertTrue($isSatisfied);
    }

    /**
     * @return void
     */
    public function testCollectBySkuShouldReturnItemMatchingGivenSku(): void
    {
        $discountFacade = $this->createDiscountFacade();

        $quoteTransfer = new QuoteTransfer();
        $itemTransfer = new ItemTransfer();
        $itemTransfer->setSku('sku');
        $quoteTransfer->addItem($itemTransfer);

        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue('sku');
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_NUMBER,
        ]);

        $collected = $discountFacade->collectBySku($quoteTransfer, $clauseTransfer);

        $this->assertCount(1, $collected);
    }

    /**
     * @return void
     */
    public function testGetQueryStringFieldsByTypeForCollectorShouldReturnListOfFieldsForGivenType(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $fields = $discountFacade->getQueryStringFieldsByType(MetaProviderFactory::TYPE_COLLECTOR);

        $this->assertNotEmpty($fields);
    }

    /**
     * @return void
     */
    public function testGetQueryStringFieldsByTypeForDecisionRuleShouldReturnListOfFieldsForGivenType(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $fields = $discountFacade->getQueryStringFieldsByType(MetaProviderFactory::TYPE_DECISION_RULE);

        $this->assertNotEmpty($fields);
    }

    /**
     * @return void
     */
    public function testGetQueryStringValueOptionsByTypeForCollectorShouldReturnListOfValueOptionsForGivenType(): void
    {
        $discountRulePluginMock = $this->createDiscountRuleWithValueOptionsPluginMock();
        $discountRulePluginMock->method('getQueryStringValueOptions')->willReturn(['a' => 'b']);
        $discountRulePluginMock->method('getFieldName')->willReturn('foo');

        $discountFacade = $this->createDiscountFacadeForDiscountRuleWithValueOptionsPlugin(
            DiscountDependencyProvider::COLLECTOR_PLUGINS,
            $discountRulePluginMock,
        );

        $fields = $discountFacade->getQueryStringValueOptions(MetaProviderFactory::TYPE_COLLECTOR);

        $this->assertNotEmpty($fields['foo']);
    }

    /**
     * @return void
     */
    public function testGetQueryStringValueOptionsByTypeForDecisionRuleShouldReturnListOfValueOptionsForGivenType(): void
    {
        $discountRulePluginMock = $this->createDiscountRuleWithValueOptionsPluginMock();
        $discountRulePluginMock->method('getQueryStringValueOptions')->willReturn(['a' => 'b']);
        $discountRulePluginMock->method('getFieldName')->willReturn('foo');

        $discountFacade = $this->createDiscountFacadeForDiscountRuleWithValueOptionsPlugin(
            DiscountDependencyProvider::DECISION_RULE_PLUGINS,
            $discountRulePluginMock,
        );

        $fields = $discountFacade->getQueryStringValueOptions(MetaProviderFactory::TYPE_DECISION_RULE);

        $this->assertNotEmpty($fields['foo']);
    }

    /**
     * @return void
     */
    public function testGetQueryStringFieldExpressionsForFieldCollectorShouldReturnListOfExpressions(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $expressions = $discountFacade->getQueryStringFieldExpressionsForField(MetaProviderFactory::TYPE_COLLECTOR, 'sku');

        $this->assertNotEmpty($expressions);
    }

    /**
     * @return void
     */
    public function testGetQueryStringFieldExpressionsForFieldDecisionRuleShouldReturnListOfExpressions(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $expressions = $discountFacade->getQueryStringFieldExpressionsForField(MetaProviderFactory::TYPE_DECISION_RULE, 'sku');

        $this->assertNotEmpty($expressions);
    }

    /**
     * @return void
     */
    public function testGetQueryStringComparatorExpressionsForDecisionRuleShouldReturnListOfComparatorExpressions(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $expressions = $discountFacade->getQueryStringComparatorExpressions(MetaProviderFactory::TYPE_DECISION_RULE);

        $this->assertNotEmpty($expressions);
    }

    /**
     * @return void
     */
    public function testGetQueryStringComparatorExpressionsForCollectorShouldReturnListOfComparatorExpressions(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $logicalComparators = $discountFacade->getQueryStringComparatorExpressions(MetaProviderFactory::TYPE_DECISION_RULE);

        $this->assertNotEmpty($logicalComparators);
    }

    /**
     * @return void
     */
    public function testGetQueryStringLogicalComparatorsShouldReturnListOfComparators(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $expressions = $discountFacade->getQueryStringLogicalComparators(MetaProviderFactory::TYPE_COLLECTOR);

        $this->assertNotEmpty($expressions);
    }

    /**
     * @return void
     */
    public function testQueryStringCompareShouldReturnTrueWhenValuesMatches(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue('value');
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_STRING,
        ]);

        $withValue = 'value';

        $isMatching = $discountFacade->queryStringCompare($clauseTransfer, $withValue);

        $this->assertTrue($isMatching);
    }

    /**
     * @return void
     */
    public function testQueryStringCompareShouldReturnFalseWhenValuesNotMatching(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue('value');
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_STRING,
        ]);

        $withValue = 'value2';

        $isMatching = $discountFacade->queryStringCompare($clauseTransfer, $withValue);

        $this->assertFalse($isMatching);
    }

    /**
     * @return void
     */
    public function testValidateQueryStringByTypeShouldReturnListErrorsWhenInvalidQueryString(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue('value');
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_STRING,
        ]);

        $errors = $discountFacade->validateQueryStringByType(MetaProviderFactory::TYPE_DECISION_RULE, 'invalid =');

        $this->assertCount(1, $errors);
    }

    /**
     * @return void
     */
    public function testValidateVoucherDiscountsMaxUsageShouldReturnTrueWhenUsageLimitIsNotReached(): void
    {
        // Arrange
        $discountVoucherTransfer = $this->getDiscountVoucher([
            DiscountVoucherTransfer::MAX_NUMBER_OF_USES => 5,
            DiscountVoucherTransfer::CUSTOM_CODE => 'functional spryker test voucher',
            DiscountVoucherTransfer::QUANTITY => 1,
            DiscountVoucherTransfer::RANDOM_GENERATED_CODE_LENGTH => 3,
        ]);
        $quoteTransfer = $this->getQuoteWithVoucherDiscount($discountVoucherTransfer);

        // Act
        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        $result = $this->createDiscountFacade()->validateVoucherDiscountsMaxUsage($quoteTransfer, $checkoutResponseTransfer);

        // Assert
        $this->assertCount(0, $checkoutResponseTransfer->getErrors());
        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function testValidateVoucherDiscountsMaxUsageShouldReturnFalseWhenUsageLimitIsReached(): void
    {
        // Arrange
        $maxNumberOfUses = 5;
        $discountVoucherTransfer = $this->getDiscountVoucher([
            DiscountVoucherTransfer::MAX_NUMBER_OF_USES => $maxNumberOfUses,
            DiscountVoucherTransfer::CUSTOM_CODE => 'functional spryker test voucher',
            DiscountVoucherTransfer::QUANTITY => 1,
            DiscountVoucherTransfer::RANDOM_GENERATED_CODE_LENGTH => 3,
        ]);
        $this->updateVoucherCodesWithNumberOfUses($discountVoucherTransfer, $maxNumberOfUses);
        $quoteTransfer = $this->getQuoteWithVoucherDiscount($discountVoucherTransfer);

        // Act
        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        $result = $this->createDiscountFacade()->validateVoucherDiscountsMaxUsage($quoteTransfer, $checkoutResponseTransfer);

        // Assert

        /** @var \Generated\Shared\Transfer\CheckoutErrorTransfer $checkoutErrorTransfer */
        $checkoutErrorTransfer = $checkoutResponseTransfer->getErrors()[0];

        $this->assertCount(1, $checkoutResponseTransfer->getErrors());
        $this->assertSame(399, $checkoutErrorTransfer->getErrorCode());
        $this->assertSame(VoucherValidator::REASON_VOUCHER_CODE_LIMIT_REACHED, $checkoutErrorTransfer->getMessage());
        $this->assertFalse($result);
        $this->assertFalse($checkoutResponseTransfer->getIsSuccess());
    }

    /**
     * @return void
     */
    public function testValidateVoucherDiscountsMaxUsageShouldReturnFalseWhenUsageLimitIsReachedForTwoVouchers(): void
    {
        // Arrange
        $maxNumberOfUses = 5;
        $discountVoucherTransfer = $this->getDiscountVoucher([
            DiscountVoucherTransfer::MAX_NUMBER_OF_USES => $maxNumberOfUses,
            DiscountVoucherTransfer::CUSTOM_CODE => 'functional spryker test voucher',
            DiscountVoucherTransfer::QUANTITY => 1,
        ]);
        $this->updateVoucherCodesWithNumberOfUses($discountVoucherTransfer, $maxNumberOfUses);
        $quoteTransfer = $this->getQuoteWithVoucherDiscount($discountVoucherTransfer);

        $discountVoucherTransfer2 = $this->getDiscountVoucher([
            DiscountVoucherTransfer::MAX_NUMBER_OF_USES => $maxNumberOfUses,
            DiscountVoucherTransfer::CUSTOM_CODE => 'second functional spryker test voucher',
            DiscountVoucherTransfer::QUANTITY => 1,
        ]);
        $this->updateVoucherCodesWithNumberOfUses($discountVoucherTransfer2, $maxNumberOfUses);
        $quoteTransfer->addVoucherDiscount(
            (new DiscountTransfer())->setVoucherCode($discountVoucherTransfer2->getCode()),
        );

        // Act
        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        $result = $this->createDiscountFacade()->validateVoucherDiscountsMaxUsage($quoteTransfer, $checkoutResponseTransfer);

        // Assert
        $this->assertCount(2, $checkoutResponseTransfer->getErrors());
        foreach ($checkoutResponseTransfer->getErrors() as $checkoutErrorTransfer) {
            $this->assertSame(399, $checkoutErrorTransfer->getErrorCode());
            $this->assertSame(VoucherValidator::REASON_VOUCHER_CODE_LIMIT_REACHED, $checkoutErrorTransfer->getMessage());
        }
        $this->assertFalse($result);
        $this->assertFalse($checkoutResponseTransfer->getIsSuccess());
    }

    /**
     * @return void
     */
    public function testValidateVoucherDiscountsMaxUsageShouldReturnTrueWhenMaxUsageZeroAndNotUsed(): void
    {
        // Arrange
        $discountVoucherTransfer = $this->getDiscountVoucher([
            DiscountVoucherTransfer::MAX_NUMBER_OF_USES => 0,
            DiscountVoucherTransfer::CUSTOM_CODE => 'functional spryker test voucher',
            DiscountVoucherTransfer::QUANTITY => 1,
            DiscountVoucherTransfer::RANDOM_GENERATED_CODE_LENGTH => 3,
        ]);
        $quoteTransfer = $this->getQuoteWithVoucherDiscount($discountVoucherTransfer);

        // Act
        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        $result = $this->createDiscountFacade()->validateVoucherDiscountsMaxUsage($quoteTransfer, $checkoutResponseTransfer);

        // Assert
        $this->assertTrue($result);
        $this->assertCount(0, $checkoutResponseTransfer->getErrors());
    }

    /**
     * @return void
     */
    public function testValidateVoucherDiscountsMaxUsageShouldReturnTrueWhenMaxUsageZeroAndUsed(): void
    {
        // Arrange
        $discountVoucherTransfer = $this->getDiscountVoucher([
            DiscountVoucherTransfer::MAX_NUMBER_OF_USES => 0,
            DiscountVoucherTransfer::CUSTOM_CODE => 'functional spryker test voucher',
            DiscountVoucherTransfer::QUANTITY => 1,
            DiscountVoucherTransfer::RANDOM_GENERATED_CODE_LENGTH => 3,
        ]);
        $this->updateVoucherCodesWithNumberOfUses($discountVoucherTransfer, 5);
        $quoteTransfer = $this->getQuoteWithVoucherDiscount($discountVoucherTransfer);

        // Act
        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        $result = $this->createDiscountFacade()->validateVoucherDiscountsMaxUsage($quoteTransfer, $checkoutResponseTransfer);

        // Assert
        $this->assertTrue($result);
        $this->assertCount(0, $checkoutResponseTransfer->getErrors());
    }

    /**
     * @return void
     */
    public function testSaveDiscountDecisionRuleShouldPersistAllConfiguredData(): void
    {
        $discountConfiguratorTransfer = $this->tester->createDiscountConfiguratorTransfer();

        $discountFacade = $this->createDiscountFacade();
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        $this->assertNotEmpty($idDiscount);

        $discountEntity = SpyDiscountQuery::create()->findOneByIdDiscount($idDiscount);

        $discountGeneralTransfer = $discountConfiguratorTransfer->getDiscountGeneral();
        $this->assertSame($discountGeneralTransfer->getDisplayName(), $discountEntity->getDisplayName());
        $this->assertSame($discountGeneralTransfer->getIsActive(), $discountEntity->getIsActive());
        $this->assertSame($discountGeneralTransfer->getIsExclusive(), $discountEntity->getIsExclusive());
        $this->assertSame($discountGeneralTransfer->getDescription(), $discountEntity->getDescription());
        $this->assertSame($discountGeneralTransfer->getValidFrom(), $discountEntity->getValidFrom()->format(static::DATE_TIME_FORMAT));
        $this->assertSame($discountGeneralTransfer->getValidTo(), $discountEntity->getValidTo()->format(static::DATE_TIME_FORMAT));

        $discountCalculatorTransfer = $discountConfiguratorTransfer->getDiscountCalculator();
        $this->assertSame($discountCalculatorTransfer->getAmount(), $discountEntity->getAmount());
        $this->assertSame($discountCalculatorTransfer->getCalculatorPlugin(), $discountEntity->getCalculatorPlugin());
        $this->assertSame($discountCalculatorTransfer->getCollectorQueryString(), $discountEntity->getCollectorQueryString());

        $discountConditionTransfer = $discountConfiguratorTransfer->getDiscountCondition();
        $this->assertSame($discountConditionTransfer->getDecisionRuleQueryString(), $discountEntity->getDecisionRuleQueryString());
    }

    /**
     * @return void
     */
    public function testSaveDiscountVoucherShouldCreateDiscountWithEmptyVoucherPool(): void
    {
        $discountConfiguratorTransfer = $this->tester->createDiscountConfiguratorTransfer();
        $discountConfiguratorTransfer->getDiscountGeneral()
            ->setDiscountType(DiscountConstants::TYPE_VOUCHER);

        $discountFacade = $this->createDiscountFacade();
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        $this->assertNotEmpty($idDiscount);

        $discountEntity = SpyDiscountQuery::create()->findOneByIdDiscount($idDiscount);

        $this->assertNotEmpty($discountEntity->getFkDiscountVoucherPool());

        $voucherPool = $discountEntity->getVoucherPool();
        $this->assertNotEmpty($voucherPool);
    }

    /**
     * @return void
     */
    public function testSaveDiscountPersistsStoreRelation(): void
    {
        // Arrange
        $idStores = [2];
        $discountFacade = $this->createDiscountFacade();

        $discountConfiguratorTransfer = $this->tester->createDiscountConfiguratorTransfer();
        $discountConfiguratorTransfer
            ->getDiscountGeneral()
                ->setDiscountType(DiscountConstants::TYPE_VOUCHER)
                ->getStoreRelation()
                    ->setIdStores($idStores);

        // Act
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        // Assert
        $discountConfiguratorTransfer = $discountFacade->getHydratedDiscountConfiguratorByIdDiscount($idDiscount);
        $this->assertEquals(
            $discountConfiguratorTransfer->getDiscountGeneral()->getStoreRelation()->getIdStores(),
            $idStores,
        );
    }

    /**
     * @return void
     */
    public function testUpdateDiscountPersistsStoreRelation(): void
    {
        // Arrange
        $atIdStore = $this->tester->haveStore([StoreTransfer::NAME => static::STORE_NAME_AT])->getIdStore();
        $deIdStore = $this->tester->haveStore([StoreTransfer::NAME => static::STORE_NAME_DE])->getIdStore();

        $originalIdStores = [$atIdStore];
        $expectedIdStores = [$deIdStore];

        $discountConfiguratorTransfer = $this->tester->createDiscountConfiguratorTransfer();
        $discountConfiguratorTransfer
            ->getDiscountGeneral()
                ->setDiscountType(DiscountConstants::TYPE_VOUCHER)
                ->getStoreRelation()
                    ->setIdStores($originalIdStores);

        $discountFacade = $this->createDiscountFacade();
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        $discountConfiguratorTransfer
            ->getDiscountGeneral()
                ->getStoreRelation()
                    ->setIdStores($expectedIdStores);

        // Act
        $discountFacade->updateDiscount($discountConfiguratorTransfer);

        // Assert
        $discountConfiguratorTransfer = $discountFacade->getHydratedDiscountConfiguratorByIdDiscount($idDiscount);
        $updatedStoreIds = $discountConfiguratorTransfer->getDiscountGeneral()->getStoreRelation()->getIdStores();
        sort($updatedStoreIds);
        sort($expectedIdStores);
        $this->assertEquals(
            $updatedStoreIds,
            $expectedIdStores,
        );
    }

    /**
     * @return void
     */
    public function testValidateQueryStringByTypeShouldReturnEmptySetWhenQueryStringIsValid(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $clauseTransfer = new ClauseTransfer();
        $clauseTransfer->setOperator('=');
        $clauseTransfer->setValue('value');
        $clauseTransfer->setAcceptedTypes([
            ComparatorOperators::TYPE_STRING,
        ]);

        $errors = $discountFacade->validateQueryStringByType(MetaProviderFactory::TYPE_DECISION_RULE, 'sku = "123"');

        $this->assertEmpty($errors);
    }

    /**
     * @return void
     */
    public function testUpdateDiscountShouldUpdateExistingRecordWithNewData(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $discountConfiguratorTransfer = $this->tester->createDiscountConfiguratorTransfer();
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        $discountConfiguratorTransfer
            ->getDiscountGeneral()
            ->setIdDiscount($idDiscount);

        $discountGeneralTransfer = $discountConfiguratorTransfer->getDiscountGeneral();
        $discountGeneralTransfer->setDisplayName('updated functional discount facade test');
        $discountGeneralTransfer->setDescription('Updated description');
        $discountGeneralTransfer->setIsActive(false);
        $discountGeneralTransfer->setIsExclusive(false);
        $discountGeneralTransfer->setValidFrom((new DateTime())->format(static::DATE_TIME_FORMAT));
        $discountGeneralTransfer->setValidTo((new DateTime('+1 day'))->format(static::DATE_TIME_FORMAT));

        $discountCalculatorTransfer = $discountConfiguratorTransfer->getDiscountCalculator();
        $discountCalculatorTransfer->setCalculatorPlugin(DiscountDependencyProvider::PLUGIN_CALCULATOR_FIXED);
        $discountCalculatorTransfer->setAmount(5);
        $discountCalculatorTransfer->setCollectorQueryString('sku = "new-sku"');

        $discountConditionTransfer = $discountConfiguratorTransfer->getDiscountCondition();
        $discountConditionTransfer->setDecisionRuleQueryString('sku = "new-decision-sku"');

        $isUpdated = $discountFacade->updateDiscount($discountConfiguratorTransfer);

        $this->assertTrue($isUpdated);

        $discountEntity = SpyDiscountQuery::create()->findOneByIdDiscount($idDiscount);

        $this->assertSame($discountGeneralTransfer->getDisplayName(), $discountEntity->getDisplayName());
        $this->assertSame($discountGeneralTransfer->getIsActive(), $discountEntity->getIsActive());
        $this->assertSame($discountGeneralTransfer->getIsExclusive(), $discountEntity->getIsExclusive());
        $this->assertSame($discountGeneralTransfer->getDescription(), $discountEntity->getDescription());
        $this->assertSame($discountGeneralTransfer->getValidFrom(), $discountEntity->getValidFrom()->format(static::DATE_TIME_FORMAT));
        $this->assertSame($discountGeneralTransfer->getValidTo(), $discountEntity->getValidTo()->format(static::DATE_TIME_FORMAT));

        $discountCalculatorTransfer = $discountConfiguratorTransfer->getDiscountCalculator();
        $this->assertSame($discountCalculatorTransfer->getAmount(), $discountEntity->getAmount());
        $this->assertSame($discountCalculatorTransfer->getCalculatorPlugin(), $discountEntity->getCalculatorPlugin());
        $this->assertSame($discountCalculatorTransfer->getCollectorQueryString(), $discountEntity->getCollectorQueryString());

        $discountConditionTransfer = $discountConfiguratorTransfer->getDiscountCondition();
        $this->assertSame($discountConditionTransfer->getDecisionRuleQueryString(), $discountEntity->getDecisionRuleQueryString());
    }

    /**
     * @return void
     */
    public function testGetHydratedDiscountConfiguratorByIdDiscountShouldHydrateToSameObjectWhichWasPersisted(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $discountConfiguratorTransfer = $this->tester->createDiscountConfiguratorTransfer();
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);
        $discountConfiguratorTransfer->getDiscountGeneral()->setIdDiscount($idDiscount);

        $hydratedDiscountConfiguratorTransfer = $discountFacade->getHydratedDiscountConfiguratorByIdDiscount(
            $idDiscount,
        );

        $originalConfiguratorArray = $discountConfiguratorTransfer->toArray();
        $hydratedConfiguratorArray = $hydratedDiscountConfiguratorTransfer->toArray();

        $discountEntity = SpyDiscountQuery::create()->findOneByIdDiscount($idDiscount);

        $this->assertEquals($originalConfiguratorArray, $hydratedConfiguratorArray);
        $this->assertTrue($discountEntity->getIsActive());
    }

    /**
     * @return void
     */
    public function testToggleDiscountVisibilityWhenDisableShouldSetToIsActiveToFalse(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $discountConfiguratorTransfer = $this->tester->createDiscountConfiguratorTransfer();
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        $isUpdated = $discountFacade->toggleDiscountVisibility($idDiscount, false);

        $discountEntity = SpyDiscountQuery::create()->findOneByIdDiscount($idDiscount);

        $this->assertTrue($isUpdated);
        $this->assertFalse($discountEntity->getIsActive());
    }

    /**
     * @return void
     */
    public function testToggleDiscountVisibilityWhenDisableShouldSetToIsActiveToTrue(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $discountConfiguratorTransfer = $this->tester->createDiscountConfiguratorTransfer();
        $discountConfiguratorTransfer->getDiscountGeneral()->setIsActive(false);
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        $isUpdated = $discountFacade->toggleDiscountVisibility($idDiscount, true);

        $this->assertTrue($isUpdated);
    }

    /**
     * @return void
     */
    public function testSaveVouchersShouldGenerateVouchers(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $discountConfiguratorTransfer = $this->tester->createDiscountConfiguratorTransfer();
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        $discountVoucherTransfer = new DiscountVoucherTransfer();
        $discountVoucherTransfer->setIdDiscount($idDiscount);
        $discountVoucherTransfer->setCustomCode('functional spryker test voucher');
        $discountVoucherTransfer->setMaxNumberOfUses(0);
        $discountVoucherTransfer->setQuantity(5);
        $discountVoucherTransfer->setRandomGeneratedCodeLength(10);

        $voucherCreateInfoTransfer = $discountFacade->saveVoucherCodes($discountVoucherTransfer);

        $this->assertEquals($voucherCreateInfoTransfer->getType(), DiscountConstants::MESSAGE_TYPE_SUCCESS);

        $discountEntity = SpyDiscountQuery::create()->findOneByIdDiscount($idDiscount);

        $voucherPoolEntity = $discountEntity->getVoucherPool();
        $voucherCodes = $voucherPoolEntity->getDiscountVouchers();

        $this->assertCount(5, $voucherCodes);
    }

    /**
     * @return void
     */
    public function testCalculatedPercentageShouldCalculatePercentageFromItemTotal(): void
    {
        $discountableItems = [];

        $itemTransfer = new ItemTransfer();
        $calculatedDiscounts = new ArrayObject();
        $itemTransfer->setCalculatedDiscounts($calculatedDiscounts);

        $discountableItemTransfer = new DiscountableItemTransfer();
        $discountableItemTransfer->setQuantity(3);
        $discountableItemTransfer->setUnitPrice(30);
        $discountableItemTransfer->setOriginalItemCalculatedDiscounts($calculatedDiscounts);
        $discountableItems[] = $discountableItemTransfer;

        $discountFacade = $this->createDiscountFacade();

        $discountTransfer = new DiscountTransfer();
        $discountTransfer->setAmount(10 * 100);

        $amount = $discountFacade->calculatePercentageDiscount($discountableItems, $discountTransfer);

        $this->assertSame(9, $amount);
    }

    /**
     * @return void
     */
    public function testCalculatedFixedShouldUseFixedAmountGiver(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $discountTransfer = new DiscountTransfer();
        $currencyTransfer = new CurrencyTransfer();
        $currencyTransfer->setCode('EUR');
        $discountTransfer->setCurrency($currencyTransfer);

        $moneyValueTransfer = new MoneyValueTransfer();
        $moneyValueTransfer->setGrossAmount(50);
        $moneyValueTransfer->setCurrency($currencyTransfer);
        $discountTransfer->addMoneyValue($moneyValueTransfer);
        $amount = $discountFacade->calculateFixedDiscount([], $discountTransfer);

        $this->assertSame(50, $amount);
    }

    /**
     * @return void
     */
    public function testDistributeAmountShouldDistributeDiscountToDiscountableItems(): void
    {
        $collectedDiscountTransfer = new CollectedDiscountTransfer();

        $totalDiscountAmount = 100;
        $discountTransfer = new DiscountTransfer();
        $discountTransfer->setAmount($totalDiscountAmount);
        $collectedDiscountTransfer->setDiscount($discountTransfer);

        $discountableItems = new ArrayObject();

        foreach ([100, 600] as $price) {
            $itemTransfer = new ItemTransfer();
            $calculatedDiscounts = new ArrayObject();
            $itemTransfer->setCalculatedDiscounts($calculatedDiscounts);

            $discountableItemTransfer = new DiscountableItemTransfer();
            $discountableItemTransfer->setQuantity(1);
            $discountableItemTransfer->setUnitPrice($price);
            $discountableItemTransfer->setOriginalItemCalculatedDiscounts($calculatedDiscounts);
            $discountableItems->append($discountableItemTransfer);
        }

        $collectedDiscountTransfer->setDiscountableItems($discountableItems);

        $discountFacade = $this->createDiscountFacade();
        $discountFacade->distributeAmount($collectedDiscountTransfer);

        $firstItemDistributedAmount = $discountableItems[0]->getOriginalItemCalculatedDiscounts()[0]->getUnitAmount();
        $secondItemDistributedAmount = $discountableItems[1]->getOriginalItemCalculatedDiscounts()[0]->getUnitAmount();

        $this->assertSame(14, $firstItemDistributedAmount);
        $this->assertSame(86, $secondItemDistributedAmount);
        $this->assertSame($totalDiscountAmount, $firstItemDistributedAmount + $secondItemDistributedAmount);
    }

    /**
     * @return void
     */
    public function testReleaseUsedVoucherCodesShouldSetNumberOfUsesCounterBack(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $discountConfiguratorTransfer = $this->tester->createDiscountConfiguratorTransfer();
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        $discountVoucherTransfer = new DiscountVoucherTransfer();
        $discountVoucherTransfer->setIdDiscount($idDiscount);
        $discountVoucherTransfer->setCustomCode('functional spryker test voucher');
        $discountVoucherTransfer->setMaxNumberOfUses(5);
        $discountVoucherTransfer->setQuantity(1);
        $discountVoucherTransfer->setRandomGeneratedCodeLength(3);

        $discountFacade->saveVoucherCodes($discountVoucherTransfer);

        $discountEntity = SpyDiscountQuery::create()->findOneByIdDiscount($idDiscount);

        $voucherPoolEntity = $discountEntity->getVoucherPool();
        $voucherCodes = $voucherPoolEntity->getDiscountVouchers();

        $voucherCodeList = [];
        foreach ($voucherCodes as $voucherCodeEntity) {
            $voucherCodeEntity->setNumberOfUses(1);
            $voucherCodeEntity->save();
            $voucherCodeList[] = $voucherCodeEntity->getCode();
        }

        $released = $discountFacade->releaseUsedVoucherCodes($voucherCodeList);

        $this->assertSame(1, $released);
    }

    /**
     * @return void
     */
    public function testUseVoucherCodesShouldUpdateVoucherCounterThatItWasAlreadyUsed(): void
    {
        $discountFacade = $this->createDiscountFacade();
        $discountConfiguratorTransfer = $this->tester->createDiscountConfiguratorTransfer();
        $idDiscount = $discountFacade->saveDiscount($discountConfiguratorTransfer);

        $discountVoucherTransfer = new DiscountVoucherTransfer();
        $discountVoucherTransfer->setIdDiscount($idDiscount);
        $discountVoucherTransfer->setCustomCode('functional spryker test voucher');
        $discountVoucherTransfer->setMaxNumberOfUses(5);
        $discountVoucherTransfer->setQuantity(1);
        $discountVoucherTransfer->setRandomGeneratedCodeLength(3);

        $discountFacade->saveVoucherCodes($discountVoucherTransfer);

        $discountEntity = SpyDiscountQuery::create()->findOneByIdDiscount($idDiscount);

        $voucherPoolEntity = $discountEntity->getVoucherPool();
        $voucherCodes = $voucherPoolEntity->getDiscountVouchers();

        $voucherCodeList = [];
        foreach ($voucherCodes as $voucherCodeEntity) {
            $voucherCodeList[] = $voucherCodeEntity->getCode();
        }

        $discountFacade->useVoucherCodes($voucherCodeList);

        $voucherPoolEntity->reload(true);
        $voucherCodes = $voucherPoolEntity->getDiscountVouchers();
        $voucherCodeEntity = $voucherCodes[0];

        $this->assertSame(1, $voucherCodeEntity->getNumberOfUses());
    }

    /**
     * @return void
     */
    public function testAddCartCodeAddsVoucherDiscountToQuote(): void
    {
        // Arrange
        $quoteTransfer = $this->tester->createQuoteTransferWithoutVoucherDiscount();

        // Act
        $resultQuoteTransfer = $this->createDiscountFacade()->addCartCode($quoteTransfer, $this->tester::VOUCHER_CODE);

        // Assert
        $this->assertCount(1, $quoteTransfer->getVoucherDiscounts());
        $this->assertSame(
            $this->tester::VOUCHER_CODE,
            $resultQuoteTransfer->getVoucherDiscounts()[0]->getVoucherCode(),
        );
    }

    /**
     * @return void
     */
    public function testAddCartCodeCantAddVoucherDiscountToQuoteWithVoucherCodeAlreadyAddedToQuote(): void
    {
        // Arrange
        $quoteTransfer = $this->tester->createQuoteTransferWithVoucherDiscount();

        // Act
        $resultQuoteTransfer = $this->createDiscountFacade()->addCartCode($quoteTransfer, $this->tester::VOUCHER_CODE);

        // Assert
        $this->assertCount(1, $resultQuoteTransfer->getVoucherDiscounts());
    }

    /**
     * @return void
     */
    public function testRemoveCartCodeRemovesVoucherDiscountFromQuote(): void
    {
        // Arrange
        $quoteTransfer = $this->tester->createQuoteTransferWithVoucherDiscount();

        // Act
        $resultQuoteTransfer = $this->createDiscountFacade()->removeCartCode($quoteTransfer, $this->tester::VOUCHER_CODE);

        // Assert
        $this->assertCount(0, $resultQuoteTransfer->getVoucherDiscounts());
    }

    /**
     * @return void
     */
    public function testClearCartCodesRemovesVoucherDiscountsFromQuote(): void
    {
        // Arrange
        $quoteTransfer = $this->tester->createQuoteTransferWithVoucherDiscount();

        // Act
        $resultQuoteTransfer = $this->createDiscountFacade()->clearCartCodes($quoteTransfer);

        // Assert
        $this->assertCount(0, $resultQuoteTransfer->getVoucherDiscounts());
    }

    /**
     * @return void
     */
    public function testFindOperationResponseMessageReturnsMessageTransfer(): void
    {
        // Arrange
        $quoteTransfer = $this->tester->createQuoteTransferWithVoucherDiscount();

        // Act
        $messageTransfer = $this->createDiscountFacade()->findOperationResponseMessage($quoteTransfer, $this->tester::VOUCHER_CODE);

        // Assert
        $this->assertNotNull($messageTransfer);
    }

    /**
     * @param array $override
     *
     * @return \Generated\Shared\Transfer\DiscountVoucherTransfer|\Spryker\Shared\Kernel\Transfer\AbstractTransfer
     */
    protected function getDiscountVoucher(array $override = [])
    {
        $discountGeneralTransfer = $this->tester->haveDiscount([
            'discountType' => DiscountConstants::TYPE_VOUCHER,
        ]);
        $override['idDiscount'] = $discountGeneralTransfer->getIdDiscount();
        $discountVoucherTransfer = $this->tester->haveGeneratedVoucherCodes($override);

        return $discountVoucherTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\DiscountVoucherTransfer $discountVoucherTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function getQuoteWithVoucherDiscount(DiscountVoucherTransfer $discountVoucherTransfer): QuoteTransfer
    {
        $quoteTransfer = new QuoteTransfer();
        $itemTransfer = new ItemTransfer();
        $itemTransfer->setSku('123');
        $quoteTransfer->addItem($itemTransfer);

        $discountTransfer = new DiscountTransfer();
        $discountTransfer->setVoucherCode($discountVoucherTransfer->getCode());
        $quoteTransfer->addVoucherDiscount($discountTransfer);

        return $quoteTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\DiscountVoucherTransfer $discountVoucherTransfer
     * @param int $numberOfUses
     *
     * @return void
     */
    protected function updateVoucherCodesWithNumberOfUses(DiscountVoucherTransfer $discountVoucherTransfer, int $numberOfUses): void
    {
        $voucherCodeEntities = SpyDiscountQuery::create()
            ->findOneByIdDiscount($discountVoucherTransfer->getIdDiscount())
            ->getVoucherPool()
            ->getDiscountVouchers();

        foreach ($voucherCodeEntities as $voucherCodeEntity) {
            $voucherCodeEntity
                ->setNumberOfUses($numberOfUses)
                ->save();
        }
    }

    /**
     * @return \Spryker\Zed\Discount\Business\DiscountFacadeInterface|\Spryker\Zed\Kernel\Business\AbstractFacade
     */
    protected function createDiscountFacade()
    {
        return $this->tester->getLocator()->discount()->facade();
    }

    /**
     * @param string $dependencyType
     * @param \Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountRuleWithValueOptionsPluginInterface|\PHPUnit\Framework\MockObject\MockObject $discountRulePluginMock
     *
     * @return \Spryker\Zed\Discount\Business\DiscountFacadeInterface|\Spryker\Zed\Kernel\Business\AbstractFacade
     */
    protected function createDiscountFacadeForDiscountRuleWithValueOptionsPlugin(string $dependencyType, $discountRulePluginMock)
    {
        $discountFacade = $this->createDiscountFacade();
        $factory = new DiscountBusinessFactory();
        $container = new Container();
        $container->set($dependencyType, function () use ($discountRulePluginMock) {
            return [
                $discountRulePluginMock,
            ];
        });
        $factory->setContainer($container);
        $discountFacade->setFactory($factory);

        return $discountFacade;
    }

    /**
     * @return \Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountRuleWithValueOptionsPluginInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createDiscountRuleWithValueOptionsPluginMock(): DiscountRuleWithValueOptionsPluginInterface
    {
        $discountRulePluginMock = $this->getMockBuilder(DiscountRuleWithValueOptionsPluginInterface::class)
            ->setMethods(['getQueryStringValueOptions', 'getFieldName'])
            ->getMock();

        return $discountRulePluginMock;
    }
}
