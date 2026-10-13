<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Zed\Discount\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\DiscountGeneralTransfer;
use Generated\Shared\Transfer\DiscountVoucherCollectionDeleteCriteriaTransfer;
use Generated\Shared\Transfer\DiscountVoucherConditionsTransfer;
use Generated\Shared\Transfer\DiscountVoucherCriteriaTransfer;
use Generated\Shared\Transfer\DiscountVoucherTransfer;
use Spryker\Shared\Discount\DiscountConfig;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Discount\Business\Deleter\DiscountVoucherDeleter;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;
use Spryker\Zed\Discount\Business\Validator\Rule\DiscountVoucher\DiscountTypeDiscountVoucherValidatorRule;
use SprykerTest\Zed\Discount\DiscountBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group Facade
 * @group DiscountVoucherWriteFacadeTest
 * Add your own group annotations below this line
 */
class DiscountVoucherWriteFacadeTest extends Unit
{
    protected const string UNKNOWN_CODE = 'DiscountVoucherWriteUnknownCode';

    protected const int UNKNOWN_ID_DISCOUNT = 99999999;

    protected const int VOUCHER_CODE_QUANTITY = 3;

    protected const int SECOND_BATCH_QUANTITY = 2;

    protected const int SECOND_BATCH_CODE_LENGTH = 6;

    protected const int SECOND_BATCH_MAX_NUMBER_OF_USES = 1;

    protected DiscountBusinessTester $tester;

    protected int $idDiscount = 0;

    protected int $idOtherDiscount = 0;

    /**
     * @var array<int, string>
     */
    protected array $codes = [];

    /**
     * @var array<int, string>
     */
    protected array $otherCodes = [];

    /**
     * @dataProvider provideDeleteCriteria
     */
    public function testDeletesOnlyTheCodesOwnedByTheGivenDiscount(
        callable $buildConditions,
        bool $expectedIsSuccessful,
        int $expectedDeletedCount,
        int $expectedRemainingCount
    ): void {
        // Arrange
        $this->haveDiscountVoucherFixtures();

        // Act
        $discountVoucherCollectionResponseTransfer = $this->createDiscountFacade()->deleteDiscountVoucherCollection(
            (new DiscountVoucherCollectionDeleteCriteriaTransfer())->setDiscountVoucherConditions(
                $buildConditions(new DiscountVoucherConditionsTransfer(), $this),
            ),
        );

        // Assert
        $this->assertSame($expectedIsSuccessful, $discountVoucherCollectionResponseTransfer->getIsSuccessful());
        $this->assertSame($expectedDeletedCount, (int)$discountVoucherCollectionResponseTransfer->getDeletedCount());
        $this->assertCount($expectedRemainingCount, $this->tester->getDiscountVoucherCodesByIdDiscount($this->idDiscount));
        $this->assertCount(1, $this->tester->getDiscountVoucherCodesByIdDiscount($this->idOtherDiscount));

        if ($expectedIsSuccessful) {
            return;
        }

        $ownCodes = $this->tester->getDiscountVoucherCodesByIdDiscount($this->idDiscount);
        $errorTransfers = $discountVoucherCollectionResponseTransfer->getErrors();
        $this->assertGreaterThan(0, $errorTransfers->count());

        foreach ($errorTransfers as $errorTransfer) {
            $this->assertSame(DiscountConfig::TRANSLATION_KEY_VOUCHER_CODE_NOT_FOUND, $errorTransfer->getMessage());
            $this->assertSame(DiscountVoucherDeleter::ENTITY_IDENTIFIER_CODE, $errorTransfer->getEntityIdentifier());
            $this->assertNotContains($errorTransfer->getParameters()[DiscountVoucherDeleter::ERROR_PARAMETER_CODE], $ownCodes);
        }
    }

