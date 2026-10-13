<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\Discount\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\DiscountCalculatorTransfer;
use Generated\Shared\Transfer\DiscountConditionsTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorCollectionTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountCriteriaTransfer;
use Generated\Shared\Transfer\DiscountGeneralTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\SortTransfer;
use Spryker\Glue\Discount\DiscountConfig as DiscountGlueConfig;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;
use Spryker\Zed\Discount\DiscountConfig;
use Spryker\Zed\Discount\DiscountDependencyProvider;
use Spryker\Zed\Discount\Exception\DiscountSortFieldNotSupportedException;
use Spryker\Zed\Discount\Exception\DiscountUuidMissingException;
use Spryker\Zed\Discount\Exception\DiscountUuidNotEnabledException;
use Spryker\Zed\DiscountExtension\Dependency\Plugin\DiscountConfiguratorCollectionExpanderPluginInterface;
use SprykerTest\Zed\Discount\DiscountBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group Facade
 * @group DiscountConfiguratorCollectionFacadeTest
 * Add your own group annotations below this line
 */
class DiscountConfiguratorCollectionFacadeTest extends Unit
{
    protected const string UNSUPPORTED_SORT_FIELD = 'validFrom';

    protected const string FIXTURE_ALPHA = 'alpha';

    protected const string FIXTURE_BETA = 'beta';

    protected const string FIXTURE_GAMMA = 'gamma';

    protected const string STORE_DE = 'DE';

    protected const string STORE_AT = 'AT';

    protected const string CURRENCY_EUR = 'EUR';

    protected const string VALID_FROM_EARLY = '2026-01-01 00:00:00';

    protected const string VALID_TO_EARLY = '2026-06-30 23:59:59';

    protected const string VALID_FROM_LATE = '2027-01-01 00:00:00';

    protected const string VALID_TO_LATE = '2027-06-30 23:59:59';

    protected const int PERSISTED_PERCENTAGE_AMOUNT = 1000;

    protected const int API_PERCENTAGE_AMOUNT = 10;

    protected const int GROSS_AMOUNT = 595;

    protected DiscountBusinessTester $tester;

    /**
     * @var array<string, \Generated\Shared\Transfer\DiscountGeneralTransfer>
     */
    protected array $discountGeneralTransfers = [];

    protected string $displayNamePrefix = '';

    protected function _before(): void
    {
        parent::_before();

        $this->tester->mockConfigMethod('isDiscountUuidEnabled', true);
    }

    /**
     * @dataProvider provideDiscountConditions
     *
     * @param array<int, string> $expectedFixtureKeys
     */
    public function testFiltersTheDiscountCollectionByTheGivenConditions(
        callable $buildConditions,
        array $expectedFixtureKeys
    ): void {
        // Arrange
        $this->haveDiscountFixtures();

        // Act
        $discountConfiguratorCollectionTransfer = $this->createDiscountFacade()->getDiscountConfiguratorCollection(
            (new DiscountCriteriaTransfer())->setDiscountConditions(
                $buildConditions($this->discountGeneralTransfers, $this->createConditions()),
            ),
        );

        // Assert
        $fixtureKeys = $this->mapCollectionToFixtureKeys($discountConfiguratorCollectionTransfer->getDiscountConfigurators()->getArrayCopy());
        sort($fixtureKeys);

        $this->assertSame($expectedFixtureKeys, $fixtureKeys);
    }

