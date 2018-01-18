<?php

declare(strict_types=1);

namespace TrancheAllocationAlgorithm;

use Money\Money;

class Wallet
{
    /** @var Money */
    private $balance;

    /**
     * @param Money $balance
     *
     * @return Wallet
     */
    public static function withMoney(Money $balance): self
    {
        return new self($balance);
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

    private function __construct(Money $balance)
    {
        $this->balance = $balance;

        $this->guardAgainstNegativeBalance();
    }

    private function guardAgainstNegativeBalance(): void
    {
        if (true === $this->balance->isNegative()) {
            throw new \InvalidArgumentException(sprintf('Wallet balance can not be negative', $this->balance->getAmount()));
        }
    }
}
