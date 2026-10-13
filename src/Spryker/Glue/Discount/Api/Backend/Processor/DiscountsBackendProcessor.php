<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Processor;

use Generated\Api\Backend\DiscountsBackendResource;
use Generated\Shared\Transfer\DiscountCalculatorTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorResponseTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountRuleGroupTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractBackendProcessor;
use Spryker\Glue\Discount\Api\Backend\Exception\DiscountsBackendExceptionFactoryInterface;
use Spryker\Glue\Discount\Api\Backend\Mapper\DiscountResourceMapperInterface;
use Spryker\Glue\Discount\Api\Backend\Reader\DiscountConfiguratorReaderInterface;
use Spryker\Glue\Discount\Api\Backend\Validator\DiscountConfiguratorValidatorInterface;
use Spryker\Glue\Discount\DiscountConfig;
use Spryker\Shared\Discount\DiscountConstants;
use Spryker\Zed\Currency\Business\CurrencyFacadeInterface;
use Spryker\Zed\Discount\Business\DiscountFacadeInterface;
use Spryker\Zed\Store\Business\StoreFacadeInterface;

class DiscountsBackendProcessor extends AbstractBackendProcessor
{
    protected const string URI_VARIABLE_UUID = 'uuid';

    protected const string ATTRIBUTE_COLLECTOR = 'collector';

    protected const string ATTRIBUTE_DECISION_RULE = 'decisionRule';

    protected const string REQUEST_KEY_DATA = 'data';

    protected const string REQUEST_KEY_ATTRIBUTES = 'attributes';

    public function __construct(
        protected DiscountFacadeInterface $discountFacade,
        protected StoreFacadeInterface $storeFacade,
        protected CurrencyFacadeInterface $currencyFacade,
        protected DiscountResourceMapperInterface $discountResourceMapper,
        protected DiscountConfiguratorReaderInterface $discountConfiguratorReader,
        protected DiscountConfiguratorValidatorInterface $discountConfiguratorValidator,
        protected DiscountsBackendExceptionFactoryInterface $exceptionFactory
    ) {
    }

    protected function processPost(mixed $data): object
    {
        /** @var \Generated\Api\Backend\DiscountsBackendResource $discountsBackendResource */
        $discountsBackendResource = $data;
        $this->discountConfiguratorReader->assertDiscountUuidsAvailable();

        return $this->buildDiscountsBackendResource(
            $this->discountFacade->createDiscount(
                $this->buildValidDiscountConfigurator($discountsBackendResource, $this->createDiscountConfiguratorWithDefaults()),
            ),
        );
    }

    protected function createDiscountConfiguratorWithDefaults(): DiscountConfiguratorTransfer
    {
        return (new DiscountConfiguratorTransfer())->setDiscountCalculator(
            (new DiscountCalculatorTransfer())->setCollectorStrategyType(
                DiscountConstants::DISCOUNT_COLLECTOR_STRATEGY_QUERY_STRING,
            ),
        );
    }

    protected function processPatch(mixed $data): object
    {
        /** @var \Generated\Api\Backend\DiscountsBackendResource $discountsBackendResource */
        $discountsBackendResource = $data;

        $discountConfiguratorTransfer = $this->discountConfiguratorReader->getDiscountConfiguratorByUuid(
            (string)$this->getUriVariable(static::URI_VARIABLE_UUID),
        );

        return $this->buildDiscountsBackendResource(
            $this->discountFacade->updateDiscountWithValidation(
                $this->buildValidDiscountConfigurator($discountsBackendResource, $discountConfiguratorTransfer),
            ),
        );
    }

    protected function buildValidDiscountConfigurator(
        DiscountsBackendResource $discountsBackendResource,
        DiscountConfiguratorTransfer $discountConfiguratorTransfer
    ): DiscountConfiguratorTransfer {
        $discountConfiguratorTransfer = $this->discountResourceMapper->mapDiscountsBackendResourceToDiscountConfiguratorTransfer(
            $discountsBackendResource,
            $discountConfiguratorTransfer,
            $this->getStoreTransfers($discountsBackendResource),
            $this->getCurrencyTransfers($discountsBackendResource),
            $this->getQueryStringsByType($discountsBackendResource, $discountConfiguratorTransfer),
        );

        $this->discountConfiguratorValidator->assertIsValid($discountConfiguratorTransfer);
        $this->discountFacade->transformDiscountCalculatorAmountForPersistence(
            $discountConfiguratorTransfer->getDiscountCalculatorOrFail(),
        );

        return $discountConfiguratorTransfer;
    }

