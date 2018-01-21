<?php

declare(strict_types=1);

namespace TrancheAllocationAlgorithm;

class Percentage
{
    /** @var string */
    private $value;

    public function isGreaterThan(self $percentage): bool
    {
        return $this->value() > $percentage->value();
    }

    public static function _75(): self
    {
        return new self('0.75');
    }

    public static function _70(): self
    {
        return new self('0.70');
    }

    public static function _65(): self
    {
        return new self('0.65');
    }

    public static function _60(): self
    {
        return new self('0.60');
    }

    public function value(): string
    {
        return $this->value;
    }

    private function __construct(string $value)
    {
        $this->value = $value;
    }
}
