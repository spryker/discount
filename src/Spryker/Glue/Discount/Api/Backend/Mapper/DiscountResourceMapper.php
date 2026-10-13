<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\Discount\Api\Backend\Mapper;

use ArrayObject;
use Generated\Api\Backend\Discounts\DiscountsCollectorBackendObject;
use Generated\Api\Backend\Discounts\DiscountsDecisionRuleBackendObject;
use Generated\Api\Backend\Discounts\DiscountsFixedAmountsBackendObject;
use Generated\Api\Backend\DiscountsBackendResource;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\DiscountCalculatorTransfer;
use Generated\Shared\Transfer\DiscountConditionTransfer;
use Generated\Shared\Transfer\DiscountConfiguratorTransfer;
use Generated\Shared\Transfer\DiscountGeneralTransfer;
use Generated\Shared\Transfer\DiscountRuleGroupTransfer;
use Generated\Shared\Transfer\DiscountRuleTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Glue\Discount\DiscountConfig;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;

class DiscountResourceMapper implements DiscountResourceMapperInterface
{
    protected const string RULE_GROUP_KEY_CONDITION = 'condition';

    protected const string RULE_GROUP_KEY_RULES = 'rules';

    protected const string RULE_GROUP_KEY_GROUPS = 'groups';

    protected const string RULE_KEY_FIELD = 'field';

    protected const string RULE_KEY_ATTRIBUTE = 'attribute';

    protected const string RULE_KEY_OPERATOR = 'operator';

    protected const string RULE_KEY_VALUE = 'value';

    /**
     * @var non-empty-string
     */
    protected const string TRANSFER_PATH_SEPARATOR = '.';

    public function __construct(protected DiscountConfig $discountConfig)
    {
    }

    public function mapDiscountConfiguratorTransferToDiscountsBackendResource(
        DiscountConfiguratorTransfer $discountConfiguratorTransfer,
        array $discountRuleGroupTransfersByType,
        DiscountsBackendResource $discountsBackendResource
    ): DiscountsBackendResource {
        $discountGeneralTransfer = $discountConfiguratorTransfer->getDiscountGeneral() ?? new DiscountGeneralTransfer();
        $discountCalculatorTransfer = $discountConfiguratorTransfer->getDiscountCalculator() ?? new DiscountCalculatorTransfer();
        $discountConditionTransfer = $discountConfiguratorTransfer->getDiscountCondition();
        $amount = $discountCalculatorTransfer->getAmount();
        $collectorRuleGroupTransfer = $discountRuleGroupTransfersByType[DiscountConfig::QUERY_STRING_TYPE_COLLECTOR] ?? null;
        $decisionRuleGroupTransfer = $discountRuleGroupTransfersByType[DiscountConfig::QUERY_STRING_TYPE_DECISION_RULE] ?? null;

        $discountsBackendResource->uuid = $discountGeneralTransfer->getUuid();
        $discountsBackendResource->displayName = $discountGeneralTransfer->getDisplayName();
        $discountsBackendResource->description = $discountGeneralTransfer->getDescription();
        $discountsBackendResource->discountType = $discountGeneralTransfer->getDiscountType();
        $discountsBackendResource->isActive = $discountGeneralTransfer->getIsActive();
        $discountsBackendResource->isExclusive = $discountGeneralTransfer->getIsExclusive();
        $discountsBackendResource->validFrom = $discountGeneralTransfer->getValidFrom();
        $discountsBackendResource->validTo = $discountGeneralTransfer->getValidTo();
        $discountsBackendResource->calculatorType = $this->mapCalculatorPluginToCalculatorType($discountCalculatorTransfer->getCalculatorPlugin());
        $discountsBackendResource->percentage = $amount === null ? null : (float)$amount;
        $discountsBackendResource->fixedAmounts = $this->mapMoneyValuesToFixedAmounts($discountCalculatorTransfer);
        $discountsBackendResource->collectorStrategyType = $discountCalculatorTransfer->getCollectorStrategyType();
        $discountsBackendResource->collector = $collectorRuleGroupTransfer === null
            ? null
            : DiscountsCollectorBackendObject::fromArray($this->mapDiscountRuleGroupToRuleGroupData($collectorRuleGroupTransfer));
        $discountsBackendResource->decisionRule = $decisionRuleGroupTransfer === null
            ? null
            : DiscountsDecisionRuleBackendObject::fromArray($this->mapDiscountRuleGroupToRuleGroupData($decisionRuleGroupTransfer));
        $discountsBackendResource->collectorQueryString = $discountCalculatorTransfer->getCollectorQueryString();
        $discountsBackendResource->decisionRuleQueryString = $discountConditionTransfer?->getDecisionRuleQueryString();
        $discountsBackendResource->minimumItemQuantity = $discountConditionTransfer?->getMinimumItemAmount();
        $discountsBackendResource->priority = $discountGeneralTransfer->getPriority();
        $discountsBackendResource->stores = $this->mapStoreRelationToStoreNames($discountGeneralTransfer);
        $discountsBackendResource->createdAt = $discountGeneralTransfer->getCreatedAt();

        return $this->mapContributedTransfersToAttributes($discountConfiguratorTransfer, $discountsBackendResource);
    }

