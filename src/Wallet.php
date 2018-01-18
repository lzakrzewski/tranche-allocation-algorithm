<?php

declare(strict_types=1);

namespace TrancheAllocationAlgorithm;

use Money\Money;

class Wallet
{
    /** @var string */
    private $name;

    /** @var Money */
    private $balance;

    /**
     * @param string $name
     * @param Money  $balance
     *
     * @return Wallet
     */
    public static function withMoney(string $name, Money $balance): self
    {
        return new self($name, $balance);
    }

    /**
     * @return Money
     */
    public function pickOnePound(): Money
    {
        $this->balance = $this->balance->subtract($onePound = Money::GBP('1'));

        $this->guardAgainstNegativeBalance();

        return $onePound;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->balance->isZero();
    }

    /**
     * @return Money
     */
    public function balance(): Money
    {
        return $this->balance;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    private function __construct(string $name, Money $balance)
    {
        $this->name    = $name;
        $this->balance = $balance;

        $this->guardAgainstNegativeBalance();
    }

    private function guardAgainstNegativeBalance(): void
    {
        if (true === $this->balance->isNegative()) {
            throw new \DomainException('Wallet balance can not be negative');
        }
    }
}
