<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductOption;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;

class PriceCalculatorService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Calculate base price of a product
     */
    public function calculateProductBasePrice(Product $product): string
    {
        return $product->getBasePrice() ?? '0.00';
    }

    /**
     * Calculate total price for a product with selected options
     */
    public function calculateProductTotalPrice(Product $product, array $selectedOptions = []): array
    {
        $basePrice = floatval($this->calculateProductBasePrice($product));
        $optionsPrice = 0;
        $optionDetails = [];

        foreach ($selectedOptions as $optionCode) {
            $option = $this->entityManager->getRepository(ProductOption::class)->findOneBy(['code' => $optionCode]);
            if ($option && $option->isActive()) {
                $optionsPrice += floatval($option->getPrice());
                $optionDetails[] = [
                    'id' => $option->getId(),
                    'code' => $option->getCode(),
                    'name' => $option->getName(),
                    'price' => $option->getPrice(),
                    'group' => $option->getOptionGroup()?->getName()
                ];
            }
        }

        $totalPrice = $basePrice + $optionsPrice;

        return [
            'base_price' => number_format($basePrice, 2, '.', ''),
            'options_price' => number_format($optionsPrice, 2, '.', ''),
            'total_price' => number_format($totalPrice, 2, '.', ''),
            'option_details' => $optionDetails
        ];
    }

    /**
     * Calculate price for an order item with quantity
     */
    public function calculateOrderItemPrice(Product $product, array $selectedOptions = [], int $quantity = 1): array
    {
        $productPricing = $this->calculateProductTotalPrice($product, $selectedOptions);
        
        $subtotal = floatval($productPricing['total_price']) * $quantity;
        $total = $subtotal; // Add taxes, shipping, etc. here if needed

        return [
            'base_price' => $productPricing['base_price'],
            'options_price' => $productPricing['options_price'],
            'unit_price' => $productPricing['total_price'],
            'quantity' => $quantity,
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'total' => number_format($total, 2, '.', ''),
            'option_details' => $productPricing['option_details']
        ];
    }

    /**
     * Validate selected options for a product
     */
    public function validateProductOptions(Product $product, array $selectedOptionCodes): array
    {
        $availableOptions = $product->getAvailableOptions();
        $validOptions = [];
        $invalidOptions = [];

        foreach ($selectedOptionCodes as $optionCode) {
            $option = $availableOptions->filter(fn($opt) => $opt->getCode() === $optionCode)->first();
            if ($option && $option->isActive()) {
                $validOptions[] = $option;
            } else {
                $invalidOptions[] = $optionCode;
            }
        }

        return [
            'valid' => $validOptions,
            'invalid' => $invalidOptions,
            'is_valid' => empty($invalidOptions)
        ];
    }

    /**
     * Get pricing breakdown for display
     */
    public function getPricingBreakdown(Product $product, array $selectedOptions = [], int $quantity = 1): array
    {
        $calculation = $this->calculateOrderItemPrice($product, $selectedOptions, $quantity);
        
        $breakdown = [
            'product_name' => $product->getName(),
            'product_code' => $product->getCode(),
            'quantity' => $quantity,
            'base_price' => [
                'amount' => $calculation['base_price'],
                'label' => 'Prix de base'
            ],
            'options' => []
        ];

        foreach ($calculation['option_details'] as $option) {
            $breakdown['options'][] = [
                'name' => $option['name'],
                'group' => $option['group'],
                'price' => $option['price'],
                'code' => $option['code']
            ];
        }

        $breakdown['options_price'] = [
            'amount' => $calculation['options_price'],
            'label' => 'Options'
        ];

        $breakdown['subtotal'] = [
            'amount' => $calculation['subtotal'],
            'label' => 'Sous-total'
        ];

        $breakdown['total'] = [
            'amount' => $calculation['total'],
            'label' => 'Total'
        ];

        return $breakdown;
    }

    /**
     * Check if an option group allows multiple selections
     */
    public function isOptionGroupMultiSelect($optionGroup): bool
    {
        // Check if option group allows multiple selections
        // You might want to add this field to ProductOptionGroup entity
        return false; // Default to single selection
    }

    /**
     * Group options by their option group for UI display
     */
    public function groupOptionsByGroup(Product $product): array
    {
        $groupedOptions = [];
        
        foreach ($product->getAvailableOptions() as $option) {
            $group = $option->getOptionGroup();
            $groupName = $group?->getName() ?: 'Options';
            
            if (!isset($groupedOptions[$groupName])) {
                $groupedOptions[$groupName] = [
                    'group_name' => $groupName,
                    'group_code' => $group?->getCode() ?: 'default',
                    'is_multi_select' => $this->isOptionGroupMultiSelect($group),
                    'options' => []
                ];
            }
            
            $groupedOptions[$groupName]['options'][] = [
                'id' => $option->getId(),
                'code' => $option->getCode(),
                'name' => $option->getName(),
                'description' => $option->getDescription(),
                'price' => $option->getPrice(),
                'is_active' => $option->isActive()
            ];
        }

        return array_values($groupedOptions);
    }

    /**
     * Calculate price for different quantities (volume discounts)
     */
    public function calculateVolumePricing(Product $product, array $selectedOptions = [], int $quantity = 1): array
    {
        $baseCalculation = $this->calculateOrderItemPrice($product, $selectedOptions, 1);
        $unitPrice = floatval($baseCalculation['total_price']);
        
        $pricing = [];
        
        // Calculate pricing for different quantities
        for ($qty = 1; $qty <= max(10, $quantity); $qty++) {
            $subtotal = $unitPrice * $qty;
            $discount = $this->calculateVolumeDiscount($qty);
            $discountAmount = $subtotal * ($discount / 100);
            $finalTotal = $subtotal - $discountAmount;
            
            $pricing[$qty] = [
                'quantity' => $qty,
                'unit_price' => number_format($unitPrice, 2, '.', ''),
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'discount_percentage' => $discount,
                'discount_amount' => number_format($discountAmount, 2, '.', ''),
                'final_total' => number_format($finalTotal, 2, '.', ''),
                'savings' => number_format($subtotal - $finalTotal, 2, '.', '')
            ];
        }
        
        return $pricing;
    }

    /**
     * Calculate volume discount percentage based on quantity
     */
    private function calculateVolumeDiscount(int $quantity): float
    {
        if ($quantity >= 10) return 10.0;
        if ($quantity >= 5) return 5.0;
        if ($quantity >= 3) return 2.0;
        return 0.0;
    }
}