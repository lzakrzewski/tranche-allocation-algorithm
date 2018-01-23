<?php

declare(strict_types=1);

namespace tests\TrancheAllocationAlgorithm;

use Money\Money;
use PHPUnit\Framework\TestCase;
use TrancheAllocationAlgorithm\Percentage;
use TrancheAllocationAlgorithm\Tranche;

class TrancheTest extends TestCase
{
    /** @test */
    public function it_can_be_created(): void
    {
        $tranche = Tranche::create('t1', 'A', Money::GBP('1001'), Percentage::_75());

        $this->assertInstanceOf(Tranche::class, $tranche);
        $this->assertEquals('t1', $tranche->id());
        $this->assertEquals('A', $tranche->name());
        $this->assertEquals(Money::GBP('1001'), $tranche->availableAmount());
        $this->assertEquals(Percentage::_75(), $tranche->percentage());
    }

    /** @test */
    public function it_can_not_be_created_with_negative_available_amount(): void
    {
        $this->expectException(\DomainException::class);

        Tranche::create('t1', 'A', Money::GBP('-1001'), Percentage::_75());
    }

    /** @test */
    public function it_can_invest_money(): void
    {
        $tranche = Tranche::create('t1', 'A', Money::GBP('1001'), Percentage::_75());

        $tranche->invest(Money::GBP('100'));
        $tranche->invest(Money::GBP('100'));

        $this->assertEquals(Money::GBP('801'), $tranche->availableAmount());
    }

    /** @test */
    public function it_can_be_funded(): void
    {
        $tranche = Tranche::create('t1', 'A', Money::GBP('1001'), Percentage::_75());

        $tranche->invest(Money::GBP('1000'));
        $tranche->invest(Money::GBP('1'));

        $this->assertTrue($tranche->isFunded());
    }

    /** @test */
    public function it_can_not_be_with_negative_available_amount(): void
    {
        $this->expectException(\DomainException::class);

        $tranche = Tranche::create('t1', 'A', Money::GBP('1001'), Percentage::_75());

        $tranche->invest(Money::GBP('1002'));
    }
}
