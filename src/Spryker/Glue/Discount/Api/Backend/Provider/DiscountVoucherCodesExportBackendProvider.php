<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Provider;

use Generated\Shared\Transfer\DiscountVoucherConditionsTransfer;
use Generated\Shared\Transfer\DiscountVoucherCriteriaTransfer;
use Generated\Shared\Transfer\DiscountVoucherTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractBackendProvider;
use Spryker\Glue\Discount\Api\Backend\Exception\DiscountVoucherCodesExportException;
use Spryker\Glue\Discount\Api\Backend\Reader\DiscountConfiguratorReaderInterface;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;
use Symfony\Component\HttpFoundation\Response;

class DiscountVoucherCodesExportBackendProvider extends AbstractBackendProvider
{
    protected const string URI_VARIABLE_UUID = 'uuid';

    protected const string HEADER_CONTENT_TYPE = 'Content-Type';

    protected const string HEADER_CONTENT_DISPOSITION = 'Content-Disposition';

    protected const string CONTENT_TYPE = 'text/csv; charset=UTF-8';

    protected const string CONTENT_DISPOSITION = 'attachment; filename="vouchers.csv"';

    protected const string CSV_STREAM = 'php://temp';

    protected const string CSV_STREAM_MODE = 'w+b';

    protected const string CSV_ESCAPE = '';

    protected const string VALUE_TRUE = '1';

    protected const string VALUE_FALSE = '0';

    protected const array COLUMNS = ['code', 'maxNumberOfUses', 'numberOfUses', 'isActive'];

    public function __construct(
        protected DiscountFacadeInterface $discountFacade,
        protected DiscountConfiguratorReaderInterface $discountConfiguratorReader
    ) {
    }

    protected function provideItem(): object
    {
        $idDiscount = $this->discountConfiguratorReader
            ->getDiscountConfiguratorByUuid((string)$this->getUriVariable(static::URI_VARIABLE_UUID))
            ->getDiscountGeneralOrFail()
            ->getIdDiscountOrFail();

        $discountVoucherTransfers = $this->discountFacade
            ->getDiscountVoucherCollection(
                (new DiscountVoucherCriteriaTransfer())->setDiscountVoucherConditions(
                    (new DiscountVoucherConditionsTransfer())->addIdDiscount($idDiscount),
                ),
            )
            ->getDiscountVouchers()
            ->getArrayCopy();

        $response = new Response($this->buildCsv($discountVoucherTransfers));
        $response->headers->set(static::HEADER_CONTENT_TYPE, static::CONTENT_TYPE);
        $response->headers->set(static::HEADER_CONTENT_DISPOSITION, static::CONTENT_DISPOSITION);

        return $response;
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\DiscountVoucherTransfer> $discountVoucherTransfers
     *
     * @throws \Spryker\Glue\Discount\Api\Backend\Exception\DiscountVoucherCodesExportException
     */
    protected function buildCsv(array $discountVoucherTransfers): string
    {
        $stream = fopen(static::CSV_STREAM, static::CSV_STREAM_MODE);

        if ($stream === false) {
            throw new DiscountVoucherCodesExportException(sprintf('Could not open %s for the voucher code export.', static::CSV_STREAM));
        }

        $this->writeCsvRow($stream, static::COLUMNS);

        foreach ($discountVoucherTransfers as $discountVoucherTransfer) {
            $this->writeCsvRow($stream, $this->mapDiscountVoucherToRow($discountVoucherTransfer));
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        if ($csv === false) {
            throw new DiscountVoucherCodesExportException('Could not read back the rendered voucher code export.');
        }

        return $csv;
    }

    /**
     * @param resource $stream
     * @param array<int, string|int> $row
     *
     * @throws \Spryker\Glue\Discount\Api\Backend\Exception\DiscountVoucherCodesExportException
     */
    protected function writeCsvRow($stream, array $row): void
    {
        if (fputcsv($stream, $row, escape: static::CSV_ESCAPE) === false) {
            throw new DiscountVoucherCodesExportException('Could not write a row of the voucher code export.');
        }
    }

    /**
     * @return array<int, string>
     */
    protected function mapDiscountVoucherToRow(DiscountVoucherTransfer $discountVoucherTransfer): array
    {
        return [
            (string)$discountVoucherTransfer->getCode(),
            (string)(int)$discountVoucherTransfer->getMaxNumberOfUses(),
            (string)(int)$discountVoucherTransfer->getNumberOfUses(),
            $discountVoucherTransfer->getIsActive() ? static::VALUE_TRUE : static::VALUE_FALSE,
        ];
    }
}
