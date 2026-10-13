<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Mapper;

use ArrayObject;

interface ErrorResourceMapperInterface
{
    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ErrorTransfer> $errorTransfers
     * @param array<string, string> $responseCodeByErrorMessage
     *
     * @return array<int, array{code: string, status: int, detail: string}>
     */
    public function mapErrorTransfersToErrors(ArrayObject $errorTransfers, array $responseCodeByErrorMessage): array;

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\MessageTransfer> $messageTransfers
     *
     * @return array<int, array{code: string, status: int, detail: string}>
     */
    public function mapMessageTransfersToErrors(ArrayObject $messageTransfers): array;
}