    /**
     * @return array<string, array{callable, bool, int, int}>
     */
    public function provideDeleteCriteria(): array
    {
        return [
            'given a code of the given discount, when it is deleted, then only that code is gone' => [
                static fn (
                    DiscountVoucherConditionsTransfer $discountVoucherConditionsTransfer,
                    self $test
                ): DiscountVoucherConditionsTransfer => $discountVoucherConditionsTransfer
                    ->addIdDiscount($test->getIdDiscount())
                    ->addCode($test->getCodes()[0]),
                true,
                1,
                static::VOUCHER_CODE_QUANTITY - 1,
            ],
            'given every code of the given discount, when they are deleted, then none is left' => [
                static function (
                    DiscountVoucherConditionsTransfer $discountVoucherConditionsTransfer,
                    self $test
                ): DiscountVoucherConditionsTransfer {
                    $discountVoucherConditionsTransfer->addIdDiscount($test->getIdDiscount());

                    foreach ($test->getCodes() as $code) {
                        $discountVoucherConditionsTransfer->addCode($code);
                    }

                    return $discountVoucherConditionsTransfer;
                },
                true,
                static::VOUCHER_CODE_QUANTITY,
                0,
            ],
            'given a code belonging to another discount, when it is deleted, then nothing is removed' => [
                static fn (
                    DiscountVoucherConditionsTransfer $discountVoucherConditionsTransfer,
                    self $test
                ): DiscountVoucherConditionsTransfer => $discountVoucherConditionsTransfer
                    ->addIdDiscount($test->getIdDiscount())
                    ->addCode($test->getOtherCodes()[0]),
                false,
                0,
                static::VOUCHER_CODE_QUANTITY,
            ],
            'given a code no discount carries, when it is deleted, then nothing is removed' => [
                static fn (
                    DiscountVoucherConditionsTransfer $discountVoucherConditionsTransfer,
                    self $test
                ): DiscountVoucherConditionsTransfer => $discountVoucherConditionsTransfer
                    ->addIdDiscount($test->getIdDiscount())
                    ->addCode(self::UNKNOWN_CODE),
                false,
                0,
                static::VOUCHER_CODE_QUANTITY,
            ],
            'given an own code and a code of another discount, when they are deleted, then nothing is removed' => [
                static fn (
                    DiscountVoucherConditionsTransfer $discountVoucherConditionsTransfer,
                    self $test
                ): DiscountVoucherConditionsTransfer => $discountVoucherConditionsTransfer
                    ->addIdDiscount($test->getIdDiscount())
                    ->addCode($test->getCodes()[0])
                    ->addCode($test->getOtherCodes()[0]),
                false,
                0,
                static::VOUCHER_CODE_QUANTITY,
            ],
            'given no code, when the deletion runs, then every code of the discount is gone' => [
                static fn (
                    DiscountVoucherConditionsTransfer $discountVoucherConditionsTransfer,
                    self $test
                ): DiscountVoucherConditionsTransfer => $discountVoucherConditionsTransfer->addIdDiscount($test->getIdDiscount()),
                true,
                static::VOUCHER_CODE_QUANTITY,
                0,
            ],
            'given no discount id, when the deletion runs, then nothing is removed' => [
                static fn (
                    DiscountVoucherConditionsTransfer $discountVoucherConditionsTransfer,
                    self $test
                ): DiscountVoucherConditionsTransfer => $discountVoucherConditionsTransfer->addCode($test->getCodes()[0]),
                false,
                0,
                static::VOUCHER_CODE_QUANTITY,
            ],
        ];
    }

