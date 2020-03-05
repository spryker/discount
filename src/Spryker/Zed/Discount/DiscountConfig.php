<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount;

use Spryker\Zed\Kernel\AbstractBundleConfig;

class DiscountConfig extends AbstractBundleConfig
{
    public const DEFAULT_VOUCHER_CODE_LENGTH = 6;
    public const DEFAULT_MINIMUM_ITEM_AMOUNT = 1;

    public const KEY_VOUCHER_CODE_CONSONANTS = 'consonants';
    public const KEY_VOUCHER_CODE_VOWELS = 'vowels';
    public const KEY_VOUCHER_CODE_NUMBERS = 'numbers';

    protected const REDIRECT_URL_DEFAULT = '/discount/index/list';

    /**
     * @api
     *
     * @return int
     */
    public function getVoucherCodeLength()
    {
        return static::DEFAULT_VOUCHER_CODE_LENGTH;
    }

    /**
     * @api
     *
     * @return array
     */
    public function getVoucherCodeCharacters()
    {
        return [
            self::KEY_VOUCHER_CODE_CONSONANTS => [
                'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z',
            ],
            self::KEY_VOUCHER_CODE_VOWELS => [
                'a', 'e', 'u',
            ],
            self::KEY_VOUCHER_CODE_NUMBERS => [
                1, 2, 3, 4, 5, 6, 7, 8, 9,
            ],
        ];
    }

    /**
     * @api
     *
     * @return int
     */
    public function getAllowedCodeCharactersLength()
    {
        $charactersLength = array_reduce($this->getVoucherCodeCharacters(), function ($length, $items) {
            $length += count($items);

            return $length;
        });

        return $charactersLength;
    }

    /**
     * @api
     *
     * @return string
     */
    public function getVoucherPoolTemplateReplacementString()
    {
        return '[code]';
    }

    /**
     * @api
     *
     * @return string
     */
    public function getDefaultRedirectUrl(): string
    {
        return static::REDIRECT_URL_DEFAULT;
    }
}
