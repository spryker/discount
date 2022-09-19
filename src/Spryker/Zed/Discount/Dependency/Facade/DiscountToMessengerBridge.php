<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Dependency\Facade;

use Generated\Shared\Transfer\MessageTransfer;

class DiscountToMessengerBridge implements DiscountToMessengerInterface
{
    /**
     * @var \Spryker\Zed\Messenger\Business\MessengerFacadeInterface
     */
    protected $messengerFacade;

    /**
     * @param \Spryker\Zed\Messenger\Business\MessengerFacadeInterface $messengerFacade
     */
    public function __construct($messengerFacade)
    {
        $this->messengerFacade = $messengerFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\MessageTransfer $message
     *
     * @return void
     */
    public function addSuccessMessage(MessageTransfer $message): void
    {
        $this->messengerFacade->addSuccessMessage($message);
    }

    /**
     * @param \Generated\Shared\Transfer\MessageTransfer $message
     *
     * @return void
     */
    public function addErrorMessage(MessageTransfer $message): void
    {
        $this->messengerFacade->addErrorMessage($message);
    }

    /**
     * @param \Generated\Shared\Transfer\MessageTransfer $message
     *
     * @return void
     */
    public function addInfoMessage(MessageTransfer $message): void
    {
        $this->messengerFacade->addInfoMessage($message);
    }
}
