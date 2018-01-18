<?php

declare(strict_types=1);

namespace TrancheAllocationAlgorithm;

interface AllocationAlgorithm
{
    /**
     * @param Wallet[]  $wallets
     * @param Tranche[] $tranches
     *
     * @return array
     */
    public function allocate(array $wallets, array $tranches): array;
}
