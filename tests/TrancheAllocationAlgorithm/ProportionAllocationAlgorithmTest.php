<?php

declare(strict_types=1);

namespace tests\TrancheAllocationAlgorithm;

use Money\Currency;
use Money\Money;
use PHPUnit\Framework\TestCase;
use TrancheAllocationAlgorithm\Percentage;
use TrancheAllocationAlgorithm\ProportionAllocationAlgorithm;
use TrancheAllocationAlgorithm\Sum;
use TrancheAllocationAlgorithm\Tranche;
use TrancheAllocationAlgorithm\Wallet;

class ProportionAllocationAlgorithmTest extends TestCase
{
    /** @var ProportionAllocationAlgorithm */
    private $algorithm;

    /** @test */
    public function it_can_allocate_2_equal_wallets_to_2_equal_tranches(): void
    {
        $wallets = [
            Wallet::create('w1', Money::GBP('100'), ['A'], Percentage::_75()),
            Wallet::create('w2', Money::GBP('100'), ['A'], Percentage::_75()),
        ];

        $tranches = [
            Tranche::create('t1', 'A', Money::GBP('100'), Percentage::_75()),
            Tranche::create('t2', 'A', Money::GBP('100'), Percentage::_75()),
        ];

        $allocations = $this->algorithm->allocate($wallets, $tranches);

        $this->assertEquals(Sum::ofWallets($wallets), Sum::ofAllocations($allocations));
        $this->assertEquals(Sum::ofTranches($tranches), Sum::ofAllocations($allocations));

        $this->assertEquals(
            [
                ['wallet'  => 'w1', 'tranche' => 't1', 'amount'  => Money::GBP('50')],
                ['wallet'  => 'w1', 'tranche' => 't2', 'amount'  => Money::GBP('50')],
                ['wallet'  => 'w2', 'tranche' => 't1', 'amount'  => Money::GBP('50')],
                ['wallet'  => 'w2', 'tranche' => 't2', 'amount'  => Money::GBP('50')],
            ],
            $allocations
        );
    }

    /** @test */
    public function it_can_allocate_2_wallets_to_2_tranches(): void
    {
        $wallets = [
            Wallet::create('w1', Money::GBP('100'), ['A'], Percentage::_75()),
            Wallet::create('w2', Money::GBP('60'), ['A'], Percentage::_75()),
        ];

        $tranches = [
            Tranche::create('t1', 'A', Money::GBP('110'), Percentage::_75()),
            Tranche::create('t2', 'A', Money::GBP('90'), Percentage::_75()),
        ];

        $allocations = $this->algorithm->allocate($wallets, $tranches);

        $this->assertEquals(Sum::ofWallets($wallets), Sum::ofAllocations($allocations));
        $this->assertEquals(
            [
                ['wallet'  => 'w1', 'tranche' => 't1', 'amount'  => Money::GBP('55')],
                ['wallet'  => 'w1', 'tranche' => 't2', 'amount'  => Money::GBP('45')],
                ['wallet'  => 'w2', 'tranche' => 't1', 'amount'  => Money::GBP('33')],
                ['wallet'  => 'w2', 'tranche' => 't2', 'amount'  => Money::GBP('27')],
            ],
            $allocations
        );
    }

    /** @test */
    public function it_can_allocate_5_wallets_to_3_tranches(): void
    {
        $wallets = [
            Wallet::create('w1', Money::GBP('100000000'), ['A'], Percentage::_75()),
            Wallet::create('w2', Money::GBP('100000000'), ['A'], Percentage::_75()),
            Wallet::create('w3', Money::GBP('20000000'), ['A'], Percentage::_75()),
            Wallet::create('w4', Money::GBP('300000'), ['A'], Percentage::_75()),
            Wallet::create('w5', Money::GBP('100000'), ['A'], Percentage::_75()),
        ];

        $tranches = [
            Tranche::create('t1', 'A', Money::GBP('100000000'), Percentage::_75()),
            Tranche::create('t2', 'A', Money::GBP('20000000'), Percentage::_75()),
            Tranche::create('t3', 'A', Money::GBP('50000'), Percentage::_75()),
        ];

        $allocations = $this->algorithm->allocate($wallets, $tranches);

        $this->assertCount(15, $allocations);
    }

    /** @test */
    public function it_can_invest_using_allocation(): void
    {
        $wallets = [
            Wallet::create('w1', Money::GBP('100'), ['A'], Percentage::_75()),
            Wallet::create('w2', Money::GBP('100'), ['A'], Percentage::_75()),
        ];

        $tranches = [
            Tranche::create('t1', 'A', Money::GBP('100'), Percentage::_75()),
            Tranche::create('t2', 'A', Money::GBP('100'), Percentage::_75()),
        ];

        $this->simulateInvestments(
            $this->algorithm->allocate($wallets, $tranches),
            $wallets,
            $tranches
        );

        $this->assertEquals(Money::GBP('0'), Sum::ofTranches($tranches));
        $this->assertEquals(Money::GBP('0'), Sum::ofWallets($wallets));
    }

    /** @test */
    public function it_can_allocate_using_real_data(): void
    {
        $wallets  = $this->readCsvWallets();
        $tranches = $this->readCsvTranches();

        $allocations = $this->algorithm->allocate($wallets, $tranches);

        $this->assertNotEmpty($allocations);
    }

    protected function setUp(): void
    {
        $this->algorithm = new ProportionAllocationAlgorithm();
    }

    protected function tearDown(): void
    {
        $this->algorithm = null;
    }

    private function simulateInvestments(array $allocations, array $wallets, array $tranches): void
    {
        foreach ($allocations as $allocation) {
            $tranche = $this->findTranche($allocation['tranche'], $tranches);
            $wallet  = $this->findWallet($allocation['wallet'], $wallets);

            $wallet->invest($tranche, $allocation['amount']);
        }
    }

    private function findWallet(string $id, array $wallets): Wallet
    {
        foreach ($wallets as $wallet) {
            if ($id == $wallet->id()) {
                return $wallet;
            }
        }

        $this->fail(sprintf('Wallet with id %s does not exist', $id));
    }

    private function findTranche(string $id, array $tranches): Tranche
    {
        foreach ($tranches as $tranche) {
            if ($id == $tranche->id()) {
                return $tranche;
            }
        }

        $this->fail(sprintf('Tranche with id %s does not exist', $id));
    }

    /** @return  Wallet[] */
    private function readCsvWallets(): array
    {
        $wallets = [];
        $file    = fopen(__DIR__.'/../../fixtures/wallets_data.csv', 'r');

        while (false !== ($line = fgetcsv($file))) {
            if ('id' == $line[0]) {
                continue;
            }

            $tranches = explode('-', $line[3]);

            $wallets[] = Wallet::create(
                $line[0],
                new Money((int) $line[1] * 100, new Currency('GBP')),
                $tranches,
                new Percentage($line[2])
            );
        }
        fclose($file);

        return $wallets;
    }

    private function readCsvTranches(): array
    {
        $tranches = [];
        $file     = fopen(__DIR__.'/../../fixtures/tranches_data.csv', 'r');

        while (false !== ($line = fgetcsv($file))) {
            if ('id' == $line[0]) {
                continue;
            }

            $tranches[] = Tranche::create(
                $line[0],
                $line[2],
                new Money((int) $line[1] * 100, new Currency('GBP')),
                new Percentage($line[3])
            );
        }
        fclose($file);

        return $tranches;
    }
}
