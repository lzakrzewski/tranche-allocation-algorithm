<?php

declare(strict_types=1);

namespace tests\TrancheAllocationAlgorithm;

use Money\Money;
use PHPUnit\Framework\TestCase;
use TrancheAllocationAlgorithm\Tranche;

class TrancheTest extends TestCase
{
    /** @test */
    public function it_can_have_available_amount(): void
    {
        $tranche = Tranche::withAmount('t1', Money::GBP('1001'));

        $this->assertEquals(Money::GBP('1001'), $tranche->availableAmount());
    }

    /** @test */
    public function it_can_have_name(): void
    {
        $tranche = Tranche::withAmount('t1', Money::GBP('1001'));

        $this->assertEquals('t1', $tranche->name());
    }

    /** @test */
    public function it_can_allocate_money(): void
    {
        $tranche = Tranche::withAmount('t1', Money::GBP('1001'));
        $tranche->allocate(Money::GBP('100'));
        $tranche->allocate(Money::GBP('100'));

        $this->assertEquals(Money::GBP('200'), $tranche->allocation());
        $this->assertEquals(Money::GBP('801'), $tranche->availableAmount());
    }

    /** @test */
    public function it_can_be_funded(): void
    {
        $tranche = Tranche::withAmount('t1', Money::GBP('1001'));
        $tranche->allocate(Money::GBP('1000'));
        $tranche->allocate(Money::GBP('1'));

        $this->assertTrue($tranche->isFunded());
    }

    /** @test */
    public function it_can_not_be_with_negative_available_amount(): void
    {
        $this->expectException(\DomainException::class);

        $tranche = Tranche::withAmount('t1', Money::GBP('1001'));

        $tranche->allocate(Money::GBP('1002'));
    }
}
