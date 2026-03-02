<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Dependency\Facade;

use Generated\Shared\Transfer\MessageTransfer;

interface DiscountToMessengerInterface
{
    public function addSuccessMessage(MessageTransfer $message): void;

    public function addErrorMessage(MessageTransfer $message): void;

    public function addInfoMessage(MessageTransfer $message): void;
}
