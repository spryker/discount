<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Discount\Business;

use Generated\Shared\Transfer\DiscountCollectorTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Generated\Shared\Transfer\VoucherCreateInfoTransfer;
use Generated\Shared\Transfer\CartRuleTransfer;
use Generated\Shared\Transfer\DiscountTransfer;
use Generated\Shared\Transfer\DecisionRuleTransfer;
use Generated\Shared\Transfer\VoucherCodesTransfer;
use Generated\Shared\Transfer\VoucherTransfer;
use Generated\Shared\Transfer\VoucherPoolTransfer;
use Generated\Shared\Transfer\VoucherPoolCategoryTransfer;
use Spryker\Zed\Calculation\Business\Model\CalculableInterface;
use Spryker\Zed\Discount\Dependency\Facade\DiscountFacadeInterface;
use Spryker\Zed\Discount\Dependency\Plugin\DiscountCalculatorPluginInterface;
use Orm\Zed\Discount\Persistence\SpyDiscount;
use Orm\Zed\Discount\Persistence\SpyDiscountDecisionRule;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucher;
use Orm\Zed\Discount\Persistence\SpyDiscountVoucherPoolCategory;
use Spryker\Zed\Kernel\Business\ModelResult;
use Orm\Zed\Discount\Persistence\SpyDiscountDecisionRule as DecisionRule;
use Spryker\Zed\Kernel\Business\AbstractFacade;
use Spryker\Zed\Discount\Business\Model\DiscountableInterface;

/**
 * @method DiscountDependencyContainer getDependencyContainer()
 */
class DiscountFacade extends AbstractFacade implements DiscountFacadeInterface
{

    /**
     * @param CalculableInterface $container
     *
     * @return SpyDiscount[]
     */
    public function calculateDiscounts(CalculableInterface $container)
    {
        return $this->getDependencyContainer()->createDiscount($container)->calculate();
    }

    /**
     * @param string $code
     *
     * @return ModelResult
     */
    public function isVoucherUsable($code)
    {
        return $this->getDependencyContainer()->getDecisionRuleVoucher()->isUsable($code);
    }

    /**
     * @param CalculableInterface $container
     * @param DecisionRule $decisionRule
     *
     * @return ModelResult
     */
    public function isMinimumCartSubtotalReached(CalculableInterface $container, DecisionRule $decisionRule)
    {
        return $this->getDependencyContainer()
            ->getDecisionRuleMinimumCartSubtotal()
            ->isMinimumCartSubtotalReached($container, $decisionRule);
    }

    /**
     * @param DiscountableInterface[] $discountableObjects
     * @param float $percentage
     *
     * @return float
     */
    public function calculatePercentage(array $discountableObjects, $percentage)
    {
        return $this->getDependencyContainer()->createCalculatorPercentage()->calculate($discountableObjects, $percentage);
    }

    /**
     * @param DiscountableInterface[] $discountableObjects
     * @param float $amount
     *
     * @return float
     */
    public function calculateFixed(array $discountableObjects, $amount)
    {
        return $this->getDependencyContainer()->createCalculatorFixed()->calculate($discountableObjects, $amount);
    }

    /**
     * @param DiscountableInterface[] $discountableObjects
     * @param DiscountTransfer $discountTransfer
     *
     * @return void
     */
    public function distributeAmount(array $discountableObjects, DiscountTransfer $discountTransfer)
    {
        $this->getDependencyContainer()->createDistributor()->distribute($discountableObjects, $discountTransfer);
    }

    /**
     * @param VoucherTransfer $voucherTransfer
     *
     * @return VoucherCreateInfoTransfer
     */
    public function createVoucherCodes(VoucherTransfer $voucherTransfer)
    {
        return $this->getDependencyContainer()->createVoucherEngine()->createVoucherCodes($voucherTransfer);
    }

    /**
     * @param VoucherTransfer $voucherTransfer
     *
     * @return SpyDiscountVoucher
     */
    public function createVoucherCode(VoucherTransfer $voucherTransfer)
    {
        return $this->getDependencyContainer()->createVoucherEngine()->createVoucherCode($voucherTransfer);
    }

    /**
     * @param VoucherCodesTransfer $voucherCodesTransfer
     *
     * @return self
     */
    public function saveVoucherCode(VoucherCodesTransfer $voucherCodesTransfer)
    {
        return $this->getDependencyContainer()->createVoucherCodesWriter()->saveVoucherCode($voucherCodesTransfer);
    }