    public function testRunsTheCollectionExpanderPluginsOncePerReadWithEveryDiscount(): void
    {
        // Arrange
        $discountIds = [
            $this->tester->haveDiscountForCollection([], [static::STORE_DE])->getIdDiscountOrFail(),
            $this->tester->haveDiscountForCollection([], [static::STORE_DE])->getIdDiscountOrFail(),
            $this->tester->haveDiscountForCollection([], [static::STORE_DE])->getIdDiscountOrFail(),
        ];
        $collectionExpanderPlugin = new class implements DiscountConfiguratorCollectionExpanderPluginInterface {
            /**
             * @var array<int, int>
             */
            public array $seenDiscountIdsPerCall = [];

            public function expand(DiscountConfiguratorCollectionTransfer $discountConfiguratorCollectionTransfer): DiscountConfiguratorCollectionTransfer
            {
                $this->seenDiscountIdsPerCall[] = $discountConfiguratorCollectionTransfer->getDiscountConfigurators()->count();

                return $discountConfiguratorCollectionTransfer;
            }
        };
        $this->tester->setDependency(DiscountDependencyProvider::PLUGINS_DISCOUNT_CONFIGURATOR_COLLECTION_EXPANDER, [$collectionExpanderPlugin]);

        // Act
        $this->createDiscountFacade()->getDiscountConfiguratorCollection(
            (new DiscountCriteriaTransfer())->setDiscountConditions((new DiscountConditionsTransfer())->setDiscountIds($discountIds)),
        );

        // Assert
        $this->assertSame([count($discountIds)], $collectionExpanderPlugin->seenDiscountIdsPerCall);
    }

    public function testRejectsEveryReadWhenTheUuidIsNotEnabled(): void
    {
        // Arrange
        $this->tester->mockConfigMethod('isDiscountUuidEnabled', false);

        // Assert
        $this->expectException(DiscountUuidNotEnabledException::class);
        $this->expectExceptionMessage('isDiscountUuidEnabled');

        // Act
        $this->createDiscountFacade()->getDiscountConfiguratorCollection(new DiscountCriteriaTransfer());
    }

    public function testNamesTheDiscountsWithoutUuidAndTheFixWhenOneIsMissing(): void
    {
        // Arrange
        $idDiscount = $this->tester->haveDiscountForCollection([], [static::STORE_DE])->getIdDiscountOrFail();
        $this->tester->removeDiscountUuid($idDiscount);

        // Assert
        $this->expectException(DiscountUuidMissingException::class);
        $this->expectExceptionMessage(sprintf('ids %d have no uuid', $idDiscount));

        // Act
        $this->createDiscountFacade()->getDiscountConfiguratorCollection(
            (new DiscountCriteriaTransfer())->setDiscountConditions((new DiscountConditionsTransfer())->addIdDiscount($idDiscount)),
        );
    }

