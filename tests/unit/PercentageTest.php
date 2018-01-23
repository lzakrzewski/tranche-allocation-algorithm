<?php

declare(strict_types=1);

namespace tests\unit;

use PHPUnit\Framework\TestCase;
use TrancheAllocationAlgorithm\Percentage;

class PercentageTest extends TestCase
{
    /** @test */
    public function it_can_be_75(): void
    {
        $percentage = Percentage::_75();

        $this->assertEquals('0.75', $percentage->value());
    }

    /** @test */
    public function it_can_be_70(): void
    {
        $percentage = Percentage::_70();

        $this->assertEquals('0.70', $percentage->value());
    }

    /** @test */
    public function it_can_be_65(): void
    {
        $percentage = Percentage::_65();

        $this->assertEquals('0.65', $percentage->value());
    }

    /** @test */
    public function it_can_be_60(): void
    {
        $percentage = Percentage::_60();

        $this->assertEquals('0.60', $percentage->value());
    }

    /** @test */
    public function it_can_be_grater(): void
    {
        $percentage = Percentage::_75();

        $this->assertTrue($percentage->isGreaterThan(Percentage::_60()));
    }

    /** @test */
    public function it_can_not_be_greater(): void
    {
        $percentage = Percentage::_60();

        $this->assertFalse($percentage->isGreaterThan(Percentage::_65()));
    }
}
