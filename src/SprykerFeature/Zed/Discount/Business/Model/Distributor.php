<?php

namespace SprykerFeature\Zed\Discount\Business\Model;

use Generated\Zed\Ide\AutoCompletion;
use Generated\Shared\Transfer\Discount\DependencyDiscountableItemInterfaceTransfer;
use SprykerEngine\Zed\Kernel\Locator;

class Distributor implements
    DistributorInterface
{
    /**
     * @var float
     */
    protected $roundingError;

    /**
     * @var Locator
     */
    protected $locator;

    /**
     * @param Locator $locator
     */
    public function __construct(Locator $locator)
    {
        $this->locator = $locator;
    }

    /**
     * @param DiscountableItemInterface[] $discountableObjects
     * @param float $amount
     */
    public function distribute(array $discountableObjects, $amount)
    {
        foreach ($discountableObjects as $discountableObject) {
            $totalGrossPrice = $this->getGrossPriceOfDiscountableObjects($discountableObjects);

            if ($totalGrossPrice == 0) {
                continue;
            }

            if ($amount <= 0) {
                continue;
            }

            /**
             * There should not be a discount that is higher than the total gross price of all discountable objects
             */
            if ($amount > $totalGrossPrice) {
                $amount = $totalGrossPrice;
            }

            $percentage = $discountableObject->getGrossPrice() / $totalGrossPrice;
            $discountAmount = $this->roundingError + $amount * $percentage;
            $discountAmountRounded = round($discountAmount, 2);
            $this->roundingError = $discountAmount - $discountAmountRounded;
            $this->addDiscountToDiscountableObject($discountableObject, $discountAmountRounded);
        }
    }

    /**
     * @param DiscountableItemInterface $discountableObject
     * @param $discountAmount
     */
    protected function addDiscountToDiscountableObject(DiscountableItemInterface $discountableObject, $discountAmount)
    {
        $discounts = $discountableObject->getDiscounts();
        $discount = new \Generated\Shared\Transfer\CalculationDiscountTransfer();
        $discount->setAmount($discountAmount);
        $discounts->add($discount);
        $discountableObject->setDiscounts($discounts);
    }

    /**
     * @param DiscountableInterface[] $discountableObjects
     * @return int
     */
    protected function getGrossPriceOfDiscountableObjects($discountableObjects)
    {
        $totalGrossPrice = 0;

        foreach ($discountableObjects as $discountableObject) {
            $totalGrossPrice += $discountableObject->getGrossPrice();
        }

        return $totalGrossPrice;
    }

    /**
     * @return Locator|AutoCompletion
     */
    protected function getLocator()
    {
        return $this->locator;
    }
}
