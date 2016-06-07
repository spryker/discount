<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\QueryString;

use Spryker\Zed\Discount\Business\QueryString\Comparator\Contains;
use Spryker\Zed\Discount\Business\QueryString\Comparator\DoesNotContain;
use Spryker\Zed\Discount\Business\QueryString\Comparator\Equal;
use Spryker\Zed\Discount\Business\QueryString\Comparator\IsIn;
use Spryker\Zed\Discount\Business\QueryString\Comparator\IsNotIn;
use Spryker\Zed\Discount\Business\QueryString\Comparator\Less;
use Spryker\Zed\Discount\Business\QueryString\Comparator\LessEqual;
use Spryker\Zed\Discount\Business\QueryString\Comparator\More;
use Spryker\Zed\Discount\Business\QueryString\Comparator\MoreEqual;
use Spryker\Zed\Discount\Business\QueryString\Comparator\NotEqual;

class OperatorProvider
{

    /**
     * @return \Spryker\Zed\Discount\Business\QueryString\Comparator\ComparatorInterface[]
     */
    public function createComparators()
    {
        return [
            $this->createContains(),
            $this->createDoesNotContain(),
            $this->createEqual(),
            $this->createIsIn(),
            $this->createIsNotIn(),
            $this->createLess(),
            $this->createLessEqual(),
            $this->createMore(),
            $this->createMoreEqual(),
            $this->createNotEqual()
        ];
    }

    /**
     * @return \Spryker\Zed\Discount\Business\QueryString\Comparator\Contains
     */
    protected function createContains()
    {
        return new Contains();
    }

    /**
     * @return \Spryker\Zed\Discount\Business\QueryString\Comparator\DoesNotContain
     */
    protected function createDoesNotContain()
    {
        return new DoesNotContain();
    }

    /**
     * @return \Spryker\Zed\Discount\Business\QueryString\Comparator\Equal
     */
    protected function createEqual()
    {
        return new Equal();
    }

    /**
     * @return \Spryker\Zed\Discount\Business\QueryString\Comparator\IsIn
     */
    protected function createIsIn()
    {
        return new IsIn();
    }

    /**
     * @return \Spryker\Zed\Discount\Business\QueryString\Comparator\IsNotIn
     */
    protected function createIsNotIn()
    {
        return new IsNotIn();
    }

    /**
     * @return \Spryker\Zed\Discount\Business\QueryString\Comparator\Less
     */
    protected function createLess()
    {
        return new Less();
    }

    /**
     * @return \Spryker\Zed\Discount\Business\QueryString\Comparator\LessEqual
     */
    protected function createLessEqual()
    {
        return new LessEqual();
    }

    /**
     * @return \Spryker\Zed\Discount\Business\QueryString\Comparator\More
     */
    protected function createMore()
    {
        return new More();
    }

    /**
     * @return \Spryker\Zed\Discount\Business\QueryString\Comparator\MoreEqual
     */
    protected function createMoreEqual()
    {
        return new MoreEqual();
    }

    /**
     * @return \Spryker\Zed\Discount\Business\QueryString\Comparator\NotEqual
     */
    protected function createNotEqual()
    {
        return new NotEqual();
    }

}