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
        $amount           = Money::GBP('0');
        $eligibleTranches = $this->eligibleTranches($wallet, $tranches);
        $allocations      = $wallet->balance()->allocateTo(count($eligibleTranches));

        if (false === empty($allocations)) {
            $amount = $allocations[0];
        }

        return array_map(
            function (Tranche $tranche) use ($wallet, $amount) {
                return [
                    'wallet'  => $wallet->id(),
                    'tranche' => $tranche->id(),
                    'amount'  => $amount,
                ];
            },
            $eligibleTranches
        );
    }

    private function eligibleTranches(Wallet $wallet, $tranches): array
    {
        return array_filter(
            $tranches,
            function (Tranche $tranche) use ($wallet) {
                return $wallet->canInvestIn($tranche);
            }
        );
    }
}
