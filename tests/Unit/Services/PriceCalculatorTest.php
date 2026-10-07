<?php

namespace Tests\Unit\Services;

use App\Services\PriceCalculator;
use PHPUnit\Framework\TestCase;

class PriceCalculatorTest extends TestCase
{
    private PriceCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new PriceCalculator();
    }

    public function test_calculates_total_with_default_tax_rate(): void
    {
        // 1000 * 2 = 2000, +10% tax = 2200
        $this->assertSame(2200, $this->calculator->calculateTotal(1000, 2));
    }

    public function test_calculates_total_with_custom_tax_rate(): void
    {
        $this->assertSame(1080, $this->calculator->calculateTotal(1000, 1, 0.08));
    }

    public function test_calculates_total_with_zero_tax_rate(): void
    {
        $this->assertSame(1000, $this->calculator->calculateTotal(1000, 1, 0.0));
    }

    public function test_calculates_total_with_zero_quantity_returns_zero(): void
    {
        $this->assertSame(0, $this->calculator->calculateTotal(1000, 0));
    }

    public function test_calculates_total_truncates_fractional_tax(): void
    {
        // 999 * 1 = 999, +10% tax = 1098.9 -> truncated to 1098
        $this->assertSame(1098, $this->calculator->calculateTotal(999, 1));
    }

    public function test_calculate_total_throws_for_negative_price(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->calculator->calculateTotal(-1, 1);
    }

    public function test_calculate_total_throws_for_negative_quantity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->calculator->calculateTotal(1000, -1);
    }

    public function test_applies_discount_percentage(): void
    {
        $this->assertSame(800, $this->calculator->applyDiscount(1000, 20));
    }

    public function test_apply_discount_zero_percent_returns_same_price(): void
    {
        $this->assertSame(1000, $this->calculator->applyDiscount(1000, 0));
    }

    public function test_apply_discount_hundred_percent_returns_zero(): void
    {
        $this->assertSame(0, $this->calculator->applyDiscount(1000, 100));
    }

    public function test_apply_discount_throws_for_negative_percent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->calculator->applyDiscount(1000, -1);
    }

    public function test_apply_discount_throws_for_percent_over_hundred(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->calculator->applyDiscount(1000, 101);
    }
}
