<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\Voucher;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\DiscountVoucherTransfer;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucher;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucherQuery;
use Propel\Runtime\Connection\ConnectionInterface;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Discount\Business\Voucher\VoucherEngine;
use Spryker\Zed\Discount\Business\Voucher\VoucherEngineInterface;
use Spryker\Zed\Discount\DiscountConfig;
use Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface;
use stdClass;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group Voucher
 * @group VoucherEngineTest
 * Add your own group annotations below this line
 */
class VoucherEngineTest extends Unit
{
    public function testCreateVoucherCodeShouldPersistItemsFromTransfer(): void
    {
        $discountVoucherEntityMock = $this->createDiscountVoucherEntityMock();
        $discountVoucherEntityMock
            ->expects($this->once())
            ->method('save')
            ->willReturn(1);

        $voucherEngine = $this->createVoucherEngine(
            null,
            null,
            $discountVoucherEntityMock,
        );

        $discountVoucherTransfer = $this->createDiscountVoucherTransfer();

        $voucherEngine->createVoucherCode($discountVoucherTransfer);
    }

    public function testCreateVoucherCodesShouldGenerateListOfCodesFromGivenTransfer(): void
    {
        $discountConfigMock = $this->createDiscountConfigMock();
        $this->configureDiscountConfigMock($discountConfigMock);

        $discountVoucherEntityMock = $this->createDiscountVoucherEntityMock();
        $discountVoucherEntityMock
            ->expects($this->once())
            ->method('getVoucherBatch')
            ->willReturn(2);

        $discountVoucherEntityMock
            ->expects($this->exactly(5))
            ->method('save');

        $discountVoucherQueryMock = $this->createDiscountVoucherQueryMock();
        $discountVoucherQueryMock->method('filterByFkDiscountVoucherPool')
            ->willReturn($discountVoucherQueryMock);

        // No explicit method() for dynamic methods; __call will handle orderByVoucherBatch/findOneByCode by default
        $discountVoucherQueryMock->method('findOne')
            ->willReturn($discountVoucherEntityMock);

        $discountVoucherQueryMock
            ->method('__call')
            ->willReturnCallback(function ($name, $args) use ($discountVoucherEntityMock, $discountVoucherQueryMock) {
                if ($name === 'findOneByCode') {
                    return null;
                }

                // default behavior: for orderByVoucherBatch return $this (handled by specific expectation above)
                return $discountVoucherQueryMock;
            });

        $discountVoucherContainerMock = $this->createDiscountQueryContainerMock();
        $discountVoucherContainerMock->method('queryDiscountVoucher')
            ->willReturn($discountVoucherQueryMock);

        $connectionMock = $this->createConnectionMock();
        $connectionMock->expects($this->once())
            ->method('beginTransaction');

        $connectionMock->expects($this->once())
            ->method('commit');

        $discountVoucherContainerMock->method('getConnection')
            ->willReturn($connectionMock);

        $voucherEngine = $this->createVoucherEngine(
            $discountConfigMock,
            $discountVoucherContainerMock,
            $discountVoucherEntityMock,
        );

        $discountVoucherTransfer = $this->createDiscountVoucherTransfer();

        $voucherCreateInfoTransfer = $voucherEngine->createVoucherCodes($discountVoucherTransfer);

        $this->assertSame(DiscountConstants::MESSAGE_TYPE_SUCCESS, $voucherCreateInfoTransfer->getType());
    }

    public function testGenerateCodesWhenLengthAndCustomCodeIsNotSetShouldReturnErrorMessage(): void
    {
        $discountConfigMock = $this->createDiscountConfigMock();
        $this->configureDiscountConfigMock($discountConfigMock);

        $discountVoucherQueryMock = $this->createDiscountVoucherQueryMock();
        $discountVoucherQueryMock->expects($this->once())
            ->method('filterByFkDiscountVoucherPool')
            ->willReturn($discountVoucherQueryMock);

        // Expect __call to be invoked for orderByVoucherBatch
        $discountVoucherQueryMock->expects($this->once())
            ->method('__call')
            ->with('orderByVoucherBatch', $this->anything())
            ->willReturnSelf();

        $discountVoucherContainerMock = $this->createDiscountQueryContainerMock();
        $discountVoucherContainerMock->method('queryDiscountVoucher')
            ->willReturn($discountVoucherQueryMock);

        $connectionMock = $this->createConnectionMock();
        $connectionMock->expects($this->once())
            ->method('beginTransaction');

        $discountVoucherContainerMock->method('getConnection')
            ->willReturn($connectionMock);

        $voucherEngine = $this->createVoucherEngine(
            null,
            $discountVoucherContainerMock,
            null,
        );

        $discountVoucherTransfer = $this->createDiscountVoucherTransfer();
        $discountVoucherTransfer->setRandomGeneratedCodeLength(0);
        $discountVoucherTransfer->setCustomCode('');

        $voucherCreateInfoTransfer = $voucherEngine->createVoucherCodes($discountVoucherTransfer);

        $this->assertSame(DiscountConstants::MESSAGE_TYPE_ERROR, $voucherCreateInfoTransfer->getType());
    }

