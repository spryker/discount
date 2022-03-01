<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Discount\Business\Calculator;

use ArrayObject;
use Generated\Shared\Transfer\CollectedDiscountTransfer;
use Generated\Shared\Transfer\DiscountTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Zed\Discount\Business\Distributor\DistributorInterface;
use Spryker\Zed\Discount\Business\Exception\CalculatorException;
use Spryker\Zed\Discount\Business\Exception\QueryStringException;
use Spryker\Zed\Discount\Business\Filter\CollectedDiscountItemFilterInterface;
use Spryker\Zed\Discount\Business\QueryString\SpecificationBuilderInterface;
use Spryker\Zed\Discount\Business\Sorter\CollectedDiscountSorterInterface;
use Spryker\Zed\Discount\Dependency\Facade\DiscountToMessengerInterface;
use Spryker\Zed\Discount\Dependency\Plugin\CollectorStrategyPluginInterface;
use Spryker\Zed\Discount\DiscountDependencyProvider;

class Calculator implements CalculatorInterface
{
    use LoggerTrait;

    /**
     * @var string
     */
    public const DISCOUNT_SUCCESSFULLY_APPLIED_KEY = 'discount.successfully.applied';

    /**
     * @var array
     */
    protected $calculatedDiscounts = [];

    /**
     * @var \Spryker\Zed\Discount\Business\QueryString\SpecificationBuilderInterface
     */
    protected $collectorBuilder;

    /**
     * @var array<\Spryker\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginInterface>
     */
    protected $calculatorPlugins;

    /**
     * @var array<\Spryker\Zed\DiscountExtension\Dependency\Plugin\CollectedDiscountGroupingStrategyPluginInterface>
     */
    protected $collectedDiscountGroupingPlugins;

    /**
     * @var \Spryker\Zed\Discount\Dependency\Facade\DiscountToMessengerInterface
     */
    protected $messengerFacade;

    /**
     * @var \Spryker\Zed\Discount\Business\Distributor\DistributorInterface
     */
    protected $distributor;

    /**
     * @var \Spryker\Zed\Discount\Business\Calculator\CollectorStrategyResolverInterface|null
     */
    protected $collectorStrategyResolver;

    /**
     * @var \Spryker\Zed\Discount\Business\Filter\CollectedDiscountItemFilterInterface
     */
    protected $collectedDiscountsItemFilter;

    /**
     * @var \Spryker\Zed\Discount\Business\Sorter\CollectedDiscountSorterInterface
     */
    protected $collectedDiscountSorter;

    /**
     * @param \Spryker\Zed\Discount\Business\QueryString\SpecificationBuilderInterface $collectorBuilder
     * @param \Spryker\Zed\Discount\Dependency\Facade\DiscountToMessengerInterface $messengerFacade
     * @param \Spryker\Zed\Discount\Business\Distributor\DistributorInterface $distributor
     * @param array<\Spryker\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginInterface> $calculatorPlugins
     * @param array<\Spryker\Zed\DiscountExtension\Dependency\Plugin\CollectedDiscountGroupingStrategyPluginInterface> $collectedDiscountGroupingPlugins
     * @param \Spryker\Zed\Discount\Business\Filter\CollectedDiscountItemFilterInterface $collectedDiscountsItemFilter
     * @param \Spryker\Zed\Discount\Business\Sorter\CollectedDiscountSorterInterface $collectedDiscountSorter
     */
    public function __construct(
        SpecificationBuilderInterface $collectorBuilder,
        DiscountToMessengerInterface $messengerFacade,
        DistributorInterface $distributor,
        array $calculatorPlugins,
        array $collectedDiscountGroupingPlugins,
        CollectedDiscountItemFilterInterface $collectedDiscountsItemFilter,
        CollectedDiscountSorterInterface $collectedDiscountSorter
    ) {
        $this->collectorBuilder = $collectorBuilder;
        $this->calculatorPlugins = $calculatorPlugins;
        $this->collectedDiscountGroupingPlugins = $collectedDiscountGroupingPlugins;
        $this->messengerFacade = $messengerFacade;
        $this->distributor = $distributor;
        $this->collectedDiscountsItemFilter = $collectedDiscountsItemFilter;
        $this->collectedDiscountSorter = $collectedDiscountSorter;
    }

