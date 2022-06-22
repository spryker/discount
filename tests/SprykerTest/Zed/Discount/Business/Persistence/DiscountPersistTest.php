<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\Persistence;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\DiscountCalculatorTransfer;
use Generated\Shared\Transfer\DiscountConditionTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountGeneralTransfer;
use Generated\Shared\Transfer\DiscountVoucherTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Orm\Zed\Discount\Persistence\SpyDiscount;
use Orm\Zed\Discount\Persistence\SpyDiscountQuery;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucherPool;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Discount\Business\Exception\PersistenceException;
use Spryker\Zed\Discount\Business\Persistence\DiscountPersist;
use Spryker\Zed\Discount\Business\Persistence\DiscountStoreRelationWriter;
use Spryker\Zed\Discount\Business\Voucher\VoucherEngineInterface;
use Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group Persistence
 * @group DiscountPersistTest
 * Add your own group annotations below this line
 */
class DiscountPersistTest extends Unit
{
    /**
     * @return void
     */
    public function testSaveDiscountWithVoucherShouldSaveEntityWithVoucherPool(): void
    {
        $discountPersist = $this->createDiscountPersist();

        $discountEntityMock = $this->createDiscountEntityMock();
        $discountPersist->method('createDiscountEntity')->willReturn($discountEntityMock);

        $discountVoucherPoolEntity = $this->createVoucherPoolEntity();
        $discountPersist->method('createVoucherPoolEntity')->willReturn($discountVoucherPoolEntity);

        $discountConfiguratorTransfer = $this->createDiscountConfiguratorTransfer();

        $discountPersist->save($discountConfiguratorTransfer);
    }

    /**
     * @return void
     */
    public function testUpdateWhenDiscountExistShouldCallSaveOnDiscountEntity(): void
    {
        $discountEntityMock = $this->createDiscountEntityMock();
        $voucherPoolEntityMock = $this->createVoucherPoolEntity();

        $discountEntityMock->expects($this->exactly(1))
            ->method('getVoucherPool')
            ->willReturn($voucherPoolEntityMock);

        $discountQueryMock = $this->createDiscountQueryMock();
        $discountQueryMock->expects($this->once())
            ->method('findOneByIdDiscount')
            ->willReturn($discountEntityMock);

        $discountQueryContainerMock = $this->createDiscountQueryContainerMock();
        $discountQueryContainerMock->method('queryDiscount')->willReturn($discountQueryMock);

        $discountConfiguratorTransfer = $this->createDiscountConfiguratorTransfer();
        $discountConfiguratorTransfer->getDiscountGeneral()->setIdDiscount(1);

        $discountPersist = $this->createDiscountPersist($discountQueryContainerMock);
        $discountPersist->update($discountConfiguratorTransfer);
    }

    /**
     * @return void
     */
    public function testUpdateWhenDiscountNotFoundShouldThrowException(): void
    {
        $this->expectException(PersistenceException::class);

        $discountQueryMock = $this->createDiscountQueryMock();
        $discountQueryMock->expects($this->once())
            ->method('findOneByIdDiscount')
            ->willReturn(null);

        $discountQueryContainerMock = $this->createDiscountQueryContainerMock();
        $discountQueryContainerMock->method('queryDiscount')->willReturn($discountQueryMock);

        $discountConfiguratorTransfer = $this->createDiscountConfiguratorTransfer();
        $discountConfiguratorTransfer->getDiscountGeneral()->setIdDiscount(1);

        $discountPersist = $this->createDiscountPersist($discountQueryContainerMock);
        $discountPersist->update($discountConfiguratorTransfer);
    }

    /**
     * @return void
     */
    public function testSaveVoucherCodesShouldCallVoucherEngineForCodeGeneration(): void
    {
        $discountEntityMock = $this->createDiscountEntityMock();
        $voucherPoolEntityMock = $this->createVoucherPoolEntity();

        $discountEntityMock->expects($this->exactly(1))
            ->method('getVoucherPool')
            ->willReturn($voucherPoolEntityMock);

        $discountQueryMock = $this->createDiscountQueryMock();
        $discountQueryMock->expects($this->once())
            ->method('findOneByIdDiscount')
            ->willReturn($discountEntityMock);

        $discountQueryContainerMock = $this->createDiscountQueryContainerMock();
        $discountQueryContainerMock->method('queryDiscount')->willReturn($discountQueryMock);

        $voucherEngineMock = $this->createVoucherEngineMock();
        $voucherEngineMock->expects($this->once())
            ->method('createVoucherCodes');

        $discountPersist = $this->createDiscountPersist($discountQueryContainerMock, $voucherEngineMock);

        $discountVoucherTransfer = new DiscountVoucherTransfer();
        $discountVoucherTransfer->setIdDiscount(123);
        $discountPersist->saveVoucherCodes($discountVoucherTransfer);
    }

    /**
     * @return void
     */
    public function testSaveVoucherCodesWhenDiscountNotFoundShouldThrowException(): void
    {
        $this->expectException(PersistenceException::class);

        $discountQueryMock = $this->createDiscountQueryMock();
        $discountQueryMock->expects($this->once())
            ->method('findOneByIdDiscount')
            ->willReturn(null);

        $discountQueryContainerMock = $this->createDiscountQueryContainerMock();
        $discountQueryContainerMock->method('queryDiscount')->willReturn($discountQueryMock);

        $discountPersist = $this->createDiscountPersist($discountQueryContainerMock);

        $discountVoucherTransfer = new DiscountVoucherTransfer();
        $discountVoucherTransfer->setIdDiscount(123);
        $discountPersist->saveVoucherCodes($discountVoucherTransfer);
    }

