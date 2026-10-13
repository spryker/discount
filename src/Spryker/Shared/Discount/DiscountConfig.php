<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\Discount;

use Spryker\Shared\Kernel\AbstractSharedConfig;

class DiscountConfig extends AbstractSharedConfig
{
    /**
     * @api
     *
     * @var string
     */
    public const PRICE_MODE_NET = 'NET_MODE';

    /**
     * @api
     *
     * @var string
     */
    public const PRICE_MODE_GROSS = 'GROSS_MODE';

    /**
     * @api
     */
    public const string TRANSLATION_KEY_DISPLAY_NAME_NOT_UNIQUE = 'Discount with this name is already used.';

    /**
     * @api
     */
    public const string TRANSLATION_KEY_CALCULATOR_PLUGIN_INVALID = 'Calculator type "%key%" is not available.';

    /**
     * @api
     */
    public const string TRANSLATION_KEY_MONEY_AMOUNT_INVALID = 'Invalid fixed amount. At least one amount is required and each currency must belong to one of the selected stores.';

    /**
     * @api
     */
    public const string TRANSLATION_KEY_QUERY_STRING_INVALID = 'Invalid rule: %message%';

    /**
     * @api
     */
    public const string TRANSLATION_KEY_STORE_UNKNOWN = 'Store with name "%name%" does not exist.';

    /**
     * @api
     */
    public const string TRANSLATION_KEY_VOUCHER_DISCOUNT_TYPE = 'Voucher codes can only be generated for a voucher discount.';

    /**
     * @api
     */
    public const string TRANSLATION_KEY_VOUCHER_CODE_NOT_FOUND = 'Voucher code was not found for this discount.';
}
