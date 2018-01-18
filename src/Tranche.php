<?php

declare(strict_types=1);

namespace TrancheAllocationAlgorithm;

use Money\Money;

class Tranche
{
    /** @var string */
    private $name;

    /** @var Money */
    private $availableAmount;

    /** @var Money */
    private $allocation;

    /**
     * @param string $name
     * @param Money  $amount
     *
     * @return Tranche
     */
    public static function withAmount(string $name, Money $amount): self
    {
        return new self($name, $amount);
    }

    /**
     * @param Money $amount
     *
     * @return Money
     */
    public function allocate(Money $amount): Money
    {
        $this->allocation      = $this->allocation->add($amount);
        $this->availableAmount = $this->availableAmount->subtract($amount);

        $this->guardAgainstNegativeAvailableAmount();

        return $amount;
    }

    public function isFunded(): bool
    {
        return $this->availableAmount->isZero();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function availableAmount(): Money
    {
        return $this->availableAmount;
    }

    public function allocation(): Money
    {
        return $this->allocation;
    }

    private function __construct(string $name, Money $availableAmount)
    {
        $this->name            = $name;
        $this->allocation      = Money::GBP('0');
        $this->availableAmount = $availableAmount;

        $this->guardAgainstNegativeAvailableAmount();
    }

    private function guardAgainstNegativeAvailableAmount(): void
    {
        if (true === $this->availableAmount->isNegative()) {
            throw new \DomainException('Tranche availableAmount can not be negative');
        }
    }
}
