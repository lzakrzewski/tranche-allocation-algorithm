<?php

declare(strict_types=1);

namespace TrancheAllocationAlgorithm;

use Money\Money;

class Tranche
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var Money */
    private $availableAmount;

    /** @var string */
    private $percentage;

    /**
     * @param string     $id
     * @param string     $name
     * @param Money      $amount
     * @param Percentage $percentage
     *
     * @return Tranche
     */
    public static function create(string $id, string $name, Money $amount, Percentage $percentage): self
    {
        return new self($id, $name, $amount, $percentage);
    }

    /**
     * @param Money $amount
     *
     * @return Money
     */
    public function invest(Money $amount): Money
    {
        $this->availableAmount = $this->availableAmount->subtract($amount);

        $this->guardAgainstNegativeAvailableAmount();

        return $amount;
    }

    public function isFunded(): bool
    {
        return $this->availableAmount->isZero();
    }

    public function id(): string
    {
        return $this->id;
    }

    public function availableAmount(): Money
    {
        return $this->availableAmount;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function percentage(): Percentage
    {
        return $this->percentage;
    }

    private function __construct(string $id, string $name, Money $availableAmount, Percentage $percentage)
    {
        $this->id              = $id;
        $this->name            = $name;
        $this->availableAmount = $availableAmount;
        $this->percentage      = $percentage;

        $this->guardAgainstNegativeAvailableAmount();
    }

    private function guardAgainstNegativeAvailableAmount(): void
    {
        if (true === $this->availableAmount->isNegative()) {
            throw new \DomainException('Tranche availableAmount can not be negative');
        }
    }
}
