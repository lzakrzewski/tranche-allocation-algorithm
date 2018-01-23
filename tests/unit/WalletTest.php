<?php

declare(strict_types=1);

namespace tests\unit;

use Money\Money;
use PHPUnit\Framework\TestCase;
use TrancheAllocationAlgorithm\Percentage;
use TrancheAllocationAlgorithm\Tranche;
use TrancheAllocationAlgorithm\Wallet;

class WalletTest extends TestCase
{
    /** @test */
    public function it_can_be_created(): void
    {
        $wallet = Wallet::create('w1', Money::GBP('1000'), ['A', 'B'], Percentage::_75());

        $this->assertInstanceOf(Wallet::class, $wallet);
        $this->assertEquals('w1', $wallet->id());
        $this->assertEquals(Money::GBP('1000'), $wallet->balance());
        $this->assertEquals(['A', 'B'], $wallet->tranches());
        $this->assertEquals(Percentage::_75(), $wallet->percentage());
    }

    /** @test */
    public function it_can_not_be_created_with_negative_balance(): void
    {
        $this->expectException(\DomainException::class);

        Wallet::create('w1', Money::GBP('-1000'), ['A', 'B'], Percentage::_75());
    }

    /** @test */
    public function it_knows_when_it_can_invest_in_tranche(): void
    {
        $wallet = Wallet::create('w1', Money::GBP('1000'), ['A', 'B'], Percentage::_75());

        $tranche = Tranche::create('t1', 'A', Money::GBP('1000'), Percentage::_75());

        $this->assertTrue($wallet->canInvestIn($tranche));
    }

    /** @test */
    public function it_can_not_invest_when_tranche_has_greater_percentage(): void
    {
        $wallet = Wallet::create('w1', Money::GBP('1000'), ['A', 'B'], Percentage::_65());

        $tranche = Tranche::create('t1', 'A', Money::GBP('1000'), Percentage::_75());

        $this->assertFalse($wallet->canInvestIn($tranche));
    }

    /** @test */
    public function it_can_not_invest_when_tranche_does_not_have_matching_name(): void
    {
        $wallet = Wallet::create('w1', Money::GBP('1000'), ['B'], Percentage::_75());

        $tranche = Tranche::create('t1', 'A', Money::GBP('1000'), Percentage::_70());

        $this->assertFalse($wallet->canInvestIn($tranche));
    }

    /** @test */
    public function it_can_not_invest_when_tranche_is_empty(): void
    {
        $wallet = Wallet::create('w1', Money::GBP('1000'), ['A', 'B'], Percentage::_75());

        $tranche = Tranche::create('t1', 'A', Money::GBP('0'), Percentage::_75());

        $this->assertFalse($wallet->canInvestIn($tranche));
    }

    /** @test */
    public function it_can_invest(): void
    {
        $wallet  = Wallet::create('w1', Money::GBP('1000'), ['A', 'B'], Percentage::_75());
        $tranche = Tranche::create('t1', 'A', Money::GBP('1000'), Percentage::_75());

        $wallet->invest($tranche, Money::GBP('100'));

        $this->assertEquals(Money::GBP('900'), $wallet->balance());
        $this->assertEquals(Money::GBP('900'), $tranche->availableAmount());
    }

    /** @test */
    public function it_can_be_empty(): void
    {
        $wallet  = Wallet::create('w1', Money::GBP('5'), ['A', 'B'], Percentage::_75());
        $tranche = Tranche::create('t1', 'A', Money::GBP('1000'), Percentage::_75());

        $wallet->invest($tranche, Money::GBP('5'));

        $this->assertTrue($wallet->isEmpty());
    }

    /** @test */
    public function it_can_not_be_negative(): void
    {
        $this->expectException(\DomainException::class);

        $wallet  = Wallet::create('w1', Money::GBP('5'), ['A', 'B'], Percentage::_75());
        $tranche = Tranche::create('t1', 'A', Money::GBP('1000'), Percentage::_75());

        $wallet->invest($tranche, Money::GBP('6'));
    }
}
