<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Discount\Business\Persistence;

use ArrayObject;
use Codeception\Test\Unit;
use Orm\Zed\Discount\Persistence\SpyDiscount;
use Orm\Zed\Discount\Persistence\SpyDiscountQuery;
use Orm\Zed\Discount\Persistence\SpyDiscountStore;
use Orm\Zed\Store\Persistence\SpyStore;
use Spryker\Zed\Discount\Business\Persistence\DiscountConfiguratorHydrate;
use Spryker\Zed\Discount\Business\Persistence\DiscountEntityMapperInterface;
use Spryker\Zed\Discount\Business\Persistence\DiscountStoreRelationMapper;
use Spryker\Zed\Discount\Business\Persistence\DiscountStoreRelationMapperInterface;
use Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Discount
 * @group Business
 * @group Persistence
 * @group DiscountConfiguratorHydrateTest
 * Add your own group annotations below this line
 */
class DiscountConfiguratorHydrateTest extends Unit
{
    /**
     * @var string
     */
    protected const FORMAT_DATE_TIME = 'Y-m-d H:i:s';

    /**
     * @uses DiscountQueryContainerInterface::queryDiscountWithStoresByFkDiscount()
     *
     * @return void
     */
    public function testHydrateDiscountShouldFillTransferWithDataFromEntities(): void
    {
        $discountEntity = $this->createDiscountEntity();

        $discountQueryMock = $this->createDiscountQueryMock();
        $discountQueryMock->method('find')
            ->willReturn($discountQueryMock);
        $discountQueryMock->method('getFirst')
            ->willReturn($discountEntity);

        $discountQueryContainerMock = $this->createDiscountQueryContainerMock();
        $discountQueryContainerMock->method('queryDiscountWithStoresByFkDiscount')->willReturn($discountQueryMock);

        $discountConfiguratorHydrate = $this->createDiscountConfiguratorHydrate($discountQueryContainerMock);

        $hydratedDiscountConfiguration = $discountConfiguratorHydrate->getByIdDiscount(1);

        $this->assertSame(
            $discountEntity->getDecisionRuleQueryString(),
            $hydratedDiscountConfiguration->getDiscountCondition()->getDecisionRuleQueryString(),
        );

        $this->assertSame(
            $discountEntity->getAmount(),
            $hydratedDiscountConfiguration->getDiscountCalculator()->getAmount(),
        );

        $this->assertSame(
            $discountEntity->getCollectorQueryString(),
            $hydratedDiscountConfiguration->getDiscountCalculator()->getCollectorQueryString(),
        );

        $this->assertSame(
            $discountEntity->getCalculatorPlugin(),
            $hydratedDiscountConfiguration->getDiscountCalculator()->getCalculatorPlugin(),
        );

        $this->assertSame(
            $discountEntity->getDisplayName(),
            $hydratedDiscountConfiguration->getDiscountGeneral()->getDisplayName(),
        );

        $this->assertSame(
            $discountEntity->getDescription(),
            $hydratedDiscountConfiguration->getDiscountGeneral()->getDescription(),
        );

        $this->assertEquals(
            $discountEntity->getValidFrom(static::FORMAT_DATE_TIME),
            $hydratedDiscountConfiguration->getDiscountGeneral()->getValidFrom(),
        );

        $this->assertEquals(
            $discountEntity->getValidTo(static::FORMAT_DATE_TIME),
            $hydratedDiscountConfiguration->getDiscountGeneral()->getValidTo(),
        );

        $this->assertSame(
            $discountEntity->getIsActive(),
            $hydratedDiscountConfiguration->getDiscountGeneral()->getIsActive(),
        );

        $this->assertSame(
            $discountEntity->getIsExclusive(),
            $hydratedDiscountConfiguration->getDiscountGeneral()->getIsExclusive(),
        );

        $this->assertSame(
            $discountEntity->getFkDiscountVoucherPool(),
            $hydratedDiscountConfiguration->getDiscountVoucher()->getFkDiscountVoucherPool(),
        );

        $this->assertSame(
            $discountEntity->getSpyDiscountStores()->getFirst()->getSpyStore()->getIdStore(),
            $hydratedDiscountConfiguration->getDiscountGeneral()->getStoreRelation()->getStores()[0]->getIdStore(),
        );

        $this->assertSame(
            $discountEntity->getSpyDiscountStores()->getFirst()->getSpyStore()->getName(),
            $hydratedDiscountConfiguration->getDiscountGeneral()->getStoreRelation()->getStores()[0]->getName(),
        );

        $this->assertSame(
            $discountEntity->getSpyDiscountStores()->count(),
            $hydratedDiscountConfiguration->getDiscountGeneral()->getStoreRelation()->getStores()->count(),
        );

        $this->assertSame(
            $discountEntity->getSpyDiscountStores()->count(),
            count($hydratedDiscountConfiguration->getDiscountGeneral()->getStoreRelation()->getIdStores()),
        );
    }

