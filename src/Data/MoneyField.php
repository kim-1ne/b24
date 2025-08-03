<?php

namespace B24\Devtools\Data;

class MoneyField
{
    /**
     * @throws \Exception
     */
    public function __construct(
        private float|int $price,
        private string $currency,
        private string $separator = '|'
    ) {
        $this->checkPrice($this->price);
    }

    public static function parse(string $money, string $separator = '|'): self
    {
        $explode = explode($separator, $money);

        return new self(
            $explode[0] ?? 0.00,
            $explode[1] ?? '',
            $separator
        );
    }

    public function __toString(): string
    {
        return $this->price . $this->separator . $this->currency;
    }

    /**
     * @throws \Exception
     */
    public function math(callable $func): static
    {
        $func($this->price);

        $this->checkPrice($this->price);

        return $this;
    }

    public function round(int $round = 2): self
    {
        $this->price = round($this->price, $round);
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function setPrice(float|int $price): static
    {
        $this->checkPrice($price);
        $this->price = $price;
        return $this;
    }

    public function setSeparator(string $separator): static
    {
        $this->separator = $separator;
        return $this;
    }

    /**
     * @throws \Exception
     */
    private function checkPrice(int|float $price): void
    {
        if ($price < 0) throw new \Exception("Price can't be < 0");

    }
}