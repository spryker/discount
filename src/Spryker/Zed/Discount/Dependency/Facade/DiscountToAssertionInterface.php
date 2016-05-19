<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */
namespace Spryker\Zed\Discount\Dependency\Facade;

interface DiscountToAssertionInterface
{
    /**
     * @param mixed $value
     * @param string|null $message
     *
     * @throws \Spryker\Zed\Assertion\Business\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function assertNumeric($value, $message = null);

    /**
     *
     * @param mixed $value
     * @param string|null $message
     *
     * @throws \Spryker\Zed\Assertion\Business\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function assertNumericNotZero($value, $message = null);

    /**
     * @param mixed $value
     * @param string|null $message
     *
     * @throws \Spryker\Zed\Assertion\Business\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function assertString($value, $message = null);

    /**
     * @param mixed $value
     * @param string|null $message
     *
     * @throws \Spryker\Zed\Assertion\Business\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function assertAlpha($value, $message = null);

    /**
     * @param mixed $value
     * @param string|null $message
     *
     * @throws \Spryker\Zed\Assertion\Business\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function assertAlphaNumeric($value, $message = null);
}