    /**
     * @param \Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface|null $discountQueryContainerMock
     * @param \Spryker\Zed\Discount\Business\Persistence\DiscountEntityMapperInterface|null $discountEntityMapperMock
     *
     * @return \Spryker\Zed\Discount\Business\Persistence\DiscountConfiguratorHydrate
     */
    protected function createDiscountConfiguratorHydrate(
        ?DiscountQueryContainerInterface $discountQueryContainerMock = null,
        ?DiscountEntityMapperInterface $discountEntityMapperMock = null
    ): DiscountConfiguratorHydrate {
        if (!$discountQueryContainerMock) {
            $discountQueryContainerMock = $this->createDiscountQueryContainerMock();
        }

        if (!$discountEntityMapperMock) {
            $discountEntityMapperMock = $this->createEntityMapperMock();
            $discountEntityMapperMock->method('getMoneyValueCollectionForEntity')
                ->willReturn(new ArrayObject());
        }

        $discountStoreRelationMapper = $this->createDiscountStoreRelationMapper();
        $discountConfigurationExpanderPlugins = [];

        return new DiscountConfiguratorHydrate(
            $discountQueryContainerMock,
            $discountEntityMapperMock,
            $discountStoreRelationMapper,
            $discountConfigurationExpanderPlugins,
        );
    }

    /**
     * @return \Orm\Zed\Discount\Persistence\SpyDiscount
     */
    protected function createDiscountEntity(): SpyDiscount
    {
        $discountEntity = new SpyDiscount();
        $discountEntity->setAmount(10)
            ->setDecisionRuleQueryString('decisionRule string')
            ->setDisplayName('display name')
            ->setDescription('description')
            ->setCollectorQueryString('collector query string')
            ->setCalculatorPlugin('Calculator plugin')
            ->setValidFrom('2001-01-01')
            ->setValidTo('2001-01-01')
            ->setIsActive(true)
            ->setFkDiscountVoucherPool(1)
            ->setIsExclusive(true)
            ->setMinimumItemAmount(1);

        $discountEntity->addSpyDiscountStore(
            (new SpyDiscountStore())
                ->setSpyStore(
                    (new SpyStore())
                        ->setIdStore(1)
                        ->setName('DE'),
                ),
        );
        $discountEntity->addSpyDiscountStore(
            (new SpyDiscountStore())
                ->setSpyStore(
                    (new SpyStore())
                        ->setIdStore(2)
                        ->setName('AT'),
                ),
        );

        return $discountEntity;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface
     */
    protected function createDiscountQueryContainerMock(): DiscountQueryContainerInterface
    {
        return $this->getMockBuilder(DiscountQueryContainerInterface::class)->getMock();
    }

    /**
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountQuery|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createDiscountQueryMock(): SpyDiscountQuery
    {
        return $this->getMockBuilder(SpyDiscountQuery::class)->setMethods(['find', 'getFirst'])->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Discount\Business\Persistence\DiscountEntityMapperInterface
     */
    protected function createEntityMapperMock(): DiscountEntityMapperInterface
    {
        return $this->getMockBuilder(DiscountEntityMapperInterface::class)->getMock();
    }

    /**
     * @return \Spryker\Zed\Discount\Business\Persistence\DiscountStoreRelationMapperInterface
     */
    protected function createDiscountStoreRelationMapper(): DiscountStoreRelationMapperInterface
    {
        return new DiscountStoreRelationMapper();
    }
}
