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

    public function canInvestIn(Tranche $tranche): bool
    {
        if (false === in_array($tranche->name(), $this->tranches())) {
            return false;
        }

        if (true === $tranche->percentage()->isGreaterThan($this->percentage())) {
            return false;
        }

        if (true === $this->isEmpty() || true === $tranche->isFunded()) {
            return false;
        }

        return true;
    }

    public function invest(Tranche $tranche, Money $amount): void
    {
        if (false === $this->canInvestIn($tranche)) {
            throw new \DomainException(sprintf('Can not invest to tranche %s', $tranche->id()));
        }

        $this->updateBalance($tranche->invest($amount));
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
        if (true === $balance->isNegative()) {
            throw new \DomainException('Wallet balance can not be negative');
        }

        $this->id         = $id;
        $this->balance    = $balance;
        $this->tranches   = $tranches;
        $this->percentage = $percentage;
    }

    private function updateBalance(Money $amount): void
    {
        $newBalance = $this->balance->subtract($amount);

        if (true === $newBalance->isNegative()) {
            throw new \DomainException('Wallet balance can not be negative');
        }

        $this->balance = $newBalance;
    }
}
