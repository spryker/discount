<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Communication\Form\Encoder;

class FormFieldEncoder implements FormFieldEncoderInterface
{
    public function decode(string $encodedValue): string
    {
        return urldecode((string)base64_decode($encodedValue, true));
    }
}
