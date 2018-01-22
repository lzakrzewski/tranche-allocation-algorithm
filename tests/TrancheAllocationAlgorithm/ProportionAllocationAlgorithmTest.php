<?php

declare(strict_types=1);

namespace tests\TrancheAllocationAlgorithm;

use Money\Currency;
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

        $allocation = $this->algorithm->allocate($wallets, $tranches);

        $this->assertEquals(
            [
                [
                    'wallet'  => 'w1',
                    'tranche' => 't1',
                    'amount'  => Money::GBP('55'),
                ],
                [
                    'wallet'  => 'w1',
                    'tranche' => 't2',
                    'amount'  => Money::GBP('45'),
                ],
                [
                    'wallet'  => 'w2',
                    'tranche' => 't1',
                    'amount'  => Money::GBP('33'),
                ],
                [
                    'wallet'  => 'w2',
                    'tranche' => 't2',
                    'amount'  => Money::GBP('27'),
                ],
            ],
            $allocation
        );
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

        $numberOfIterations = $this->simulateInvestments($wallets, $tranches);

        $this->assertEquals(1, $numberOfIterations);
    }

    /** @test */
    public function it_can_invest_with_real_data(): void
    {
        $this->markTestIncomplete();

        $wallets  = $this->readCsvWallets();
        $tranches = $this->readCsvTranches();

        $numberOfIterations = $this->simulateInvestments($wallets, $tranches);

        $this->assertEquals(1, $numberOfIterations);
    }

    protected function setUp(): void
    {
        $this->algorithm = new ProportionAllocationAlgorithm();
    }

    protected function tearDown(): void
    {
        $this->algorithm = null;
    }

    private function simulateInvestments(array $wallets, array $tranches)
    {
        $iterations = 0;

        $history = [];

        do {
            $allocations = $this->algorithm->allocate($wallets, $tranches);

            $history[] = $allocations;

            ++$iterations;

            foreach ($allocations as $allocation) {
                $tranche = $this->findTranche($allocation['tranche'], $tranches);
                $wallet  = $this->findWallet($allocation['wallet'], $wallets);

                try {
                    $wallet->invest($tranche, $allocation['amount']);
                } catch (\Exception $e) {
                }
            }

            $sumOnTranches = $this->sumOnTranches($tranches);
            $sumOnWallets  = $this->sumOnWallets($wallets);

            if ($iterations > 100) {
                $this->createCsvLog($history);
                $this->fail('Too many iterations');
            }
        } while (!$sumOnTranches->isZero() && !$sumOnWallets->isZero());

        return $iterations;
    }

    private function sumOnTranches(array $tranches): Money
    {
        return array_reduce(
            $tranches,
            function (Money $carry, Tranche $tranche) {
                return $carry->add($tranche->availableAmount());
            },
            Money::GBP('0')
        );
    }

    private function sumOnWallets(array $wallets): Money
    {
        return array_reduce(
            $wallets,
            function (Money $carry, Wallet $wallet) {
                return $carry->add($wallet->balance());
            },
            Money::GBP('0')
        );
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

    private function createCsvLog(array $history)
    {
        // Generate CSV data from array
        $fh = fopen('php://temp', 'rw'); // don't create a file, attempt
        // to use memory instead

        // write out the headers
        fputcsv($fh, array_keys(current($allocations)));

        // write out the data
        foreach ($allocations as $row) {
            $row = array_map(
                function (array $before) {
                    $before['amount'] = ($before['amount']->getAmount() / 100);

                    return $before;
                },
                $row
            );
            var_dump($row);
            die;

            fputcsv($fh, $row);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        file_put_contents(__DIR__.'/../../var/log/allocations.csv', $csv);

        return $csv;
    }
}