    public function mapDiscountsBackendResourceToDiscountConfiguratorTransfer(
        DiscountsBackendResource $discountsBackendResource,
        DiscountConfiguratorTransfer $discountConfiguratorTransfer,
        array $storeTransfers,
        array $currencyTransfers,
        array $queryStringsByType
    ): DiscountConfiguratorTransfer {
        $discountConfiguratorTransfer
            ->setDiscountGeneral(
                $this->mapDiscountGeneral($discountsBackendResource, $discountConfiguratorTransfer, $storeTransfers),
            )
            ->setDiscountCalculator(
                $this->mapDiscountCalculator($discountsBackendResource, $discountConfiguratorTransfer, $currencyTransfers, $queryStringsByType),
            )
            ->setDiscountCondition(
                $this->mapDiscountCondition($discountsBackendResource, $discountConfiguratorTransfer, $queryStringsByType),
            );

        return $this->mapContributedAttributesToTransfers($discountsBackendResource, $discountConfiguratorTransfer);
    }

    public function mapRuleGroupDataToDiscountRuleGroupTransfer(
        array $ruleGroupData,
        DiscountRuleGroupTransfer $discountRuleGroupTransfer
    ): DiscountRuleGroupTransfer {
        $discountRuleGroupTransfer->setCondition($this->findStringValue($ruleGroupData, static::RULE_GROUP_KEY_CONDITION));
        $this->addRules($ruleGroupData, $discountRuleGroupTransfer);

        return $this->addNestedRuleGroups($ruleGroupData, $discountRuleGroupTransfer);
    }

    /**
     * @param array<string, mixed> $ruleGroupData
     */
    protected function addRules(
        array $ruleGroupData,
        DiscountRuleGroupTransfer $discountRuleGroupTransfer
    ): DiscountRuleGroupTransfer {
        $rulesData = $ruleGroupData[static::RULE_GROUP_KEY_RULES] ?? null;

        if (!is_array($rulesData) || $rulesData === []) {
            return $discountRuleGroupTransfer;
        }

        foreach ($rulesData as $ruleData) {
            /** @var array<string, mixed> $ruleData */
            $discountRuleGroupTransfer->addRule(
                (new DiscountRuleTransfer())
                    ->setField($this->findStringValue($ruleData, static::RULE_KEY_FIELD))
                    ->setAttribute($this->findStringValue($ruleData, static::RULE_KEY_ATTRIBUTE))
                    ->setOperator($this->findStringValue($ruleData, static::RULE_KEY_OPERATOR))
                    ->setValue($this->findStringValue($ruleData, static::RULE_KEY_VALUE)),
            );
        }

        return $discountRuleGroupTransfer;
    }