    /**
     * @return array
     */
    public function getDecisionRulePluginNames()
    {
        return $this->getDependencyContainer()->getConfig()->getDecisionRulePluginNames();
    }

    /**
     * @param DiscountTransfer $discountTransfer
     *
     * @return SpyDiscount
     */
    public function createDiscount(DiscountTransfer $discountTransfer)
    {
        return $this->getDependencyContainer()->createDiscountWriter()->create($discountTransfer);
    }

    /**
     * @param DiscountTransfer $discountTransfer
     *
     * @return SpyDiscount
     */
    public function updateDiscount(DiscountTransfer $discountTransfer)
    {
        return $this->getDependencyContainer()->createDiscountWriter()->update($discountTransfer);
    }

    /**
     * @return array
     */
    public function getVoucherPoolCategories()
    {
        return $this->getDependencyContainer()
            ->createVoucherPoolCategory()
            ->getAvailableVoucherPoolCategories();
    }

    /**
     * @param DecisionRuleTransfer $decisionRuleTransfer
     *
     * @return DecisionRule
     */
    public function saveDiscountDecisionRule(DecisionRuleTransfer $decisionRuleTransfer)
    {
        return $this->getDependencyContainer()->createDiscountDecisionRuleWriter()->saveDiscountDecisionRule($decisionRuleTransfer);
    }

    /**
     * @param CartRuleTransfer $cartRuleFormTransfer
     *
     * @return DiscountTransfer
     */
    public function saveCartRules(CartRuleTransfer $cartRuleFormTransfer)
    {
        return $this->getDependencyContainer()->createCartRule()->saveCartRule($cartRuleFormTransfer);
    }

    /**
     * @param DecisionRuleTransfer $discountDecisionRuleTransfer
     *
     * @return SpyDiscountDecisionRule
     */
    public function createDiscountDecisionRule(DecisionRuleTransfer $discountDecisionRuleTransfer)
    {
        return $this->getDependencyContainer()->createDiscountDecisionRuleWriter()->create($discountDecisionRuleTransfer);
    }

    /**
     * @param int $idDiscount
     *
     * @return array
     */
    public function getCurrentCartRulesDetailsByIdDiscount($idDiscount)
    {
        return $this->getDependencyContainer()
            ->createCartRule()
            ->getCurrentCartRulesDetailsByIdDiscount($idDiscount);
    }

    /**
     * @param DecisionRuleTransfer $discountDecisionRuleTransfer
     *
     * @return SpyDiscountDecisionRule
     */
    public function updateDiscountDecisionRule(DecisionRuleTransfer $discountDecisionRuleTransfer)
    {
        return $this->getDependencyContainer()->createDiscountDecisionRuleWriter()->update($discountDecisionRuleTransfer);
    }

    /**
     * @param VoucherTransfer $discountVoucherTransfer
     *
     * @return SpyDiscountVoucher
     */
    public function createDiscountVoucher(VoucherTransfer $discountVoucherTransfer)
    {
        return $this->getDependencyContainer()->createDiscountVoucherWriter()->create($discountVoucherTransfer);
    }

    /**
     * @param VoucherTransfer $discountVoucherTransfer
     *
     * @return SpyDiscountVoucher
     */
    public function updateDiscountVoucher(VoucherTransfer $discountVoucherTransfer)
    {
        return $this->getDependencyContainer()->createDiscountVoucherWriter()->update($discountVoucherTransfer);
    }

    /**
     * @param VoucherPoolTransfer $discountVoucherPoolTransfer
     *
     * @return SpyDiscountVoucher
     */
    public function createDiscountVoucherPool(VoucherPoolTransfer $discountVoucherPoolTransfer)
    {
        return $this->getDependencyContainer()->createDiscountVoucherPoolWriter()->create($discountVoucherPoolTransfer);
    }

    /**
     * @param VoucherPoolTransfer $discountVoucherPoolTransfer
     *
     * @return SpyDiscountVoucher
     */
    public function updateDiscountVoucherPool(VoucherPoolTransfer $discountVoucherPoolTransfer)
    {
        return $this->getDependencyContainer()->createDiscountVoucherPoolWriter()->update($discountVoucherPoolTransfer);
    }

