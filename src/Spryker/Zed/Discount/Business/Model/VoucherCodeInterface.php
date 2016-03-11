<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\Model;

interface VoucherCodeInterface
{

    /**
     * @param string[] $codes
     *
     * @return bool
     */
    public function releaseUsedCodes(array $codes);

    /**
     * @param string[] $codes
     *
     * @return bool
     */
    public function useCodes(array $codes);

}