    /**
     * @param array<\Generated\Shared\Transfer\DiscountTransfer> $discounts
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array<\Generated\Shared\Transfer\CollectedDiscountTransfer>
     */
    public function calculate(array $discounts, QuoteTransfer $quoteTransfer)
    {
        $collectedDiscountTransfers = $this->calculateDiscountAmount($discounts, $quoteTransfer);
        $collectedDiscountTransferGroups = $this->groupCollectedDiscounts($collectedDiscountTransfers);

        $collectedDiscountTransfers = [];
        foreach ($collectedDiscountTransferGroups as $collectedDiscountTransfersGroup) {
            $collectedDiscountTransfersGroup = $this->collectedDiscountSorter->sort($collectedDiscountTransfersGroup);
            $collectedDiscountTransfersGroup = $this->filterExclusiveDiscounts($collectedDiscountTransfersGroup);
            $collectedDiscountTransfers[] = $collectedDiscountTransfersGroup;
        }
        $collectedDiscountTransfers = array_merge(...$collectedDiscountTransfers);

        $this->distributeDiscountAmount($collectedDiscountTransfers);

        $collectedDiscountTransfers = $this->collectedDiscountsItemFilter->filter($collectedDiscountTransfers);

        $this->addDiscountsAppliedMessage(
            $collectedDiscountTransfers,
            $quoteTransfer->getCartRuleDiscounts(),
            $quoteTransfer->getVoucherDiscounts(),
        );

        return $collectedDiscountTransfers;
    }

    /**
     * @param array<\Generated\Shared\Transfer\DiscountTransfer> $discounts
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array<\Generated\Shared\Transfer\CollectedDiscountTransfer>
     */
    protected function calculateDiscountAmount(array $discounts, QuoteTransfer $quoteTransfer): array
    {
        $collectedDiscountTransfers = [];
        foreach ($discounts as $discountTransfer) {
            $discountableItems = $this->collectItems($quoteTransfer, $discountTransfer);

            if (count($discountableItems) === 0) {
                continue;
            }

            $calculatorPlugin = $this->getCalculatorPlugin($discountTransfer);
            $discountAmount = $calculatorPlugin->calculateDiscount($discountableItems, $discountTransfer);
            $discountTransfer->setAmount($discountAmount);

            $collectedDiscountTransfers[] = $this->createCollectedDiscountTransfer($discountTransfer, $discountableItems);
        }

        return $collectedDiscountTransfers;
    }

    /**
     * @param array<\Generated\Shared\Transfer\CollectedDiscountTransfer> $collectedDiscountTransfers
     *
     * @return array \Generated\Shared\Transfer\CollectedDiscountTransfer[][]
     */
    protected function groupCollectedDiscounts(array $collectedDiscountTransfers)
    {
        $collectedDiscountTransferGroups = [];
        foreach ($collectedDiscountTransfers as $index => $collectedDiscountTransfer) {
            foreach ($this->collectedDiscountGroupingPlugins as $collectedDiscountGroupingPlugin) {
                if ($collectedDiscountGroupingPlugin->isApplicable($collectedDiscountTransfer)) {
                    $collectedDiscountTransferGroups[$collectedDiscountGroupingPlugin->getGroupName()][] = $collectedDiscountTransfer;
                    unset($collectedDiscountTransfers[$index]);

                    break;
                }
            }
        }

        if ($collectedDiscountTransferGroups === []) {
            return [
                $collectedDiscountTransfers,
            ];
        }

        $collectedDiscountTransferGroups[] = $collectedDiscountTransfers;

        return $collectedDiscountTransferGroups;
    }