    /**
     * @return void
     */
    public function testToggleDiscountVisibilityShouldChangeActiveFlag(): void
    {
        $discountEntityMock = $this->createDiscountEntityMock();

        $discountEntityMock->expects($this->exactly(1))
            ->method('setIsActive')
            ->with(true);

        $discountQueryMock = $this->createDiscountQueryMock();
        $discountQueryMock->expects($this->once())
            ->method('findOneByIdDiscount')
            ->willReturn($discountEntityMock);

        $discountQueryContainerMock = $this->createDiscountQueryContainerMock();
        $discountQueryContainerMock->method('queryDiscount')->willReturn($discountQueryMock);

        $discountPersist = $this->createDiscountPersist($discountQueryContainerMock);

        $discountVoucherTransfer = new DiscountVoucherTransfer();
        $discountVoucherTransfer->setIdDiscount(123);
        $discountPersist->toggleDiscountVisibility(1, true);
    }

    /**
     * @return void
     */
    public function testToggleDiscountVisibilityShouldThrowExceptionWhenDiscountNotFound(): void
    {
        $this->expectException(PersistenceException::class);

        $discountQueryMock = $this->createDiscountQueryMock();
        $discountQueryMock->expects($this->once())
            ->method('findOneByIdDiscount')
            ->willReturn(null);

        $discountQueryContainerMock = $this->createDiscountQueryContainerMock();
        $discountQueryContainerMock->method('queryDiscount')->willReturn($discountQueryMock);

        $discountPersist = $this->createDiscountPersist($discountQueryContainerMock);

        $discountVoucherTransfer = new DiscountVoucherTransfer();
        $discountVoucherTransfer->setIdDiscount(123);
        $discountPersist->toggleDiscountVisibility(1, true);
    }

    /**
     * @return \Generated\Shared\Transfer\DiscountConfiguratorTransfer
     */
    protected function createDiscountConfiguratorTransfer(): DiscountConfiguratorTransfer
    {
        $discountConfiguratorTransfer = new DiscountConfiguratorTransfer();

        $discountGeneralTransfer = new DiscountGeneralTransfer();
        $discountGeneralTransfer->setDiscountType(DiscountConstants::TYPE_VOUCHER);
        $discountGeneralTransfer->setStoreRelation(new StoreRelationTransfer());
        $discountConfiguratorTransfer->setDiscountGeneral($discountGeneralTransfer);

        $discountCalculatorTransfer = new DiscountCalculatorTransfer();
        $discountConfiguratorTransfer->setDiscountCalculator($discountCalculatorTransfer);

        $discountVoucherTransfer = new DiscountVoucherTransfer();
        $discountConfiguratorTransfer->setDiscountVoucher($discountVoucherTransfer);

        $discountConditionTransfer = new DiscountConditionTransfer();
        $discountConfiguratorTransfer->setDiscountCondition($discountConditionTransfer);

        return $discountConfiguratorTransfer;
    }

    /**
     * @param \Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface|null $discountQueryContainerMock
     * @param \Spryker\Zed\Discount\Business\Voucher\VoucherEngineInterface|null $voucherEngineMock
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Discount\Business\Persistence\DiscountPersist
     */
    protected function createDiscountPersist(
        ?DiscountQueryContainerInterface $discountQueryContainerMock = null,
        ?VoucherEngineInterface $voucherEngineMock = null
    ): DiscountPersist {
        if (!$discountQueryContainerMock) {
            $discountQueryContainerMock = $this->createDiscountQueryContainerMock();
        }

        if (!$voucherEngineMock) {
            $voucherEngineMock = $this->createVoucherEngineMock();
        }

        $discountStoreRelationWriterMock = $this->createDiscountStoreRelationWriterMock();
        $postCreatePlugins = [];
        $postUpdatePlugins = [];

        $discountPersistMock = $this->getMockBuilder(DiscountPersist::class)
            ->setMethods(['createDiscountEntity', 'createVoucherPoolEntity'])
            ->setConstructorArgs(
                [
                    $voucherEngineMock,
                    $discountQueryContainerMock,
                    $discountStoreRelationWriterMock,
                    $postCreatePlugins,
                    $postUpdatePlugins,
                ],
            )
            ->getMock();

        return $discountPersistMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface
     */
    protected function createDiscountQueryContainerMock(): DiscountQueryContainerInterface
    {
        return $this->getMockBuilder(DiscountQueryContainerInterface::class)->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Discount\Business\Persistence\DiscountStoreRelationWriter
     */
    protected function createDiscountStoreRelationWriterMock(): DiscountStoreRelationWriter
    {
        return $this->getMockBuilder(DiscountStoreRelationWriter::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Orm\Zed\Discount\Persistence\SpyDiscountQuery
     */
    protected function createDiscountQueryMock(): SpyDiscountQuery
    {
        return $this->getMockBuilder(SpyDiscountQuery::class)->setMethods(['findOneByIdDiscount'])->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Discount\Business\Voucher\VoucherEngineInterface
     */
    protected function createVoucherEngineMock(): VoucherEngineInterface
    {
        return $this->getMockBuilder(VoucherEngineInterface::class)->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Orm\Zed\Discount\Persistence\SpyDiscount
     */
    protected function createDiscountEntityMock(): SpyDiscount
    {
        $discountEntity = $this->getMockBuilder(SpyDiscount::class)->getMock();
        $discountEntity->expects($this->once())
            ->method('save')
            ->willReturn(1);

        return $discountEntity;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Orm\Zed\Discount\Persistence\SpyDiscountVoucherPool
     */
    protected function createVoucherPoolEntity(): SpyDiscountVoucherPool
    {
        $discountVoucherPoolEntity = $this->getMockBuilder(SpyDiscountVoucherPool::class)->getMock();
        $discountVoucherPoolEntity
            ->method('save')
            ->willReturn(1);

        return $discountVoucherPoolEntity;
    }
}