    /**
     * @dataProvider provideDiscountVouchersToValidate
     */
    public function testRejectsAVoucherGenerationForANonVoucherDiscount(
        callable $resolveIdDiscount,
        bool $expectedIsSuccessful
    ): void {
        // Arrange
        $cartRuleDiscountGeneralTransfer = $this->tester->haveDiscountForCollection([
            DiscountGeneralTransfer::DISCOUNT_TYPE => DiscountConstants::TYPE_CART_RULE,
        ]);
        $voucherDiscountGeneralTransfer = $this->tester->haveDiscountForCollection([
            DiscountGeneralTransfer::DISCOUNT_TYPE => DiscountConstants::TYPE_VOUCHER,
        ]);

        $discountVoucherTransfer = (new DiscountVoucherTransfer())->setIdDiscount(
            $resolveIdDiscount($cartRuleDiscountGeneralTransfer, $voucherDiscountGeneralTransfer),
        );

        // Act
        $discountVoucherResponseTransfer = $this->createDiscountFacade()->validateDiscountVoucher($discountVoucherTransfer);

        // Assert
        $this->assertSame($expectedIsSuccessful, $discountVoucherResponseTransfer->getIsSuccessful());
        $this->assertSame($discountVoucherTransfer, $discountVoucherResponseTransfer->getDiscountVoucher());

        if ($expectedIsSuccessful) {
            $this->assertCount(0, $discountVoucherResponseTransfer->getErrors());

            return;
        }

        $errorTransfer = $discountVoucherResponseTransfer->getErrors()->offsetGet(0);
        $this->assertSame(DiscountConfig::TRANSLATION_KEY_VOUCHER_DISCOUNT_TYPE, $errorTransfer->getMessage());
        $this->assertSame(DiscountTypeDiscountVoucherValidatorRule::ENTITY_IDENTIFIER_ID_DISCOUNT, $errorTransfer->getEntityIdentifier());
    }

    /**
     * @return array<string, array{callable, bool}>
     */
    public function provideDiscountVouchersToValidate(): array
    {
        return [
            'given a voucher discount, when the voucher is validated, then it is accepted' => [
                static fn (
                    DiscountGeneralTransfer $cartRuleDiscountGeneralTransfer,
                    DiscountGeneralTransfer $voucherDiscountGeneralTransfer
                ): int => $voucherDiscountGeneralTransfer->getIdDiscountOrFail(),
                true,
            ],
            'given a cart rule discount, when the voucher is validated, then the discount type rule rejects it' => [
                static fn (
                    DiscountGeneralTransfer $cartRuleDiscountGeneralTransfer
                ): int => $cartRuleDiscountGeneralTransfer->getIdDiscountOrFail(),
                false,
            ],
            'given a discount id no row carries, when the voucher is validated, then the discount type rule rejects it' => [
                static fn (): int => self::UNKNOWN_ID_DISCOUNT,
                false,
            ],
        ];
    }

    public function testLeavesSaveVoucherCodesFreeOfTheNewDiscountTypeRule(): void
    {
        // Arrange
        $discountGeneralTransfer = $this->tester->haveDiscountForCollection([
            DiscountGeneralTransfer::DISCOUNT_TYPE => DiscountConstants::TYPE_CART_RULE,
        ]);

        $discountVoucherTransfer = (new DiscountVoucherTransfer())
            ->setIdDiscount($discountGeneralTransfer->getIdDiscountOrFail())
            ->setQuantity(1)
            ->setCustomCode(strtoupper(uniqid('RG', false)))
            ->setRandomGeneratedCodeLength(6)
            ->setMaxNumberOfUses(1);

        // Act
        $voucherCreateInfoTransfer = $this->createDiscountFacade()->saveVoucherCodes($discountVoucherTransfer);

        // Assert
        $this->assertSame(DiscountConstants::MESSAGE_TYPE_SUCCESS, $voucherCreateInfoTransfer->getType());
        $persistedCodes = $this->tester->getDiscountVoucherCodesByIdDiscount($discountGeneralTransfer->getIdDiscountOrFail());
        $this->assertCount(1, $persistedCodes);
        $this->assertSame($persistedCodes, $voucherCreateInfoTransfer->getCodes());
    }

    public function testReturnsOnlyTheCodesCreatedByTheCallWhenOlderCodesShareTheBatch(): void
    {
        // Arrange
        $this->haveDiscountVoucherFixtures();
        $discountVoucherTransfer = (new DiscountVoucherTransfer())
            ->setIdDiscount($this->idDiscount)
            ->setQuantity(1)
            ->setCustomCode(strtoupper(uniqid('RG', false)))
            ->setMaxNumberOfUses(0);

        // Act
        $voucherCreateInfoTransfer = $this->createDiscountFacade()->saveVoucherCodes($discountVoucherTransfer);

        // Assert
        $this->assertSame(DiscountConstants::MESSAGE_TYPE_SUCCESS, $voucherCreateInfoTransfer->getType());
        $this->assertSame([$discountVoucherTransfer->getCustomCode()], $voucherCreateInfoTransfer->getCodes());
        $this->assertCount(static::VOUCHER_CODE_QUANTITY + 1, $this->tester->getDiscountVoucherCodesByIdDiscount($this->idDiscount));
    }