    /**
     * @return array<string, array{callable, array<int, string>}>
     */
    public function provideDiscountConditions(): array
    {
        return [
            'given a display name fragment, when the collection is read, then the matching discounts are returned' => [
                static fn (array $fixtures, DiscountConditionsTransfer $discountConditionsTransfer): DiscountConditionsTransfer => $discountConditionsTransfer->setDisplayName(
                    (string)$fixtures[self::FIXTURE_BETA]->getDisplayName(),
                ),
                [static::FIXTURE_BETA],
            ],
            'given a display name fragment in another case, when the collection is read, then the match is case insensitive' => [
                static fn (array $fixtures, DiscountConditionsTransfer $discountConditionsTransfer): DiscountConditionsTransfer => $discountConditionsTransfer->setDisplayName(
                    strtoupper((string)$fixtures[self::FIXTURE_BETA]->getDisplayName()),
                ),
                [static::FIXTURE_BETA],
            ],
            'given the discount ids of two fixtures, when the collection is read, then only those are returned' => [
                static fn (array $fixtures, DiscountConditionsTransfer $discountConditionsTransfer): DiscountConditionsTransfer => $discountConditionsTransfer
                    ->addIdDiscount($fixtures[self::FIXTURE_ALPHA]->getIdDiscountOrFail())
                    ->addIdDiscount($fixtures[self::FIXTURE_GAMMA]->getIdDiscountOrFail()),
                [static::FIXTURE_ALPHA, static::FIXTURE_GAMMA],
            ],
            'given the active flag, when the collection is read, then only active discounts are returned' => [
                static fn (array $fixtures, DiscountConditionsTransfer $discountConditionsTransfer): DiscountConditionsTransfer => $discountConditionsTransfer->setIsActive(true),
                [static::FIXTURE_ALPHA, static::FIXTURE_GAMMA],
            ],
            'given the inactive flag, when the collection is read, then only inactive discounts are returned' => [
                static fn (array $fixtures, DiscountConditionsTransfer $discountConditionsTransfer): DiscountConditionsTransfer => $discountConditionsTransfer->setIsActive(false),
                [static::FIXTURE_BETA],
            ],
            'given the voucher type, when the collection is read, then only voucher discounts are returned' => [
                static fn (array $fixtures, DiscountConditionsTransfer $discountConditionsTransfer): DiscountConditionsTransfer => $discountConditionsTransfer->addDiscountType(DiscountConstants::TYPE_VOUCHER),
                [static::FIXTURE_BETA],
            ],
            'given a store name, when the collection is read, then a discount in several stores is returned once' => [
                static fn (array $fixtures, DiscountConditionsTransfer $discountConditionsTransfer): DiscountConditionsTransfer => $discountConditionsTransfer->addStoreName(self::STORE_AT),
                [static::FIXTURE_BETA, static::FIXTURE_GAMMA],
            ],
            'given a store name nothing is assigned to, when the collection is read, then nothing is returned' => [
                static fn (array $fixtures, DiscountConditionsTransfer $discountConditionsTransfer): DiscountConditionsTransfer => $discountConditionsTransfer->addStoreName('DiscountCollectionUnknownStore'),
                [],
            ],
            'given a valid from bound, when the collection is read, then only discounts starting later are returned' => [
                static fn (array $fixtures, DiscountConditionsTransfer $discountConditionsTransfer): DiscountConditionsTransfer => $discountConditionsTransfer->setValidFrom(self::VALID_FROM_LATE),
                [static::FIXTURE_GAMMA],
            ],
            'given a valid to bound, when the collection is read, then only discounts ending earlier are returned' => [
                static fn (array $fixtures, DiscountConditionsTransfer $discountConditionsTransfer): DiscountConditionsTransfer => $discountConditionsTransfer->setValidTo(self::VALID_TO_EARLY),
                [static::FIXTURE_ALPHA, static::FIXTURE_BETA],
            ],
            'given the uuid of a fixture, when the collection is read, then only that discount is returned' => [
                static fn (array $fixtures, DiscountConditionsTransfer $discountConditionsTransfer): DiscountConditionsTransfer => $discountConditionsTransfer->addUuid(
                    (string)$fixtures[self::FIXTURE_ALPHA]->getUuid(),
                ),
                [static::FIXTURE_ALPHA],
            ],
            'given an unknown uuid, when the collection is read, then nothing is returned' => [
                static fn (array $fixtures, DiscountConditionsTransfer $discountConditionsTransfer): DiscountConditionsTransfer => $discountConditionsTransfer->addUuid('11111111-2222-3333-4444-555555555555'),
                [],
            ],
        ];
    }

    /**
     * @dataProvider provideSortCollections
     *
     * @param array<int, array{string, bool}> $sortFields
     * @param array<int, string> $expectedFixtureKeys
     */
    public function testSortsTheDiscountCollectionByTheMappedFields(array $sortFields, array $expectedFixtureKeys): void
    {
        // Arrange
        $this->haveDiscountFixtures();

        $discountCriteriaTransfer = (new DiscountCriteriaTransfer())->setDiscountConditions($this->createConditions());

        foreach ($sortFields as [$field, $isAscending]) {
            $discountCriteriaTransfer->addSort((new SortTransfer())->setField($field)->setIsAscending($isAscending));
        }

        // Act
        $discountConfiguratorCollectionTransfer = $this->createDiscountFacade()->getDiscountConfiguratorCollection($discountCriteriaTransfer);

        // Assert
        $this->assertSame($expectedFixtureKeys, $this->mapCollectionToFixtureKeys($discountConfiguratorCollectionTransfer->getDiscountConfigurators()->getArrayCopy()));
    }

