<?php

declare(strict_types=1);

namespace tests\unit;

use Money\Money;
use PHPUnit\Framework\TestCase;
use TrancheAllocationAlgorithm\Percentage;
use TrancheAllocationAlgorithm\Sum;
use TrancheAllocationAlgorithm\Tranche;
use TrancheAllocationAlgorithm\Wallet;

class SumTest extends TestCase
{
    /** @test */
    public function it_can_calculate_sum_of_tranches(): void
    {
        $sum = Sum::ofTranches([
            Tranche::create('t1', 'A', Money::GBP('1001')),
            Tranche::create('t2', 'A', Money::GBP('2001')),
            Tranche::create('t3', 'A', Money::GBP('1111')),
        ]);

        $this->assertTrue($sum->equals(Money::GBP('4113')));
    }

    /** @test */
    public function it_can_calculate_sum_on_tranches_when_no_tranches(): void
    {
        $sum = Sum::ofTranches([]);

        $this->assertTrue($sum->equals(Money::GBP('0')));
    }

    /** @test */
    public function it_fails_when_try_to_calculate_sum_on_tranches_with_invalid_object(): void
    {
        $this->expectException(\TypeError::class);

        Sum::ofTranches([new \stdClass()]);
    }

    /** @test */
    public function it_can_calculate_sum_of_eligible_tranches(): void
    {
        $wallet = Wallet::create('w1', Money::GBP('100'), ['A', 'B'], Percentage::_70());

        $sum = Sum::ofEligibleTranches(
            $wallet,
            [
                Tranche::create('t1', 'A', Money::GBP('11'), Percentage::_70()),
                Tranche::create('t2', 'B', Money::GBP('22'), Percentage::_65()),
                Tranche::create('t3', 'C', Money::GBP('33'), Percentage::_65()),
                Tranche::create('t4', 'A', Money::GBP('44'), Percentage::_75()),
            ]
        );

        $this->assertTrue($sum->equals(Money::GBP('33')));
    }

    /** @test */
    public function it_can_calculate_sum_of_wallets(): void
    {
        $sum = Sum::ofWallets([
            Wallet::create('w1', Money::GBP('100'), ['A', 'B'], Percentage::_70()),
            Wallet::create('w2', Money::GBP('222'), ['C'], Percentage::_75()),
            Wallet::create('w3', Money::GBP('333'), ['A'], Percentage::_65()),
        ]);

        $this->assertTrue($sum->equals(Money::GBP('655')));
    }

    /** @test */
    public function it_can_calculate_sum_of_allocation(): void
    {
        $sum = Sum::ofAllocations([
            ['wallet' => 'w1', 'tranche' => 't1', 'amount' => Money::GBP('101')],
            ['wallet' => 'w2', 'tranche' => 't1', 'amount' => Money::GBP('222')],
            ['wallet' => 'w3', 'tranche' => 't1', 'amount' => Money::GBP('354')],
        ]);

        $this->assertTrue($sum->equals(Money::GBP('677')));
    }

    /** @test */
    public function it_can_calculate_sum_of_allocation_for_wallet(): void
    {
        $sum = Sum::ofAllocationForWallet(
            'w1',
            [
                ['wallet' => 'w1', 'tranche' => 't1', 'amount' => Money::GBP('101')],
                ['wallet' => 'w1', 'tranche' => 't2', 'amount' => Money::GBP('222')],
                ['wallet' => 'w3', 'tranche' => 't1', 'amount' => Money::GBP('354')],
            ]
        );

        $this->assertTrue($sum->equals(Money::GBP('323')));
    }

    /** @test */
    public function it_fails_when_amount_key_does_not_exist_in_allocation(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Sum::ofAllocations([
            ['wallet' => 'w1', 'tranche' => 't1'],
            ['wallet' => 'w2', 'tranche' => 't1', 'amount' => Money::GBP('222')],
            ['wallet' => 'w3', 'tranche' => 't1', 'amount' => Money::GBP('354')],
        ]);
    }

    /** @test */
    public function it_fails_when_amount_key_is_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Sum::ofAllocations([
            ['wallet' => 'w1', 'tranche' => 't1', 'amount' => new \stdClass()],
            ['wallet' => 'w2', 'tranche' => 't1', 'amount' => Money::GBP('222')],
            ['wallet' => 'w3', 'tranche' => 't1', 'amount' => Money::GBP('354')],
        ]);
    }

    /** @test */
    public function it_fails_when_wallet_key_does_not_exist_in_allocation(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Sum::ofAllocationForWallet(
            'w1',
            [
                ['wallet' => 'w1', 'tranche' => 't1', 'amount' => Money::GBP('101')],
                ['tranche' => 't2', 'amount' => Money::GBP('222')],
                ['wallet'  => 'w3', 'tranche' => 't1', 'amount' => Money::GBP('354')],
            ]
        );
    }
}