    /**
     * @param array<string, mixed> $ruleGroupData
     */
    protected function addNestedRuleGroups(
        array $ruleGroupData,
        DiscountRuleGroupTransfer $discountRuleGroupTransfer
    ): DiscountRuleGroupTransfer {
        $nestedRuleGroupsData = $ruleGroupData[static::RULE_GROUP_KEY_GROUPS] ?? null;

        if (!is_array($nestedRuleGroupsData) || $nestedRuleGroupsData === []) {
            return $discountRuleGroupTransfer;
        }

        foreach ($nestedRuleGroupsData as $nestedRuleGroupData) {
            /** @var array<string, mixed> $nestedRuleGroupData */
            $discountRuleGroupTransfer->addGroup(
                $this->mapRuleGroupDataToDiscountRuleGroupTransfer($nestedRuleGroupData, new DiscountRuleGroupTransfer()),
            );
        }

        return $discountRuleGroupTransfer;
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\StoreTransfer> $storeTransfers
     */
    protected function mapDiscountGeneral(
        DiscountsBackendResource $discountsBackendResource,
        DiscountConfiguratorTransfer $discountConfiguratorTransfer,
        array $storeTransfers
    ): DiscountGeneralTransfer {
        $discountGeneralTransfer = $discountConfiguratorTransfer->getDiscountGeneral() ?? new DiscountGeneralTransfer();

        $discountGeneralTransfer->fromArray([
            DiscountGeneralTransfer::DISPLAY_NAME => $discountsBackendResource->displayName,
            DiscountGeneralTransfer::DESCRIPTION => $discountsBackendResource->description,
            DiscountGeneralTransfer::DISCOUNT_TYPE => $discountsBackendResource->discountType,
            DiscountGeneralTransfer::IS_ACTIVE => $discountsBackendResource->isActive,
            DiscountGeneralTransfer::IS_EXCLUSIVE => $discountsBackendResource->isExclusive,
            DiscountGeneralTransfer::VALID_FROM => $discountsBackendResource->validFrom,
            DiscountGeneralTransfer::VALID_TO => $discountsBackendResource->validTo,
            DiscountGeneralTransfer::PRIORITY => $discountsBackendResource->priority,
        ], true);

        return $discountGeneralTransfer->setStoreRelation(
            $this->mapStoreNamesToStoreRelation($discountsBackendResource->stores, $discountGeneralTransfer, $storeTransfers),
        );
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\CurrencyTransfer> $currencyTransfers
     * @param array<string, string|null> $queryStringsByType
     */
    protected function mapDiscountCalculator(
        DiscountsBackendResource $discountsBackendResource,
        DiscountConfiguratorTransfer $discountConfiguratorTransfer,
        array $currencyTransfers,
        array $queryStringsByType
    ): DiscountCalculatorTransfer {
        $discountCalculatorTransfer = $discountConfiguratorTransfer->getDiscountCalculator() ?? new DiscountCalculatorTransfer();

        $discountCalculatorTransfer->fromArray([
            DiscountCalculatorTransfer::CALCULATOR_PLUGIN => $this->mapCalculatorTypeToCalculatorPlugin($discountsBackendResource->calculatorType),
            DiscountCalculatorTransfer::AMOUNT => $discountsBackendResource->percentage === null ? null : (string)$discountsBackendResource->percentage,
            DiscountCalculatorTransfer::COLLECTOR_STRATEGY_TYPE => $discountsBackendResource->collectorStrategyType,
            DiscountCalculatorTransfer::COLLECTOR_QUERY_STRING => $queryStringsByType[DiscountConfig::QUERY_STRING_TYPE_COLLECTOR] ?? null,
        ], true);

        return $discountCalculatorTransfer->setMoneyValueCollection(
            $this->mapFixedAmountsToMoneyValueTransfers($discountsBackendResource->fixedAmounts ?? [], $currencyTransfers),
        );
    }

    /**
     * @param array<string, string|null> $queryStringsByType
     */
    protected function mapDiscountCondition(
        DiscountsBackendResource $discountsBackendResource,
        DiscountConfiguratorTransfer $discountConfiguratorTransfer,
        array $queryStringsByType
    ): DiscountConditionTransfer {
        $discountConditionTransfer = $discountConfiguratorTransfer->getDiscountCondition() ?? new DiscountConditionTransfer();

        return $discountConditionTransfer->fromArray([
            DiscountConditionTransfer::MINIMUM_ITEM_AMOUNT => $discountsBackendResource->minimumItemQuantity,
            DiscountConditionTransfer::DECISION_RULE_QUERY_STRING => $queryStringsByType[DiscountConfig::QUERY_STRING_TYPE_DECISION_RULE] ?? null,
        ], true);
    }

    protected function mapCalculatorPluginToCalculatorType(?string $calculatorPlugin): ?string
    {
        if ($calculatorPlugin === null) {
            return null;
        }

        $calculatorType = array_search($calculatorPlugin, $this->discountConfig->getCalculatorPluginKeyByCalculatorType(), true);

        return $calculatorType === false ? $calculatorPlugin : $calculatorType;
    }

    protected function mapCalculatorTypeToCalculatorPlugin(?string $calculatorType): ?string
    {
        if ($calculatorType === null) {
            return null;
        }

        return $this->discountConfig->getCalculatorPluginKeyByCalculatorType()[$calculatorType] ?? $calculatorType;
    }

    protected function mapContributedTransfersToAttributes(
        DiscountConfiguratorTransfer $discountConfiguratorTransfer,
        DiscountsBackendResource $discountsBackendResource
    ): DiscountsBackendResource {
        foreach ($this->discountConfig->getTransferPathByAttribute() as $attribute => $transferPath) {
            $contributedTransfer = $this->findTransferByPath($discountConfiguratorTransfer, $transferPath);

            if ($contributedTransfer === null) {
                continue;
            }

            $discountsBackendResource->{$attribute} = DiscountsBackendResource::fromArray([
                $attribute => $contributedTransfer->toArray(true, true),
            ])->{$attribute};
        }

        return $discountsBackendResource;
    }

    protected function mapContributedAttributesToTransfers(
        DiscountsBackendResource $discountsBackendResource,
        DiscountConfiguratorTransfer $discountConfiguratorTransfer
    ): DiscountConfiguratorTransfer {
        foreach ($this->discountConfig->getTransferPathByAttribute() as $attribute => $transferPath) {
            $contributedObject = $discountsBackendResource->{$attribute};

            if ($contributedObject === null) {
                continue;
            }

            $this->getTransferByPath($discountConfiguratorTransfer, $transferPath)
                ->fromArray($this->filterSubmittedProperties($contributedObject->toArray()), true);
        }

        return $discountConfiguratorTransfer;
    }

    protected function findTransferByPath(AbstractTransfer $transfer, string $transferPath): ?AbstractTransfer
    {
        foreach (explode(static::TRANSFER_PATH_SEPARATOR, $transferPath) as $propertyName) {
            $transfer = $transfer->{$this->getGetterMethod($propertyName)}();

            if ($transfer === null) {
                return null;
            }
        }

        return $transfer;
    }

    protected function getTransferByPath(AbstractTransfer $transfer, string $transferPath): AbstractTransfer
    {
        foreach (explode(static::TRANSFER_PATH_SEPARATOR, $transferPath) as $propertyName) {
            $getterMethod = $this->getGetterMethod($propertyName);

            if ($transfer->{$getterMethod}() === null) {
                $transfer->fromArray([$propertyName => []], true);
            }

            $transfer = $transfer->{$getterMethod}();
        }

        return $transfer;
    }

    protected function getGetterMethod(string $propertyName): string
    {
        return sprintf('get%s', ucfirst($propertyName));
    }

    /**
     * @param array<string, mixed> $properties
     *
     * @return array<string, mixed>
     */
    protected function filterSubmittedProperties(array $properties): array
    {
        return array_filter($properties, static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @return array<int, \Generated\Api\Backend\Discounts\DiscountsFixedAmountsBackendObject>
     */
    protected function mapMoneyValuesToFixedAmounts(
        DiscountCalculatorTransfer $discountCalculatorTransfer
    ): array {
        $fixedAmounts = [];

        foreach ($discountCalculatorTransfer->getMoneyValueCollection() as $moneyValueTransfer) {
            $fixedAmount = new DiscountsFixedAmountsBackendObject();
            $fixedAmount->currencyIsoCode = $moneyValueTransfer->getCurrency()?->getCode();
            $fixedAmount->netAmount = $moneyValueTransfer->getNetAmount();
            $fixedAmount->grossAmount = $moneyValueTransfer->getGrossAmount();

            $fixedAmounts[] = $fixedAmount;
        }

        return $fixedAmounts;
    }

    /**
     * @return array<int, string>
     */
    protected function mapStoreRelationToStoreNames(
        DiscountGeneralTransfer $discountGeneralTransfer
    ): array {
        $storeNames = [];

        foreach ($discountGeneralTransfer->getStoreRelation()?->getStores() ?? [] as $storeTransfer) {
            $storeNames[] = $storeTransfer->getNameOrFail();
        }

        return $storeNames;
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapDiscountRuleGroupToRuleGroupData(
        DiscountRuleGroupTransfer $discountRuleGroupTransfer
    ): array {
        $rules = [];
        $groups = [];

        foreach ($discountRuleGroupTransfer->getRules() as $discountRuleTransfer) {
            $rules[] = $this->mapDiscountRuleToRuleData($discountRuleTransfer);
        }

        foreach ($discountRuleGroupTransfer->getGroups() as $nestedDiscountRuleGroupTransfer) {
            $groups[] = $this->mapDiscountRuleGroupToRuleGroupData($nestedDiscountRuleGroupTransfer);
        }

        return [
            static::RULE_GROUP_KEY_CONDITION => $discountRuleGroupTransfer->getCondition(),
            static::RULE_GROUP_KEY_RULES => $rules,
            static::RULE_GROUP_KEY_GROUPS => $groups,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapDiscountRuleToRuleData(
        DiscountRuleTransfer $discountRuleTransfer
    ): array {
        return [
            static::RULE_KEY_FIELD => $discountRuleTransfer->getField(),
            static::RULE_KEY_ATTRIBUTE => $discountRuleTransfer->getAttribute(),
            static::RULE_KEY_OPERATOR => $discountRuleTransfer->getOperator(),
            static::RULE_KEY_VALUE => $discountRuleTransfer->getValue(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function findStringValue(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return $value === null ? null : (string)$value;
    }

    /**
     * @param array<int, string>|null $storeNames
     * @param array<int, \Generated\Shared\Transfer\StoreTransfer> $storeTransfers
     */
    protected function mapStoreNamesToStoreRelation(
        ?array $storeNames,
        DiscountGeneralTransfer $discountGeneralTransfer,
        array $storeTransfers
    ): StoreRelationTransfer {
        $storeRelationTransfer = $discountGeneralTransfer->getStoreRelation()
            ?? (new StoreRelationTransfer())->setIdEntity($discountGeneralTransfer->getIdDiscount());

        $storeIdsIndexedByStoreName = $this->getStoreIdsIndexedByStoreName($storeTransfers);
        $relatedStoreTransfers = new ArrayObject();
        $storeIds = [];

        foreach ($storeNames ?? [] as $storeName) {
            $idStore = $storeIdsIndexedByStoreName[$storeName] ?? null;
            $relatedStoreTransfers->append((new StoreTransfer())->setName($storeName)->setIdStore($idStore));

            if ($idStore !== null) {
                $storeIds[] = $idStore;
            }
        }

        return $storeRelationTransfer->setStores($relatedStoreTransfers)->setIdStores($storeIds);
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\StoreTransfer> $storeTransfers
     *
     * @return array<string, int>
     */
    protected function getStoreIdsIndexedByStoreName(
        array $storeTransfers
    ): array {
        $storeIdsIndexedByStoreName = [];

        foreach ($storeTransfers as $storeTransfer) {
            $storeIdsIndexedByStoreName[$storeTransfer->getNameOrFail()] = $storeTransfer->getIdStoreOrFail();
        }

        return $storeIdsIndexedByStoreName;
    }

    /**
     * @param array<int, \Generated\Api\Backend\Discounts\DiscountsFixedAmountsBackendObject> $fixedAmounts
     * @param array<int, \Generated\Shared\Transfer\CurrencyTransfer> $currencyTransfers
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\MoneyValueTransfer>
     */
    protected function mapFixedAmountsToMoneyValueTransfers(
        array $fixedAmounts,
        array $currencyTransfers
    ): ArrayObject {
        $currencyIdsIndexedByIsoCode = $this->getCurrencyIdsIndexedByIsoCode($currencyTransfers);
        $moneyValueTransfers = new ArrayObject();

        foreach ($fixedAmounts as $fixedAmount) {
            $currencyIsoCode = $fixedAmount->getCurrencyIsoCode();

            $moneyValueTransfers->append(
                (new MoneyValueTransfer())
                    ->setCurrency((new CurrencyTransfer())->setCode($currencyIsoCode))
                    ->setFkCurrency($currencyIsoCode === null ? null : ($currencyIdsIndexedByIsoCode[$currencyIsoCode] ?? null))
                    ->setNetAmount($fixedAmount->getNetAmount())
                    ->setGrossAmount($fixedAmount->getGrossAmount()),
            );
        }

        return $moneyValueTransfers;
    }

    /**
     * @param array<int, \Generated\Shared\Transfer\CurrencyTransfer> $currencyTransfers
     *
     * @return array<string, int>
     */
    protected function getCurrencyIdsIndexedByIsoCode(
        array $currencyTransfers
    ): array {
        $currencyIdsIndexedByIsoCode = [];

        foreach ($currencyTransfers as $currencyTransfer) {
            $currencyIdsIndexedByIsoCode[$currencyTransfer->getCodeOrFail()] = $currencyTransfer->getIdCurrencyOrFail();
        }

        return $currencyIdsIndexedByIsoCode;
    }
}
