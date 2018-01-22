<?php

declare(strict_types=1);

namespace TrancheAllocationAlgorithm;

use Money\Money;

class ProportionAllocationAlgorithm implements AllocationAlgorithm
{
    /** {@inheritdoc} */
    public function allocate(array $wallets, array $tranches): array
    {
        $allocations = [];

        foreach ($wallets as $wallet) {
            $allocations = array_merge(
                $allocations,
                $this->allocationsForWallet($wallet, $tranches)
            );
        }

        return $allocations;
    }

    private function allocationsForWallet(Wallet $wallet, array $tranches): array
    {
        $result           = [];
        $eligibleTranches = $this->eligibleTranches($wallet, $tranches);

        if (empty($eligibleTranches)) {
            return [];
        }

        $sumOnTranches    = $this->sumOnTranches($eligibleTranches);
        $ratios           = $this->ratios($sumOnTranches, $eligibleTranches);
        // var_dump($ratios);
        try {
            $allocations = $wallet->balance()->allocate($ratios);
        } catch (\Exception $e) {
            var_dump($ratios, $eligibleTranches);
            die;
        }

        //  var_dump($allocations);

        // die;

        foreach ($eligibleTranches as $key => $tranche) {
            if (false === isset($allocations[$key])) {
                continue;
            }

            $result[] = [
                'wallet'  => $wallet->id(),
                'tranche' => $tranche->id(),
                'amount'  => $allocations[$key],
            ];
        }

        return $result;
    }

    private function eligibleTranches(Wallet $wallet, $tranches): array
    {
        $eligibleTranches = array_filter(
            $tranches,
            function (Tranche $tranche) use ($wallet) {
                return $wallet->canInvestIn($tranche) && $tranche->availableAmount()->isPositive();
            }
        );

        return array_values($eligibleTranches);
    }

    private function sumOnTranches(array $eligibleTranches): Money
    {
        return array_reduce(
            $eligibleTranches,
            function (Money $carry, Tranche $tranche) {
                return $carry->add($tranche->availableAmount());
            },
            Money::GBP('0')
        );
    }

    private function ratios(Money $sumOnTranches, array $eligibleTranches): array
    {
        $ratios = [];

        /** @var Tranche $tranche */
        foreach ($eligibleTranches as $tranche) {
            $ratios[] = $tranche->availableAmount()->ratioOf($sumOnTranches);
        }

        return $ratios;
    }
}