    /**
     * @return array<string, array{array<int, array{string, bool}>, array<int, string>}>
     */
    public function provideSortCollections(): array
    {
        return [
            'given no sort, when the collection is read, then the newest discount comes first' => [
                [],
                [static::FIXTURE_GAMMA, static::FIXTURE_BETA, static::FIXTURE_ALPHA],
            ],
            'given an ascending display name sort, when the collection is read, then the names are ordered' => [
                [['displayName', true]],
                [static::FIXTURE_ALPHA, static::FIXTURE_BETA, static::FIXTURE_GAMMA],
            ],
            'given a descending display name sort, when the collection is read, then the names are reversed' => [
                [['displayName', false]],
                [static::FIXTURE_GAMMA, static::FIXTURE_BETA, static::FIXTURE_ALPHA],
            ],
            'given an ascending priority sort, when the collection is read, then the priorities are ordered' => [
                [['priority', true]],
                [static::FIXTURE_ALPHA, static::FIXTURE_BETA, static::FIXTURE_GAMMA],
            ],
            'given a descending priority sort, when the collection is read, then the priorities are reversed' => [
                [['priority', false]],
                [static::FIXTURE_GAMMA, static::FIXTURE_BETA, static::FIXTURE_ALPHA],
            ],
            'given an ascending created at sort, when the collection is read, then the identity tiebreaker orders the ties' => [
                [['createdAt', true]],
                [static::FIXTURE_ALPHA, static::FIXTURE_BETA, static::FIXTURE_GAMMA],
            ],
            'given an ascending activity sort, when the collection is read, then the inactive discount comes first' => [
                [['isActive', true], ['displayName', true]],
                [static::FIXTURE_BETA, static::FIXTURE_ALPHA, static::FIXTURE_GAMMA],
            ],
            'given a descending exclusivity sort, when the collection is read, then the exclusive discounts come first' => [
                [['isExclusive', false], ['displayName', true]],
                [static::FIXTURE_ALPHA, static::FIXTURE_GAMMA, static::FIXTURE_BETA],
            ],
        ];
    }

    /**
     * @dataProvider providePaginations
     *
     * @param array<int, string> $expectedFixtureKeys
     */
    public function testPaginatesTheDiscountCollection(
        int $page,
        int $maxPerPage,
        array $expectedFixtureKeys,
        int $expectedLastPage
    ): void {
        // Arrange
        $this->haveDiscountFixtures();

        $discountCriteriaTransfer = (new DiscountCriteriaTransfer())
            ->setDiscountConditions($this->createConditions())
            ->setPagination((new PaginationTransfer())->setPage($page)->setMaxPerPage($maxPerPage))
            ->addSort((new SortTransfer())->setField('displayName')->setIsAscending(true));

        // Act
        $discountConfiguratorCollectionTransfer = $this->createDiscountFacade()->getDiscountConfiguratorCollection($discountCriteriaTransfer);

        // Assert
        $this->assertSame($expectedFixtureKeys, $this->mapCollectionToFixtureKeys($discountConfiguratorCollectionTransfer->getDiscountConfigurators()->getArrayCopy()));
        $this->assertSame(3, $discountConfiguratorCollectionTransfer->getPaginationOrFail()->getNbResults());
        $this->assertSame($expectedLastPage, $discountConfiguratorCollectionTransfer->getPaginationOrFail()->getLastPage());
    }

    /**
     * @return array<string, array{int, int, array<int, string>, int}>
     */
    public function providePaginations(): array
    {
        return [
            'given the first page of two, when the collection is read, then the first two discounts are returned' => [
                1, 2, [static::FIXTURE_ALPHA, static::FIXTURE_BETA], 2,
            ],
            'given the second page of two, when the collection is read, then the remaining discount is returned' => [
                2, 2, [static::FIXTURE_GAMMA], 2,
            ],
            'given a page beyond the range, when the collection is read, then the last page is returned' => [
                5, 2, [static::FIXTURE_GAMMA], 2,
            ],
            'given a page holding every discount, when the collection is read, then all of them are returned' => [
                1, 10, [static::FIXTURE_ALPHA, static::FIXTURE_BETA, static::FIXTURE_GAMMA], 1,
            ],
        ];
    }

