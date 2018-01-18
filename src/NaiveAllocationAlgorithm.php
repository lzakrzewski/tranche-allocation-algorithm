<?php

declare(strict_types=1);

namespace TrancheAllocationAlgorithm;

class NaiveAllocationAlgorithm implements AllocationAlgorithm
{
    /** {@inheritdoc} */
    public function allocate(array $wallets, array $tranches): array
    {
        $allocations = [];

        do {
            $notEmptyWallets   = $this->notEmptyWallets($wallets);
            $notFundedTranches = $this->notFundedTranches($tranches);

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
        } while (0 !== count($notEmptyWallets) || 0 !== count($notFundedTranches));

        return array_values($allocations);
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

    private function group(array $allocations): array
    {
        return array_reduce(
            $allocations,
            function (array $carry, array $allocations): void {
            },
            []
        );
    }
}
