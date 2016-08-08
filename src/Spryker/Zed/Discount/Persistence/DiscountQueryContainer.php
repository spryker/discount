<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Persistence;

use Orm\Zed\Discount\Persistence\Map\SpyDiscountTableMap;
use Orm\Zed\Discount\Persistence\Map\SpyDiscountVoucherPoolTableMap;
use Orm\Zed\Discount\Persistence\Map\SpyDiscountVoucherTableMap;
use Orm\Zed\Sales\Persistence\SpySalesDiscountQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Kernel\Persistence\AbstractQueryContainer;

/**
 * @method \Spryker\Zed\Discount\Persistence\DiscountPersistenceFactory getFactory()
 */
class DiscountQueryContainer extends AbstractQueryContainer implements DiscountQueryContainerInterface
{

    const ALIAS_COL_ID_DISCOUNT = 'id_discount';
    const ALIAS_COL_AMOUNT = 'amount';
    const ALIAS_COL_TYPE = 'type';
    const ALIAS_COL_DESCRIPTION = 'description';
    const ALIAS_COL_VOUCHER_CODE = 'VoucherCode';
    const ALIAS_VOUCHER_POOL_NAME = 'voucher_pool';

    /**
     * @api
     *
     * @param string $code
     *
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountVoucherQuery
     */
    public function queryVoucher($code)
    {
        return $this->getFactory()
            ->createDiscountVoucherQuery()
            ->filterByCode($code);
    }

    /**
     * @api
     *
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountQuery
     */
    public function queryActiveAndRunningDiscounts()
    {
        $now = new \DateTime();
        $dateFormatted = $now->format('Y-m-d H:i:s');

        $query = $this->getFactory()
            ->createDiscountQuery()
            ->filterByIsActive(true)
            ->where(
                '(' . SpyDiscountTableMap::COL_VALID_FROM . ' <= ? AND ' . SpyDiscountTableMap::COL_VALID_TO . ' >= ? )',
                [
                    $dateFormatted,
                    $dateFormatted,
                ]
            )
            ->_or()
            ->where(
                '(' . SpyDiscountTableMap::COL_VALID_FROM . ' IS NULL AND ' . SpyDiscountTableMap::COL_VALID_TO . ' IS NULL )'
            );

        return $query;
    }

    /**
     * @api
     *
     * @param string[] $voucherCodes
     *
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountQuery
     */
    public function queryDiscountsBySpecifiedVouchers(array $voucherCodes = [])
    {
        $query = $this->queryActiveAndRunningDiscounts()
            ->useVoucherPoolQuery()
                ->useDiscountVoucherQuery()
                    ->withColumn(SpyDiscountVoucherTableMap::COL_CODE, self::ALIAS_COL_VOUCHER_CODE)
                    ->filterByCode(array_unique($voucherCodes), Criteria::IN)
                    ->orderByCode()
                ->endUse()
            ->endUse();

        return $query;

    }

    /**
     * @api
     *
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountQuery
     */
    public function queryActiveCartRules()
    {
        $query = $this->queryActiveAndRunningDiscounts()
            ->filterByDiscountType(DiscountConstants::TYPE_CART_RULE);

        return $query;
    }

    /**
     * @api
     *
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountVoucherPoolQuery
     */
    public function queryVoucherPool()
    {
        return $this->getFactory()
            ->createDiscountVoucherPoolQuery();
    }

    /**
     * @api
     *
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountQuery
     */
    public function queryDiscount()
    {
        return $this->getFactory()
            ->createDiscountQuery();
    }

    /**
     * @api
     *
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountVoucherQuery
     */
    public function queryDiscountVoucher()
    {
        return $this->getFactory()
            ->createDiscountVoucherQuery()
            ->joinVoucherPool()
            ->withColumn(SpyDiscountVoucherPoolTableMap::COL_NAME, self::ALIAS_VOUCHER_POOL_NAME);
    }

    /**
     * @api
     *
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountVoucherPoolQuery
     */
    public function queryDiscountVoucherPool()
    {
        return $this->getFactory()
            ->createDiscountVoucherPoolQuery();
    }

    /**
     * @api
     *
     * @param array $codes
     *
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountVoucherQuery
     */
    public function queryVoucherPoolByVoucherCodes(array $codes)
    {
        return $this->queryDiscountVoucher()
            ->joinVoucherPool()
            ->filterByCode($codes, Criteria::IN);
    }

    /**
     * @api
     *
     * @param int $idVoucherCode
     *
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountVoucherPoolQuery
     */
    public function queryVoucherCodeByIdVoucherCode($idVoucherCode)
    {
        return $this->queryDiscountVoucherPool()
            ->withColumn(SpyDiscountTableMap::COL_ID_DISCOUNT, self::ALIAS_COL_ID_DISCOUNT)
            ->withColumn(SpyDiscountTableMap::COL_AMOUNT, self::ALIAS_COL_AMOUNT)
            ->withColumn(SpyDiscountTableMap::COL_DESCRIPTION, self::ALIAS_COL_DESCRIPTION)
            ->filterByIdDiscountVoucherPool($idVoucherCode);
    }

    /**
     * @api
     *
     * @param int $idVoucherPool
     *
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountVoucherQuery
     */
    public function queryVouchersByIdVoucherPool($idVoucherPool)
    {
        return $this->getFactory()
            ->createDiscountVoucherQuery()
            ->filterByFkDiscountVoucherPool($idVoucherPool);
    }

    /**
     * @api
     *
     * @param int $idVoucher
     *
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountVoucherQuery
     */
    public function queryVoucherByIdVoucher($idVoucher)
    {
        return $this->getFactory()
            ->createDiscountVoucherQuery()
            ->filterByIdDiscountVoucher($idVoucher);
    }


    /**
     * @api
     *
     * @param string $discountName
     *
     * @return \Orm\Zed\Discount\Persistence\SpyDiscountQuery
     */
    public function queryDiscountName($discountName)
    {
        return $this->getFactory()
           ->createDiscountQuery()
           ->filterByDisplayName($discountName);
    }

    /**
     * @api
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesDiscountQuery
     */
    public function querySalesDiscount()
    {
        return new SpySalesDiscountQuery();
    }

}
