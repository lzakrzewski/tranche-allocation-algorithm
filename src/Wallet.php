<?php

declare(strict_types=1);

namespace TrancheAllocationAlgorithm;

use Money\Money;

class Wallet
{
    /** @var string */
    private $id;

    /** @var Money */
    private $balance;

    /** @var array */
    private $tranches;

    /** @var Percentage */
    private $percentage;

    public static function create(string $id, Money $balance, array $tranches, Percentage $percentage): self
    {
        return new self($id, $balance, $tranches, $percentage);
    }

    public function pickOnePound(): Money
    {
        $this->balance = $this->balance->subtract($onePound = Money::GBP('1'));

        $this->guardAgainstNegativeBalance();

        return $onePound;
    }

    public function isEmpty(): bool
    {
        return $this->balance->isZero();
    }

    public function balance(): Money
    {
        return $this->balance;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function tranches(): array
    {
        return $this->tranches;
    }

    public function percentage(): Percentage
    {
        return $this->percentage;
    }

    private function __construct(string $id, Money $balance, array $tranches, Percentage $percentage)
    {
        $this->id         = $id;
        $this->balance    = $balance;
        $this->tranches   = $tranches;
        $this->percentage = $percentage;

        $this->guardAgainstNegativeBalance();
    }

    private function guardAgainstNegativeBalance(): void
    {
        if (true === $this->balance->isNegative()) {
            throw new \DomainException('Wallet balance can not be negative');
        }
    }
}
