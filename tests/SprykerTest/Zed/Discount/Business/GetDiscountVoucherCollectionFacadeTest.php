<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\Discount\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\DiscountVoucherConditionsTransfer;
use Generated\Shared\Transfer\DiscountVoucherCriteriaTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\SortTransfer;
use Spryker\Glue\Discount\DiscountConfig as DiscountGlueConfig;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;
use Spryker\Zed\Discount\DiscountConfig;
use Spryker\Zed\Discount\Exception\DiscountSortFieldNotSupportedException;
use SprykerTest\Zed\Discount\DiscountBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group Facade
 * @group GetDiscountVoucherCollectionFacadeTest
 * Add your own group annotations below this line
 */
class GetDiscountVoucherCollectionFacadeTest extends Unit
{
    protected const string UNSUPPORTED_SORT_FIELD = 'validFrom';

    protected const int VOUCHER_CODE_QUANTITY = 3;

    protected DiscountBusinessTester $tester;

    protected int $idDiscount = 0;

    protected int $idOtherDiscount = 0;

    protected string $customCode = '';

    /**
     * @var array<int, string>
     */
    protected array $codes = [];

    /**
     * @dataProvider provideDiscountVoucherConditions
     */
    public function testFiltersTheDiscountVoucherCollectionByTheGivenConditions(
        callable $arrangeConditions,
        int $expectedCount
    ): void {
        // Arrange
        $this->haveDiscountVoucherFixtures();
        $discountVoucherConditionsTransfer = new DiscountVoucherConditionsTransfer();
        $arrangeConditions($discountVoucherConditionsTransfer, $this);

        // Act
        $discountVoucherCollectionTransfer = $this->createDiscountFacade()->getDiscountVoucherCollection(
            (new DiscountVoucherCriteriaTransfer())->setDiscountVoucherConditions($discountVoucherConditionsTransfer),
        );

        // Assert
        $this->assertCount($expectedCount, $discountVoucherCollectionTransfer->getDiscountVouchers());
    }

    /**
     * @return array<string, array{callable, int}>
     */
    public function provideDiscountVoucherConditions(): array
    {
        return [
            'given the discount id, when the collection is read, then only its own codes are returned' => [
                static function (DiscountVoucherConditionsTransfer $conditions, self $test): void {
                    $conditions->addIdDiscount($test->getIdDiscount());
                },
                static::VOUCHER_CODE_QUANTITY,
            ],
            'given the discount id of another discount, when the collection is read, then only that one code is returned' => [
                static function (DiscountVoucherConditionsTransfer $conditions, self $test): void {
                    $conditions->addIdDiscount($test->getIdOtherDiscount());
                },
                1,
            ],
            'given one exact code, when the collection is read, then only that voucher is returned' => [
                static function (DiscountVoucherConditionsTransfer $conditions, self $test): void {
                    $conditions->addIdDiscount($test->getIdDiscount())->addCode($test->getCodes()[0]);
                },
                1,
            ],
            'given a code fragment, when the collection is read, then every partially matching voucher is returned' => [
                static function (DiscountVoucherConditionsTransfer $conditions, self $test): void {
                    $conditions->addIdDiscount($test->getIdDiscount())->setCode($test->getCustomCode());
                },
                static::VOUCHER_CODE_QUANTITY,
            ],
            'given a code fragment no voucher carries, when the collection is read, then nothing is returned' => [
                static function (DiscountVoucherConditionsTransfer $conditions, self $test): void {
                    $conditions->addIdDiscount($test->getIdDiscount())->setCode('GetDiscountVoucherCollectionNoSuchCode');
                },
                0,
            ],
            'given the active flag, when the collection is read, then the generated codes are returned' => [
                static function (DiscountVoucherConditionsTransfer $conditions, self $test): void {
                    $conditions->addIdDiscount($test->getIdDiscount())->setIsActive(true);
                },
                static::VOUCHER_CODE_QUANTITY,
            ],
            'given the inactive flag, when the collection is read, then nothing is returned' => [
                static function (DiscountVoucherConditionsTransfer $conditions, self $test): void {
                    $conditions->addIdDiscount($test->getIdDiscount())->setIsActive(false);
                },
                0,
            ],
            'given a voucher batch nothing was generated in, when the collection is read, then nothing is returned' => [
                static function (DiscountVoucherConditionsTransfer $conditions, self $test): void {
                    $conditions->addIdDiscount($test->getIdDiscount())->addVoucherBatch(0);
                },
                0,
            ],
        ];
    }

