<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\QueryString;

use Spryker\Zed\Discount\Business\Exception\QueryStringException;

class Tokenizer implements TokenizerInterface
{
    /**
     * @var string
     */
    public const STRING_TO_TOKENS_REGEXP = '((\(|\)|["\'].*?["\'])|\s+)';

    /**
     * @param string $queryString
     *
     * @throws \Spryker\Zed\Discount\Business\Exception\QueryStringException
     *
     * @return array<string>
     */
    public function tokenizeQueryString($queryString)
    {
        /** @var array<string> $tokens */
        $tokens = preg_split(
            static::STRING_TO_TOKENS_REGEXP,
            $queryString,
            -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE,
        );

        $tokens = array_map('trim', $tokens);

        if (count($tokens) === 0) {
            throw new QueryStringException('Could not tokenize query string.');
        }

        return $tokens;
    }
}