    public function testDeletesOnlyTheGivenBatchWhenNoCodeIsListed(): void
    {
        // Arrange
        $this->haveDiscountVoucherFixtures();
        $this->createDiscountFacade()->saveVoucherCodes(
            (new DiscountVoucherTransfer())
                ->setIdDiscount($this->idDiscount)
                ->setQuantity(static::SECOND_BATCH_QUANTITY)
                ->setCustomCode(strtoupper(uniqid('SB', false)))
                ->setRandomGeneratedCodeLength(static::SECOND_BATCH_CODE_LENGTH)
                ->setMaxNumberOfUses(static::SECOND_BATCH_MAX_NUMBER_OF_USES),
        );
        $secondBatch = $this->getLatestVoucherBatch($this->idDiscount);

        // Act
        $discountVoucherCollectionResponseTransfer = $this->createDiscountFacade()->deleteDiscountVoucherCollection(
            (new DiscountVoucherCollectionDeleteCriteriaTransfer())->setDiscountVoucherConditions(
                (new DiscountVoucherConditionsTransfer())
                    ->addIdDiscount($this->idDiscount)
                    ->addVoucherBatch($secondBatch),
            ),
        );

        // Assert
        $this->assertTrue($discountVoucherCollectionResponseTransfer->getIsSuccessful());
        $this->assertSame(static::SECOND_BATCH_QUANTITY, (int)$discountVoucherCollectionResponseTransfer->getDeletedCount());
        $this->assertSame($this->codes, $this->tester->getDiscountVoucherCodesByIdDiscount($this->idDiscount));
        $this->assertCount(1, $this->tester->getDiscountVoucherCodesByIdDiscount($this->idOtherDiscount));
    }

    public function getIdDiscount(): int
    {
        return $this->idDiscount;
    }

    /**
     * @return array<int, string>
     */
    public function getCodes(): array
    {
        return $this->codes;
    }

    /**
     * @return array<int, string>
     */
    public function getOtherCodes(): array
    {
        return $this->otherCodes;
    }

    protected function haveDiscountVoucherFixtures(): void
    {
        $this->idDiscount = $this->tester
            ->haveVoucherDiscountWithGeneratedCodes(strtoupper(uniqid('DV', false)), static::VOUCHER_CODE_QUANTITY)
            ->getIdDiscountOrFail();
        $this->idOtherDiscount = $this->tester
            ->haveVoucherDiscountWithGeneratedCodes(strtoupper(uniqid('OT', false)))
            ->getIdDiscountOrFail();

        $this->codes = $this->tester->getDiscountVoucherCodesByIdDiscount($this->idDiscount);
        $this->otherCodes = $this->tester->getDiscountVoucherCodesByIdDiscount($this->idOtherDiscount);
    }

    protected function createDiscountFacade(): DiscountFacadeInterface
    {
        return $this->tester->getLocator()->discount()->facade();
    }

    protected function getLatestVoucherBatch(int $idDiscount): int
    {
        $latestVoucherBatch = 0;
        $discountVoucherCollectionTransfer = $this->createDiscountFacade()->getDiscountVoucherCollection(
            (new DiscountVoucherCriteriaTransfer())->setDiscountVoucherConditions(
                (new DiscountVoucherConditionsTransfer())->addIdDiscount($idDiscount),
            ),
        );

        foreach ($discountVoucherCollectionTransfer->getDiscountVouchers() as $discountVoucherTransfer) {
            $latestVoucherBatch = max($latestVoucherBatch, (int)$discountVoucherTransfer->getVoucherBatch());
        }

        return $latestVoucherBatch;
    }
}
