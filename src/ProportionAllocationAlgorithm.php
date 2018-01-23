<?php

declare(strict_types=1);

namespace TrancheAllocationAlgorithm;

use Money\Money;

class ProportionAllocationAlgorithm implements AllocationAlgorithm
{
    /** {@inheritdoc} */
    public function allocate(array $wallets, array $tranches): array
    {
        return array_reduce(
            $wallets,
            function (array $allocations, Wallet $wallet) use ($tranches) {
                return array_merge($allocations, $this->allocationsForWallet($wallet, $tranches));
            },
            []
        );
    }

    private function allocationsForWallet(Wallet $wallet, array $tranches): array
    {
        $result           = [];
        $eligibleTranches = $this->eligibleTranches($wallet, $tranches);

        if (true === empty($eligibleTranches)) {
            return [];
        }

        $sumOnTranches = Sum::ofTranches($eligibleTranches);
        $ratios        = $this->ratios($sumOnTranches, $eligibleTranches);
        $allocations   = $wallet->balance()->allocate($ratios);

        foreach ($eligibleTranches as $key => $tranche) {
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
                return $wallet->canInvestIn($tranche);
            }
        );

        return array_values($eligibleTranches);
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