    /**
     * - Filters exclusive discounts returning an array of only one exclusive discount, or return all.
     *
     * @param array<\Generated\Shared\Transfer\CollectedDiscountTransfer> $collectedDiscountTransfers
     *
     * @return array<\Generated\Shared\Transfer\CollectedDiscountTransfer>
     */
    protected function filterExclusiveDiscounts(array $collectedDiscountTransfers): array
    {
        foreach ($collectedDiscountTransfers as $collectedDiscountTransfer) {
            if ($collectedDiscountTransfer->getDiscount()->getIsExclusive()) {
                return [$collectedDiscountTransfer];
            }
        }

        return $collectedDiscountTransfers;
    }

    /**
     * @param array<\Generated\Shared\Transfer\CollectedDiscountTransfer> $collectedDiscountTransfers
     *
     * @return void
     */
    protected function distributeDiscountAmount(array $collectedDiscountTransfers): void
    {
        foreach ($collectedDiscountTransfers as $collectedDiscountTransfer) {
            $this->distributor->distributeDiscountAmountToDiscountableItems($collectedDiscountTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\DiscountTransfer $discountTransfer
     *
     * @return void
     */
    protected function setSuccessfulDiscountAddMessage(DiscountTransfer $discountTransfer): void
    {
        if (!$discountTransfer->getAmount()) {
            return;
        }

        $messageTransfer = new MessageTransfer();
        $messageTransfer->setValue(static::DISCOUNT_SUCCESSFULLY_APPLIED_KEY);
        $messageTransfer->setParameters([
            'display_name' => $discountTransfer->getDisplayName(),
        ]);

        $this->messengerFacade->addSuccessMessage($messageTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\DiscountTransfer $discountTransfer
     *
     * @return array<\Generated\Shared\Transfer\DiscountableItemTransfer>
     */
    protected function collectItems(QuoteTransfer $quoteTransfer, DiscountTransfer $discountTransfer)
    {
        $alternativeCollectorStrategyPlugin = $this->resolveCollectorPluginStrategy($quoteTransfer, $discountTransfer);
        if ($alternativeCollectorStrategyPlugin) {
            return $alternativeCollectorStrategyPlugin->collect($discountTransfer, $quoteTransfer);
        }

        try {
            $collectorQueryString = $discountTransfer->getCollectorQueryString();

            /** @var \Spryker\Zed\Discount\Business\QueryString\Specification\CollectorSpecification\CollectorSpecificationInterface $collectorComposite */
            $collectorComposite = $this->collectorBuilder
                ->buildFromQueryString(
                    $collectorQueryString,
                );

            return $collectorComposite->collect($quoteTransfer);
        } catch (QueryStringException $exception) {
            $this->getLogger()->warning($exception->getMessage(), ['exception' => $exception]);
        }

        return [];
    }

    /**
     * @param \Generated\Shared\Transfer\DiscountTransfer $discountTransfer
     *
     * @throws \Spryker\Zed\Discount\Business\Exception\CalculatorException
     *
     * @return \Spryker\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginInterface
     */
    protected function getCalculatorPlugin(DiscountTransfer $discountTransfer)
    {
        if (!isset($this->calculatorPlugins[$discountTransfer->getCalculatorPlugin()])) {
            throw new CalculatorException(
                sprintf(
                    'Calculator plugin with name "%s" not found. Did you forget to register it in "%s"::getAvailableCalculatorPlugins',
                    $discountTransfer->getCalculatorPlugin(),
                    DiscountDependencyProvider::class,
                ),
            );
        }

        return $this->calculatorPlugins[$discountTransfer->getCalculatorPlugin()];
    }

    /**
     * @param \Generated\Shared\Transfer\DiscountTransfer $discountTransfer
     * @param array<\Generated\Shared\Transfer\DiscountableItemTransfer> $discountableItems
     *
     * @return \Generated\Shared\Transfer\CollectedDiscountTransfer
     */
    protected function createCollectedDiscountTransfer(DiscountTransfer $discountTransfer, array $discountableItems): CollectedDiscountTransfer
    {
        $calculatedDiscounts = new CollectedDiscountTransfer();
        $calculatedDiscounts->setDiscount($discountTransfer);
        $calculatedDiscounts->setDiscountableItems(new ArrayObject($discountableItems));

        return $calculatedDiscounts;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\DiscountTransfer $discountTransfer
     *
     * @return \Spryker\Zed\Discount\Dependency\Plugin\CollectorStrategyPluginInterface|null
     */
    protected function resolveCollectorPluginStrategy(QuoteTransfer $quoteTransfer, DiscountTransfer $discountTransfer): ?CollectorStrategyPluginInterface
    {
        if (!$this->collectorStrategyResolver) {
            return null;
        }

        return $this->collectorStrategyResolver->resolveCollector($discountTransfer, $quoteTransfer);
    }

    /**
     * @param \Spryker\Zed\Discount\Business\Calculator\CollectorStrategyResolverInterface $collectorStrategyResolver
     *
     * @return void
     */
    public function setCollectorStrategyResolver(CollectorStrategyResolverInterface $collectorStrategyResolver)
    {
        $this->collectorStrategyResolver = $collectorStrategyResolver;
    }

    /**
     * @param array<\Generated\Shared\Transfer\CollectedDiscountTransfer> $collectedDiscountTransferCollection
     * @param \ArrayObject<int, \Generated\Shared\Transfer\DiscountTransfer> $oldCartRuleDiscountTransferCollection
     * @param \ArrayObject<int, \Generated\Shared\Transfer\DiscountTransfer> $oldVoucherDiscountTransferCollection
     *
     * @return void
     */
    protected function addDiscountsAppliedMessage(
        array $collectedDiscountTransferCollection,
        ArrayObject $oldCartRuleDiscountTransferCollection,
        ArrayObject $oldVoucherDiscountTransferCollection
    ): void {
        $discountIds = array_merge(
            $this->getDiscountIds($oldCartRuleDiscountTransferCollection),
            $this->getDiscountIds($oldVoucherDiscountTransferCollection),
        );
        foreach ($collectedDiscountTransferCollection as $collectedDiscountTransfer) {
            if (
                !in_array($collectedDiscountTransfer->getDiscount()->getIdDiscount(), $discountIds)
                || $this->isDiscountAmountBeenChanged($collectedDiscountTransfer->getDiscount(), $oldCartRuleDiscountTransferCollection, $oldVoucherDiscountTransferCollection)
            ) {
                $this->setSuccessfulDiscountAddMessage($collectedDiscountTransfer->getDiscount());
            }
        }
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\DiscountTransfer> $discountTransferCollection
     *
     * @return array
     */
    protected function getDiscountIds(ArrayObject $discountTransferCollection): array
    {
        $discountIds = [];

        foreach ($discountTransferCollection as $discountTransfer) {
            if ($discountTransfer->getIdDiscount()) {
                $discountIds[] = $discountTransfer->getIdDiscount();
            }
        }

        return $discountIds;
    }

    /**
     * @param \Generated\Shared\Transfer\DiscountTransfer $discountTransfer
     * @param \ArrayObject<int, \Generated\Shared\Transfer\DiscountTransfer> $oldCartRuleDiscountTransferCollection
     * @param \ArrayObject<int, \Generated\Shared\Transfer\DiscountTransfer> $oldVoucherDiscountTransferCollection
     *
     * @return bool
     */
    protected function isDiscountAmountBeenChanged(
        DiscountTransfer $discountTransfer,
        ArrayObject $oldCartRuleDiscountTransferCollection,
        ArrayObject $oldVoucherDiscountTransferCollection
    ): bool {
        foreach ($oldCartRuleDiscountTransferCollection as $oldDiscountTransfer) {
            if ($oldDiscountTransfer->getAmount() !== $discountTransfer->getAmount() && $oldDiscountTransfer->getIdDiscount() === $discountTransfer->getIdDiscount()) {
                return true;
            }
        }

        foreach ($oldVoucherDiscountTransferCollection as $oldDiscountTransfer) {
            if ($oldDiscountTransfer->getAmount() !== $discountTransfer->getAmount() && $oldDiscountTransfer->getIdDiscount() === $discountTransfer->getIdDiscount()) {
                return true;
            }
        }

        return false;
    }
}
