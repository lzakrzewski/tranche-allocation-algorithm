<?php

declare(strict_types=1);

namespace tests\TrancheAllocationAlgorithm;

use Money\Money;
use PHPUnit\Framework\TestCase;
use TrancheAllocationAlgorithm\Percentage;
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
    public function it_can_pick_one_pound(): void
    {
        $wallet   = Wallet::create('w1', Money::GBP('1000'), ['A', 'B'], Percentage::_75());
        $onePound = $wallet->pickOnePound();

        $this->assertEquals(Money::GBP('1'), $onePound);
        $this->assertEquals(Money::GBP('999'), $wallet->balance());
    }

    /** @test */
    public function it_can_be_empty(): void
    {
        $wallet = Wallet::create('w1', Money::GBP('5'), ['A', 'B'], Percentage::_75());

        $wallet->pickOnePound();
        $wallet->pickOnePound();
        $wallet->pickOnePound();
        $wallet->pickOnePound();
        $wallet->pickOnePound();

        $this->assertTrue($wallet->isEmpty());
    }

    /** @test */
    public function it_can_not_be_negative(): void
    {
        $this->expectException(\DomainException::class);

        $wallet = Wallet::create('w1', Money::GBP('5'), ['A', 'B'], Percentage::_75());

        $wallet->pickOnePound();
        $wallet->pickOnePound();
        $wallet->pickOnePound();
        $wallet->pickOnePound();
        $wallet->pickOnePound();
        $wallet->pickOnePound();
    }
}
