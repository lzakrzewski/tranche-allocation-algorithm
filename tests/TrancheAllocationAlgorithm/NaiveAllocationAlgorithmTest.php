<?php

declare(strict_types=1);

namespace tests\TrancheAllocationAlgorithm;

use Money\Money;
use PHPUnit\Framework\TestCase;
use TrancheAllocationAlgorithm\NaiveAllocationAlgorithm;
use TrancheAllocationAlgorithm\Tranche;
use TrancheAllocationAlgorithm\Wallet;

class NaiveAllocationAlgorithmTest extends TestCase
{
    /** @var NaiveAllocationAlgorithm */
    private $algorithm;

    /** @test */
    public function it_can_allocate_1_wallet_in_1_tranche(): void
    {
        $wallets = [
            Wallet::withMoney('w1', Money::GBP('100')),
        ];

        $tranches = [
            Tranche::withAmount('t1', Money::GBP('100')),
        ];

        $allocation = $this->algorithm->allocate($wallets, $tranches);

        $this->assertEquals(
            [
                [
                    'wallet'  => 'w1',
                    'tranche' => 't1',
                    'amount'  => Money::GBP('100'),
                ],
            ],
            $allocation
        );
    }

    /** @test */
    public function it_can_allocate_3_wallets_in_1_tranche(): void
    {
        $wallets = [
            Wallet::withMoney('w1', Money::GBP('33')),
            Wallet::withMoney('w2', Money::GBP('33')),
            Wallet::withMoney('w3', Money::GBP('34')),
        ];

        $tranches = [
            Tranche::withAmount('t1', Money::GBP('100')),
        ];

        $allocation = $this->algorithm->allocate($wallets, $tranches);

        $this->assertEquals(
            [
                [
                    'wallet'  => 'w1',
                    'tranche' => 't1',
                    'amount'  => Money::GBP('33'),
                ],
                [
                    'wallet'  => 'w2',
                    'tranche' => 't1',
                    'amount'  => Money::GBP('33'),
                ],
                [
                    'wallet'  => 'w3',
                    'tranche' => 't1',
                    'amount'  => Money::GBP('34'),
                ],
            ],
            $allocation
        );
    }

    /** @test */
    public function it_can_allocate_3_wallets_in_1_tranche_when_more_money_in_wallets(): void
    {
        $wallets = [
            Wallet::withMoney('w1', Money::GBP('100')),
            Wallet::withMoney('w2', Money::GBP('33')),
            Wallet::withMoney('w3', Money::GBP('34')),
        ];

        $tranches = [
            Tranche::withAmount('t1', Money::GBP('100')),
        ];

        $allocation = $this->algorithm->allocate($wallets, $tranches);

        $this->assertEquals(
            [
                [
                    'wallet'  => 'w1',
                    'tranche' => 't1',
                    'amount'  => Money::GBP('34'),
                ],
                [
                    'wallet'  => 'w2',
                    'tranche' => 't1',
                    'amount'  => Money::GBP('33'),
                ],
                [
                    'wallet'  => 'w3',
                    'tranche' => 't1',
                    'amount'  => Money::GBP('33'),
                ],
            ],
            $allocation
        );
    }

    /** {@inheritdoc} */
    protected function setUp(): void
    {
        $this->algorithm = new NaiveAllocationAlgorithm();
    }

    /** {@inheritdoc} */
    protected function tearDown(): void
    {
        $this->algorithm = null;
    }
}
