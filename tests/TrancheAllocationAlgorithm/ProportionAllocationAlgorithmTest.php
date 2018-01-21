<?php

declare(strict_types=1);

namespace tests\TrancheAllocationAlgorithm;

use Money\Money;
use PHPUnit\Framework\TestCase;
use TrancheAllocationAlgorithm\Percentage;
use TrancheAllocationAlgorithm\ProportionAllocationAlgorithm;
use TrancheAllocationAlgorithm\Tranche;
use TrancheAllocationAlgorithm\Wallet;

class ProportionAllocationAlgorithmTest extends TestCase
{
    /** @var ProportionAllocationAlgorithm */
    private $algorithm;

    /** @test */
    public function it_can_allocate_2_wallets_to_2_tranches(): void
    {
        $wallets = [
            Wallet::create('w1', Money::GBP('100'), ['A'], Percentage::_75()),
            Wallet::create('w2', Money::GBP('100'), ['A'], Percentage::_75()),
        ];

        $tranches = [
            Tranche::create('t1', 'A', Money::GBP('100'), Percentage::_75()),
            Tranche::create('t2', 'A', Money::GBP('100'), Percentage::_75()),
        ];

        $allocation = $this->algorithm->allocate($wallets, $tranches);

        $this->assertEquals(
            [
                [
                    'wallet'  => 'w1',
                    'tranche' => 't1',
                    'amount'  => Money::GBP('50'),
                ],
                [
                    'wallet'  => 'w1',
                    'tranche' => 't2',
                    'amount'  => Money::GBP('50'),
                ],
                [
                    'wallet'  => 'w2',
                    'tranche' => 't1',
                    'amount'  => Money::GBP('50'),
                ],
                [
                    'wallet'  => 'w2',
                    'tranche' => 't2',
                    'amount'  => Money::GBP('50'),
                ],
            ],
            $allocation
        );
    }

    protected function setUp(): void
    {
        $this->algorithm = new ProportionAllocationAlgorithm();
    }

    protected function tearDown(): void
    {
        $this->algorithm = null;
    }
}
