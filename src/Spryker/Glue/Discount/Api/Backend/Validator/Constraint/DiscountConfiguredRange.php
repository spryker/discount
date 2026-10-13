<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Validator\Constraint;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class DiscountConfiguredRange extends Constraint
{
    /**
     * @uses \Spryker\Zed\Discount\DiscountConfig::getPriorityMinValue()
     * @uses \Spryker\Zed\Discount\DiscountConfig::getPriorityMaxValue()
     */
    public const string RANGE_PRIORITY = 'priority';

    /**
     * @uses \Spryker\Zed\Discount\DiscountConfig::getVoucherCodesQuantityMinValue()
     * @uses \Spryker\Zed\Discount\DiscountConfig::getVoucherCodesQuantityMaxValue()
     */
    public const string RANGE_VOUCHER_CODES_QUANTITY = 'voucherCodesQuantity';

    /**
     * @uses \Spryker\Zed\Discount\DiscountConfig::getVoucherCodeLengthMinValue()
     * @uses \Spryker\Zed\Discount\DiscountConfig::getVoucherCodeLengthMaxValue()
     */
    public const string RANGE_VOUCHER_CODE_LENGTH = 'voucherCodeLength';

    public const string NOT_IN_RANGE_ERROR = '3b0a6f2e-9c41-4d7a-8e5f-2d1c0b9a8f7e';

    public string $message = 'This value should be between {{ min }} and {{ max }}.';

    /**
     * @param array<string, mixed>|null $options
     * @param array<int, string>|null $groups
     */
    public function __construct(
        public string $range = self::RANGE_PRIORITY,
        ?string $message = null,
        ?array $groups = null,
        mixed $payload = null,
        ?array $options = null
    ) {
        parent::__construct($options, $groups, $payload);

        $this->message = $message ?? $this->message;
    }

    /**
     * @return array<int, string>
     */
    public function getRequiredOptions(): array
    {
        return [];
    }
}
