<?php

declare(strict_types=1);

namespace TrancheAllocationAlgorithm;

use Money\Money;

class ApplyCapAllocationAlgorithm implements AllocationAlgorithm
{
    /** @var AllocationAlgorithm */
    private $allocationAlgorithm;

    /**
     * @param AllocationAlgorithm $allocationAlgorithm
     */
    public function __construct(AllocationAlgorithm $allocationAlgorithm)
    {
        $this->allocationAlgorithm = $allocationAlgorithm;
    }

    /**
     * {@inheritdoc}
     */
    public function allocate(array $wallets, array $tranches): array
    {
        return $this->applyCap(
            $tranches,
            $this->allocationAlgorithm->allocate($wallets, $tranches)
        );
    }

    public function applyCap(array $tranches, array $allocations): array
    {
        $caps = $this->calculateCaps($tranches, $allocations);

        foreach ($allocations as $key => $allocation) {
            if (isset($caps[$allocation['tranche']])) {
                $cap              = $caps[$allocation['tranche']];
                /** @var Money $investmentAmount */
                $investmentAmount = $allocation['amount'];

                if (true === $investmentAmount->greaterThan($cap)) {
                    $allocations[$key]['amount'] = $cap;
                }
            }
        }

        return $allocations;
    }

    private function calculateCaps(array $tranches, array $allocations): array
    {
        $caps = [];

        foreach ($tranches as $tranche) {
            $trancheId              = (string) $tranche->id();
            $amount                 = $tranche->availableAmount();
            $eligibleInvestorsCount = $this->countEligibleInvestorsForTranche($tranche, $allocations);

            if (0 !== $eligibleInvestorsCount) {
                $caps[$trancheId] = $amount->divide($eligibleInvestorsCount);
            }
        }

        return $caps;
    }

    private function countEligibleInvestorsForTranche(Tranche $tranche, array $allocations): int
    {
        $count = 0;

        foreach ($allocations as $allocation) {
            if ($allocation['tranche'] === (string) $tranche->id()) {
                ++$count;
            }
        }

        return $count;
    }
}