    /**
     * @param VoucherPoolCategoryTransfer $discountVoucherPoolCategoryTransfer
     *
     * @return SpyDiscountVoucherPoolCategory
     */
    public function createDiscountVoucherPoolCategory(VoucherPoolCategoryTransfer $discountVoucherPoolCategoryTransfer)
    {
        return $this->getDependencyContainer()->createDiscountVoucherPoolCategoryWriter()
            ->create($discountVoucherPoolCategoryTransfer);
    }

    /**
     * @param VoucherPoolCategoryTransfer $discountVoucherPoolCategoryTransfer
     *
     * @return SpyDiscountVoucherPoolCategory
     */
    public function updateDiscountVoucherPoolCategory(VoucherPoolCategoryTransfer $discountVoucherPoolCategoryTransfer)
    {
        return $this->getDependencyContainer()->createDiscountVoucherPoolCategoryWriter()
            ->update($discountVoucherPoolCategoryTransfer);
    }

    /**
     * @param string $poolCategoryName
     *
     * @return SpyDiscountVoucherPoolCategory
     */
    public function getOrCreateDiscountVoucherPoolCategoryByName($poolCategoryName)
    {
        return $this->getDependencyContainer()->createDiscountVoucherPoolCategoryWriter()
            ->getOrCreateByName($poolCategoryName);
    }

    /**
     * @param string $pluginName
     *
     * @return DiscountCalculatorPluginInterface
     */
    public function getCalculatorPluginByName($pluginName)
    {
        return $this->getDependencyContainer()->getConfig()->getCalculatorPluginByName($pluginName);
    }

    /**
     * @param CalculableInterface $container
     * @param DiscountCollectorTransfer $discountCollectorTransfer
     *
     * @return array
     */
    public function getDiscountableItems(
        CalculableInterface $container,
        DiscountCollectorTransfer $discountCollectorTransfer
    ) {
        return $this->getDependencyContainer()->createItemCollector()->collect($container, $discountCollectorTransfer);
    }

    /**
     * @param CalculableInterface $container
     * @param DiscountCollectorTransfer $discountCollectorTransfer
     *
     * @return OrderTransfer[]
     */
    public function getDiscountableItemExpenses(
        CalculableInterface $container,
        DiscountCollectorTransfer $discountCollectorTransfer
    ) {
        return $this->getDependencyContainer()->createItemExpenseCollector()
            ->collect($container, $discountCollectorTransfer);
    }

    /**
     * @param CalculableInterface $container
     * @param DiscountCollectorTransfer $discountCollectorTransfer
     *
     * @return OrderTransfer[]
     */
    public function getDiscountableOrderExpenses(
        CalculableInterface $container,
        DiscountCollectorTransfer $discountCollectorTransfer
    ) {
        return $this->getDependencyContainer()->createOrderExpenseCollector()
            ->collect($container, $discountCollectorTransfer);
    }

    /**
     * @param CalculableInterface $container
     * @param DiscountCollectorTransfer $discountCollectorTransfer
     *
     * @return OrderTransfer[]
     */
    public function getDiscountableItemProductOptions(
        CalculableInterface $container,
        DiscountCollectorTransfer $discountCollectorTransfer
    ) {
        return $this->getDependencyContainer()->createItemProductOptionCollector()
            ->collect($container, $discountCollectorTransfer);
    }

    /**
     * @param CalculableInterface $container
     * @param DiscountCollectorTransfer $discountCollectorTransfer
     *
     * @return OrderTransfer[]
     */
    public function getDiscountableItemsFromCollectorAggregate(
        CalculableInterface $container,
        DiscountCollectorTransfer $discountCollectorTransfer
    ) {
        return $this->getDependencyContainer()->createAggregateCollector()
            ->collect($container, $discountCollectorTransfer);
    }

    /**
     * @return array
     */
    public function getDiscountCollectors()
    {
        return array_keys($this->getDependencyContainer()->createAvailableCollectorPlugins());
    }

    /**
     * @return array
     */
    public function getDiscountCalculators()
    {
        return array_keys($this->getDependencyContainer()->createAvailableCalculatorPlugins());
    }

    /**
     * @param array|string[] $codes
     *
     * @return bool
     */
    public function releaseUsedVoucherCodes(array $codes)
    {
        return $this->getDependencyContainer()->createVoucherCode()->releaseUsedCodes($codes);
    }

    /**
     * @param array|string[] $codes
     *
     * @return bool
     */
    public function useVoucherCodes(array $codes)
    {
        return $this->getDependencyContainer()->createVoucherCode()->useCodes($codes);
    }

}