    /**
     * @dataProvider provideSortAndPaginationQueries
     *
     * @param array<int, array{string, bool}> $sortFields
     */
    public function testSortsAndPaginatesTheDiscountVoucherCollection(
        array $sortFields,
        ?int $page,
        ?int $maxPerPage,
        int $expectedCount,
        ?bool $expectedIsAscending
    ): void {
        // Arrange
        $this->haveDiscountVoucherFixtures();

        $discountVoucherCriteriaTransfer = (new DiscountVoucherCriteriaTransfer())->setDiscountVoucherConditions(
            (new DiscountVoucherConditionsTransfer())->addIdDiscount($this->idDiscount),
        );

        foreach ($sortFields as [$field, $isAscending]) {
            $discountVoucherCriteriaTransfer->addSort((new SortTransfer())->setField($field)->setIsAscending($isAscending));
        }

        if ($page !== null && $maxPerPage !== null) {
            $discountVoucherCriteriaTransfer->setPagination(
                (new PaginationTransfer())->setPage($page)->setMaxPerPage($maxPerPage),
            );
        }

        // Act
        $discountVoucherCollectionTransfer = $this->createDiscountFacade()->getDiscountVoucherCollection($discountVoucherCriteriaTransfer);

        // Assert
        $codes = [];

        foreach ($discountVoucherCollectionTransfer->getDiscountVouchers() as $discountVoucherTransfer) {
            $codes[] = $discountVoucherTransfer->getCodeOrFail();
        }

        $this->assertCount($expectedCount, $codes);

        if ($expectedIsAscending === null) {
            return;
        }

        $sortedCodes = $codes;
        $expectedIsAscending ? sort($sortedCodes) : rsort($sortedCodes);
        $this->assertSame($sortedCodes, $codes);
    }

    /**
     * @return array<string, array{array<int, array{string, bool}>, int|null, int|null, int, bool|null}>
     */
    public function provideSortAndPaginationQueries(): array
    {
        return [
            'given an ascending code sort, when the collection is read, then the codes are ordered' => [
                [['code', true]], null, null, static::VOUCHER_CODE_QUANTITY, true,
            ],
            'given a descending code sort, when the collection is read, then the codes are reversed' => [
                [['code', false]], null, null, static::VOUCHER_CODE_QUANTITY, false,
            ],
            'given the first page of two, when the collection is read, then two codes are returned' => [
                [['code', true]], 1, 2, 2, true,
            ],
            'given a page beyond the range, when the collection is read, then the last page is returned' => [
                [['code', true]], 9, 2, 1, true,
            ],
        ];
    }

    public function testEverySortableFieldOfTheBackendApiIsMappedInTheModuleConfig(): void
    {
        // Act
        $sortableColumnMap = (new DiscountConfig())->getDiscountVoucherCollectionSortableColumnMap();

        // Assert
        $this->assertSame(
            [],
            array_diff((new DiscountGlueConfig())->getDiscountVoucherCodeCollectionSortableFields(), array_keys($sortableColumnMap)),
        );
    }

    public function testRejectsASortFieldOutsideTheModuleConfigInsteadOfFallingBackToTheDefaultSort(): void
    {
        // Arrange
        $criteriaTransfer = (new DiscountVoucherCriteriaTransfer())->addSort(
            (new SortTransfer())->setField(static::UNSUPPORTED_SORT_FIELD)->setIsAscending(true),
        );

        // Assert
        $this->expectException(DiscountSortFieldNotSupportedException::class);
        $this->expectExceptionMessage(static::UNSUPPORTED_SORT_FIELD);

        // Act
        $this->createDiscountFacade()->getDiscountVoucherCollection($criteriaTransfer);
    }

    public function testCarriesTheDiscountIdOnEveryVoucherOfTheCollection(): void
    {
        // Arrange
        $this->haveDiscountVoucherFixtures();

        // Act
        $discountVoucherCollectionTransfer = $this->createDiscountFacade()->getDiscountVoucherCollection(
            (new DiscountVoucherCriteriaTransfer())->setDiscountVoucherConditions(
                (new DiscountVoucherConditionsTransfer())->addCode($this->codes[0]),
            ),
        );

        // Assert
        $this->assertCount(1, $discountVoucherCollectionTransfer->getDiscountVouchers());
        $this->assertSame($this->idDiscount, $discountVoucherCollectionTransfer->getDiscountVouchers()->offsetGet(0)->getIdDiscount());
    }

    public function getIdDiscount(): int
    {
        return $this->idDiscount;
    }

    public function getIdOtherDiscount(): int
    {
        return $this->idOtherDiscount;
    }

    public function getCustomCode(): string
    {
        return $this->customCode;
    }

    /**
     * @return array<int, string>
     */
    public function getCodes(): array
    {
        return $this->codes;
    }

    protected function haveDiscountVoucherFixtures(): void
    {
        $this->customCode = strtoupper(uniqid('VC', false));

        $this->idDiscount = $this->tester
            ->haveVoucherDiscountWithGeneratedCodes($this->customCode, static::VOUCHER_CODE_QUANTITY)
            ->getIdDiscountOrFail();
        $this->idOtherDiscount = $this->tester
            ->haveVoucherDiscountWithGeneratedCodes(strtoupper(uniqid('OT', false)))
            ->getIdDiscountOrFail();

        $this->codes = $this->tester->getDiscountVoucherCodesByIdDiscount($this->idDiscount);
    }

    protected function createDiscountFacade(): DiscountFacadeInterface
    {
        return $this->tester->getLocator()->discount()->facade();
    }
}