    public function testGenerateCodesWhenAllCodesCollideShouldReturnError(): void
    {
        $discountConfigMock = $this->createDiscountConfigMock();
        $this->configureDiscountConfigMock($discountConfigMock);

        $discountVoucherQueryMock = $this->createDiscountVoucherQueryMock();
        $discountVoucherQueryMock->expects($this->once())
            ->method('filterByFkDiscountVoucherPool')
            ->willReturn($discountVoucherQueryMock);

        // Configure __call to return a dummy object when findOneByCode is called
        $discountVoucherQueryMock->method('__call')
            ->willReturnCallback(function ($name, $args) use ($discountVoucherQueryMock) {
                if ($name === 'findOneByCode') {
                    return new stdClass();
                }

                if ($name === 'orderByVoucherBatch') {
                    return $discountVoucherQueryMock;
                }

                // default behavior: for orderByVoucherBatch return $this (handled by specific expectation above)
                return null;
            });

        $discountVoucherContainerMock = $this->createDiscountQueryContainerMock();
        $discountVoucherContainerMock->method('queryDiscountVoucher')
            ->willReturn($discountVoucherQueryMock);

        $connectionMock = $this->createConnectionMock();
        $connectionMock->expects($this->once())
            ->method('beginTransaction');

        $discountVoucherContainerMock->method('getConnection')
            ->willReturn($connectionMock);

        $discountConfigMock = $this->createDiscountConfigMock();
        $this->configureDiscountConfigMock($discountConfigMock);

        $voucherEngine = $this->createVoucherEngine(
            $discountConfigMock,
            $discountVoucherContainerMock,
            null,
        );

        $discountVoucherTransfer = $this->createDiscountVoucherTransfer();

        $voucherCreateInfoTransfer = $voucherEngine->createVoucherCodes($discountVoucherTransfer);

        $this->assertSame(DiscountConstants::MESSAGE_TYPE_ERROR, $voucherCreateInfoTransfer->getType());
    }

    protected function createVoucherEngine(
        ?DiscountConfig $discountConfigMock = null,
        ?DiscountQueryContainerInterface $discountQueryContainerMock = null,
        ?SpyDiscountVoucher $discountVoucherEntity = null
    ): VoucherEngineInterface {
        if (!$discountConfigMock) {
            $discountConfigMock = $this->createDiscountConfigMock();
        }

        if (!$discountQueryContainerMock) {
            $discountQueryContainerMock = $this->createDiscountQueryContainerMock();
        }

        $voucherEngineMock = $this->getMockBuilder(VoucherEngine::class)
            ->onlyMethods(['createDiscountVoucherEntity'])
            ->setConstructorArgs([
                $discountConfigMock,
                $discountQueryContainerMock,
            ])
            ->getMock();

        $voucherEngineMock->method('createDiscountVoucherEntity')
            ->willReturn($discountVoucherEntity);

        return $voucherEngineMock;
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Discount\DiscountConfig $discountConfigMock
     *
     * @return void
     */
    protected function configureDiscountConfigMock(DiscountConfig $discountConfigMock): void
    {
        $discountConfigMock
            ->method('getVoucherCodeCharacters')
            ->willReturn($this->getVoucherCodeCharacters());

        $discountConfigMock
            ->method('getVoucherPoolTemplateReplacementString')
            ->willReturn('[template]');
    }

    protected function getVoucherCodeCharacters(): array
    {
        return [
            DiscountConfig::KEY_VOUCHER_CODE_CONSONANTS => [
                'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z',
            ],
            DiscountConfig::KEY_VOUCHER_CODE_VOWELS => [
                'a', 'e', 'u',
            ],
            DiscountConfig::KEY_VOUCHER_CODE_NUMBERS => [
                1, 2, 3, 4, 5, 6, 7, 8, 9,
            ],
        ];
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Discount\DiscountConfig
     */
    protected function createDiscountConfigMock(): DiscountConfig
    {
        return $this->getMockBuilder(DiscountConfig::class)->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface
     */
    protected function createDiscountQueryContainerMock(): DiscountQueryContainerInterface
    {
        return $this->getMockBuilder(DiscountQueryContainerInterface::class)->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Orm\Zed\Discount\Persistence\SpyDiscountVoucher
     */
    protected function createDiscountVoucherEntityMock(): SpyDiscountVoucher
    {
        return $this->getMockBuilder(SpyDiscountVoucher::class)->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Orm\Zed\Discount\Persistence\SpyDiscountVoucherQuery
     */
    protected function createDiscountVoucherQueryMock(): SpyDiscountVoucherQuery
    {
        return $this->getMockBuilder(SpyDiscountVoucherQuery::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'filterByFkDiscountVoucherPool',
                'findOne',
                '__call',
            ])
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Propel\Runtime\Connection\ConnectionInterface
     */
    protected function createConnectionMock(): ConnectionInterface
    {
        return $this->getMockBuilder(ConnectionInterface::class)->getMock();
    }

    protected function createDiscountVoucherTransfer(): DiscountVoucherTransfer
    {
        $discountVoucherTransfer = new DiscountVoucherTransfer();
        $discountVoucherTransfer->setCode('test');
        $discountVoucherTransfer->setCustomCode('prefix');
        $discountVoucherTransfer->setMaxNumberOfUses(0);
        $discountVoucherTransfer->setRandomGeneratedCodeLength(5);
        $discountVoucherTransfer->setQuantity(5);

        return $discountVoucherTransfer;
    }
}