    /**
     * @return array<int, \Generated\Shared\Transfer\StoreTransfer>
     */
    protected function getStoreTransfers(
        DiscountsBackendResource $discountsBackendResource
    ): array {
        $storeNames = $discountsBackendResource->stores ?? [];

        if ($storeNames === []) {
            return [];
        }

        return $this->storeFacade->getStoreTransfersByStoreNames($storeNames);
    }

    /**
     * @return array<int, \Generated\Shared\Transfer\CurrencyTransfer>
     */
    protected function getCurrencyTransfers(
        DiscountsBackendResource $discountsBackendResource
    ): array {
        $currencyIsoCodes = [];

        foreach ($discountsBackendResource->fixedAmounts ?? [] as $fixedAmount) {
            $currencyIsoCode = $fixedAmount->getCurrencyIsoCode();

            if ($currencyIsoCode !== null) {
                $currencyIsoCodes[$currencyIsoCode] = $currencyIsoCode;
            }
        }

        if ($currencyIsoCodes === []) {
            return [];
        }

        return $this->currencyFacade->getCurrencyTransfersByIsoCodes(array_values($currencyIsoCodes));
    }

    /**
     * @return array<string, string|null>
     */
    protected function getQueryStringsByType(
        DiscountsBackendResource $discountsBackendResource,
        DiscountConfiguratorTransfer $discountConfiguratorTransfer
    ): array {
        $submittedAttributes = $this->findSubmittedAttributes();

        return [
            DiscountConfig::QUERY_STRING_TYPE_COLLECTOR => $this->findQueryString(
                DiscountConfig::QUERY_STRING_TYPE_COLLECTOR,
                static::ATTRIBUTE_COLLECTOR,
                $discountsBackendResource->collector?->toArray(),
                $discountConfiguratorTransfer->getDiscountCalculator()?->getCollectorQueryString(),
                $submittedAttributes,
            ),
            DiscountConfig::QUERY_STRING_TYPE_DECISION_RULE => $this->findQueryString(
                DiscountConfig::QUERY_STRING_TYPE_DECISION_RULE,
                static::ATTRIBUTE_DECISION_RULE,
                $discountsBackendResource->decisionRule?->toArray(),
                $discountConfiguratorTransfer->getDiscountCondition()?->getDecisionRuleQueryString(),
                $submittedAttributes,
            ),
        ];
    }

    /**
     * @param array<string, mixed>|null $ruleGroupData
     * @param array<string, mixed>|null $submittedAttributes
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function findQueryString(
        string $type,
        string $attribute,
        ?array $ruleGroupData,
        ?string $storedQueryString,
        ?array $submittedAttributes
    ): ?string {
        if ($submittedAttributes !== null && !array_key_exists($attribute, $submittedAttributes)) {
            return $storedQueryString;
        }

        if ($ruleGroupData === null) {
            return null;
        }

        $discountRuleGroupResponseTransfer = $this->discountFacade->buildQueryStringFromDiscountRuleGroup(
            $type,
            $this->discountResourceMapper->mapRuleGroupDataToDiscountRuleGroupTransfer($ruleGroupData, new DiscountRuleGroupTransfer()),
        );

        if ($discountRuleGroupResponseTransfer->getIsSuccessful() !== true) {
            throw $this->exceptionFactory->createExceptionFromDiscountRuleGroupResponse($discountRuleGroupResponseTransfer);
        }

        return $discountRuleGroupResponseTransfer->getQueryString();
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function findSubmittedAttributes(): ?array
    {
        if (!$this->hasRequest()) {
            return null;
        }

        $requestBody = json_decode((string)$this->getRequest()->getContent(), true);
        $submittedAttributes = $requestBody[static::REQUEST_KEY_DATA][static::REQUEST_KEY_ATTRIBUTES] ?? null;

        return is_array($submittedAttributes) ? $submittedAttributes : null;
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function buildDiscountsBackendResource(
        DiscountConfiguratorResponseTransfer $discountConfiguratorResponseTransfer
    ): DiscountsBackendResource {
        if ($discountConfiguratorResponseTransfer->getIsSuccessful() !== true) {
            throw $this->exceptionFactory->createExceptionFromDiscountConfiguratorResponse($discountConfiguratorResponseTransfer);
        }

        $discountConfiguratorTransfer = $this->discountConfiguratorReader->getDiscountConfiguratorByIdDiscount(
            $discountConfiguratorResponseTransfer->getDiscountConfiguratorOrFail()->getDiscountGeneralOrFail()->getIdDiscountOrFail(),
        );

        return $this->discountResourceMapper->mapDiscountConfiguratorTransferToDiscountsBackendResource(
            $discountConfiguratorTransfer,
            $this->discountConfiguratorReader->getDiscountRuleGroupTransfersByType($discountConfiguratorTransfer),
            new DiscountsBackendResource(),
        );
    }
}
