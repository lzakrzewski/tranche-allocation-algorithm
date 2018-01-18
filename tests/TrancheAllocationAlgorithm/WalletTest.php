<?php

declare(strict_types=1);

namespace tests\TrancheAllocationAlgorithm;

use Money\Money;
use PHPUnit\Framework\TestCase;
use TrancheAllocationAlgorithm\Wallet;

class WalletTest extends TestCase
{
    /** @test */
    public function it_can_have_money(): void
    {
        $wallet = Wallet::withMoney(Money::GBP('1000'));

        $this->assertEquals(Money::GBP('1000'), $wallet->balance());
    }

    /** @test */
    public function it_can_pick_one_pound(): void
    {
        $wallet   = Wallet::withMoney(Money::GBP('1000'));
        $onePound = $wallet->pickOnePound();

        $this->assertEquals(Money::GBP('1'), $onePound);
        $this->assertEquals(Money::GBP('999'), $wallet->balance());
    }

    /** @test */
    public function it_can_be_empty(): void
    {
        $wallet = Wallet::withMoney(Money::GBP('5'));

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
        $this->expectException(\InvalidArgumentException::class);

        $wallet = Wallet::withMoney(Money::GBP('5'));

        $wallet->pickOnePound();
        $wallet->pickOnePound();
        $wallet->pickOnePound();
        $wallet->pickOnePound();
        $wallet->pickOnePound();
        $wallet->pickOnePound();
    }
}
