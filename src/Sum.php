<?php

declare(strict_types=1);

namespace TrancheAllocationAlgorithm;

use Money\Money;

class Sum
{
    /**
     * @param Tranche[] $tranches
     *
     * @return Money
     */
    public static function ofTranches(array $tranches): Money
    {
        return array_reduce(
            $tranches,
            function (Money $carry, Tranche $tranche) {
                return $carry->add($tranche->availableAmount());
            },
            Money::GBP('0')
        );
    }

    /**
     * @param Wallet
     * @param Tranche[] $tranches
     *
     * @return Money
     */
    public static function ofEligibleTranches(Wallet $wallet, array $tranches): Money
    {
        return self::ofTranches(
            array_filter(
                $tranches,
                function (Tranche $tranche) use ($wallet) {
                    return $wallet->canInvestIn($tranche);
                }
            )
        );
    }

    /**
     * @param Wallet[] $wallets
     *
     * @return Money
     */
    public static function ofWallets(array $wallets): Money
    {
        return array_reduce(
            $wallets,
            function (Money $carry, Wallet $wallet) {
                return $carry->add($wallet->balance());
            },
            Money::GBP('0')
        );
    }

    /**
     * @param array $allocations
     *
     * @return Money
     */
    public static function ofAllocations(array $allocations): Money
    {
        return array_reduce(
            $allocations,
            function (Money $carry, array $allocation) {
                if (false === isset($allocation['amount']) || false === $allocation['amount'] instanceof Money) {
                    throw new \InvalidArgumentException('Allocation should have "amount" key with "Money" type');
                }

                return $carry->add($allocation['amount']);
            },
            Money::GBP('0')
        );
    }

    /**
     * @param string $walletId
     * @param array  $allocations
     *
     * @return Money
     */
    public static function ofAllocationForWallet(string $walletId, array $allocations): Money
    {
        return self::ofAllocations(
            array_filter(
                $allocations,
                function (array $allocation) use ($walletId) {
                    if (false === isset($allocation['wallet'])) {
                        throw new \InvalidArgumentException('Allocation should have "wallet" key');
                    }

                    return $walletId === $allocation['wallet'];
                }
            )
        );
    }

    /**
     * @param string $trancheId
     * @param array  $allocations
     *
     * @return Money
     */
    public static function ofAllocationForTranche(string $trancheId, array $allocations): Money
    {
        return self::ofAllocations(
            array_filter(
                $allocations,
                function (array $allocation) use ($trancheId) {
                    if (false === isset($allocation['tranche'])) {
                        throw new \InvalidArgumentException('Allocation should have "tranche" key');
                    }

                    return $trancheId === $allocation['tranche'];
                }
            )
        );
    }
}