    public function testExpandsEachDiscountConfiguratorWithItsRelationsAndApiUnits(): void
    {
        // Arrange
        $this->haveDiscountFixtures();

        // Act
        $discountConfiguratorCollectionTransfer = $this->createDiscountFacade()->getDiscountConfiguratorCollection(
            (new DiscountCriteriaTransfer())->setDiscountConditions(
                $this->createConditions()->addIdDiscount($this->discountGeneralTransfers[static::FIXTURE_BETA]->getIdDiscountOrFail()),
            ),
        );

        // Assert
        $discountConfiguratorTransfer = $discountConfiguratorCollectionTransfer->getDiscountConfigurators()->offsetGet(0);
        $discountGeneralTransfer = $discountConfiguratorTransfer->getDiscountGeneralOrFail();
        $discountCalculatorTransfer = $discountConfiguratorTransfer->getDiscountCalculatorOrFail();

        $this->assertSame(
            [static::STORE_AT, static::STORE_DE],
            $this->mapStoreRelationToSortedStoreNames($discountConfiguratorTransfer),
        );
        $this->assertSame(
            [static::CURRENCY_EUR],
            array_map(
                static fn ($moneyValueTransfer): ?string => $moneyValueTransfer->getCurrency()?->getCode(),
                array_values($discountCalculatorTransfer->getMoneyValueCollection()->getArrayCopy()),
            ),
        );
        $this->assertSame(
            DiscountConstants::DISCOUNT_COLLECTOR_STRATEGY_QUERY_STRING,
            $discountCalculatorTransfer->getCollectorStrategyType(),
        );
        $this->assertSame(static::API_PERCENTAGE_AMOUNT, $discountCalculatorTransfer->getAmount());
        $this->assertNotNull($discountGeneralTransfer->getCreatedAt());
        $this->assertNotNull($discountGeneralTransfer->getUuid());
    }

    public function testEverySortableFieldOfTheBackendApiIsMappedInTheModuleConfig(): void
    {
        // Act
        $sortableColumnMap = (new DiscountConfig())->getDiscountCollectionSortableColumnMap();

        // Assert
        $this->assertSame(
            [],
            array_diff((new DiscountGlueConfig())->getDiscountCollectionSortableFields(), array_keys($sortableColumnMap)),
        );
    }

    public function testRejectsASortFieldOutsideTheModuleConfigInsteadOfFallingBackToTheDefaultSort(): void
    {
        // Arrange
        $criteriaTransfer = (new DiscountCriteriaTransfer())->addSort(
            (new SortTransfer())->setField(static::UNSUPPORTED_SORT_FIELD)->setIsAscending(true),
        );

        // Assert
        $this->expectException(DiscountSortFieldNotSupportedException::class);
        $this->expectExceptionMessage(static::UNSUPPORTED_SORT_FIELD);

        // Act
        $this->createDiscountFacade()->getDiscountConfiguratorCollection($criteriaTransfer);
    }

