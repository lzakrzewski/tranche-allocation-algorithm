<?php

declare(strict_types=1);

namespace TrancheAllocationAlgorithm;

use Rogervila\ArrayDiffMultidimensional;

class NaiveAllocationAlgorithm implements AllocationAlgorithm
{
    /** {@inheritdoc} */
    public function allocate(array $wallets, array $tranches): array
    {
        $before = [];

        while (true) {
            $after = $this->doAllocate($wallets, $tranches, $before);

            if (empty(ArrayDiffMultidimensional::compare($after, $before))) {
                return array_values($after);
            }

            $before = $after;
        }

        throw new \Exception('Unable to allocate');
    }

    private function doAllocate(array $wallets, array $tranches, array $allocations): array
    {
        $notEmptyWallets   = $this->notEmptyWallets($wallets);
        $notFundedTranches = $this->notFundedTranches($tranches);

        if (0 === count($notEmptyWallets)) {
            return $allocations;
        }

        if (0 === count($notFundedTranches)) {
            return $allocations;
        }

        foreach ($notEmptyWallets as $wallet) {
            foreach ($notFundedTranches as $tranche) {
                try {
                    $combination = $wallet->name().$tranche->name();
                    $allocation  = $tranche->allocate($wallet->pickOnePound());

                    if (false === isset($allocations[$combination])) {
                        $allocations[$combination] = [
                            'wallet'  => $wallet->name(),
                            'tranche' => $tranche->name(),
                            'amount'  => $allocation,
                        ];

                        continue;
                    }

                    $allocations[$combination]['amount'] = $allocations[$combination]['amount']->add($allocation);
                } catch (\DomainException $exception) {
                }
            }
        }

        return $allocations;
    }

    /**
     * @param Wallet[] $wallets
     *
     * @return Wallet[]
     */
    private function notEmptyWallets(array $wallets): array
    {
        return array_filter($wallets, function (Wallet $wallet) {return !$wallet->isEmpty(); });
    }

    /**
     * @param Tranche[] $tranches
     *
     * @return Tranche[]
     */
    private function notFundedTranches(array $tranches): array
    {
        return array_filter($tranches, function (Tranche $tranche) {return !$tranche->isFunded(); });
    }
}