    protected function haveDiscountFixtures(): void
    {
        $this->displayNamePrefix = uniqid('DiscountCollection', false);

        $this->discountGeneralTransfers = [
            static::FIXTURE_ALPHA => $this->tester->haveDiscountForCollection(
                [
                    DiscountGeneralTransfer::DISPLAY_NAME => $this->displayNamePrefix . '-a',
                    DiscountGeneralTransfer::DISCOUNT_TYPE => DiscountConstants::TYPE_CART_RULE,
                    DiscountGeneralTransfer::IS_ACTIVE => true,
                    DiscountGeneralTransfer::IS_EXCLUSIVE => true,
                    DiscountGeneralTransfer::PRIORITY => 10,
                    DiscountGeneralTransfer::VALID_FROM => static::VALID_FROM_EARLY,
                    DiscountGeneralTransfer::VALID_TO => static::VALID_TO_EARLY,
                ],
                [static::STORE_DE],
            ),
            static::FIXTURE_BETA => $this->tester->haveDiscountForCollection(
                [
                    DiscountGeneralTransfer::DISPLAY_NAME => $this->displayNamePrefix . '-b',
                    DiscountGeneralTransfer::DISCOUNT_TYPE => DiscountConstants::TYPE_VOUCHER,
                    DiscountGeneralTransfer::IS_ACTIVE => false,
                    DiscountGeneralTransfer::IS_EXCLUSIVE => false,
                    DiscountGeneralTransfer::PRIORITY => 20,
                    DiscountGeneralTransfer::VALID_FROM => static::VALID_FROM_EARLY,
                    DiscountGeneralTransfer::VALID_TO => static::VALID_TO_EARLY,
                ],
                [static::STORE_DE, static::STORE_AT],
                [
                    DiscountCalculatorTransfer::CALCULATOR_PLUGIN => DiscountDependencyProvider::PLUGIN_CALCULATOR_PERCENTAGE,
                    DiscountCalculatorTransfer::AMOUNT => static::PERSISTED_PERCENTAGE_AMOUNT,
                ],
            ),
            static::FIXTURE_GAMMA => $this->tester->haveDiscountForCollection(
                [
                    DiscountGeneralTransfer::DISPLAY_NAME => $this->displayNamePrefix . '-c',
                    DiscountGeneralTransfer::DISCOUNT_TYPE => DiscountConstants::TYPE_CART_RULE,
                    DiscountGeneralTransfer::IS_ACTIVE => true,
                    DiscountGeneralTransfer::IS_EXCLUSIVE => true,
                    DiscountGeneralTransfer::PRIORITY => 30,
                    DiscountGeneralTransfer::VALID_FROM => static::VALID_FROM_LATE,
                    DiscountGeneralTransfer::VALID_TO => static::VALID_TO_LATE,
                ],
                [static::STORE_AT],
            ),
        ];

        $this->tester->haveDiscountAmountForCurrency(
            $this->discountGeneralTransfers[static::FIXTURE_BETA]->getIdDiscountOrFail(),
            static::CURRENCY_EUR,
            static::GROSS_AMOUNT,
        );
    }

    protected function createConditions(): DiscountConditionsTransfer
    {
        return (new DiscountConditionsTransfer())->setDisplayName($this->displayNamePrefix);
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\DiscountConfiguratorTransfer> $discountConfiguratorTransfers
     *
     * @return array<int, string>
     */
    protected function mapCollectionToFixtureKeys(array $discountConfiguratorTransfers): array
    {
        $fixtureKeyByDisplayName = [];

        foreach ($this->discountGeneralTransfers as $fixtureKey => $discountGeneralTransfer) {
            $fixtureKeyByDisplayName[(string)$discountGeneralTransfer->getDisplayName()] = $fixtureKey;
        }

        $fixtureKeys = [];

        foreach ($discountConfiguratorTransfers as $discountConfiguratorTransfer) {
            $displayName = (string)$discountConfiguratorTransfer->getDiscountGeneralOrFail()->getDisplayName();
            $fixtureKeys[] = $fixtureKeyByDisplayName[$displayName] ?? $displayName;
        }

        return $fixtureKeys;
    }

    /**
     * @return array<int, string>
     */
    protected function mapStoreRelationToSortedStoreNames(DiscountConfiguratorTransfer $discountConfiguratorTransfer): array
    {
        $storeNames = [];

        foreach ($discountConfiguratorTransfer->getDiscountGeneralOrFail()->getStoreRelationOrFail()->getStores() as $storeTransfer) {
            $storeNames[] = $storeTransfer->getNameOrFail();
        }

        sort($storeNames);

        return $storeNames;
    }

    protected function createDiscountFacade(): DiscountFacadeInterface
    {
        /** @var \Spryker\Zed\Discount\Business\DiscountFacadeInterface $discountFacade */
        $discountFacade = $this->tester->getFacade();

        return $discountFacade;
    }
}
