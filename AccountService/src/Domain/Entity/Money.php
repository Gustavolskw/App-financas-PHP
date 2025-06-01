<?php

namespace App\Domain\Entity;
class Money
{
    private string $currency;
    private int $units;
    private int $cents;

    public function __construct(string $currency, int $units, int $cents = 0)
    {
        $this->currency = $currency;
        $this->setAmount($units, $cents);
    }

    public static function fromDatabase(string $currency, string $amount): self
    {
        // Espera valor no formato decimal, ex: "1234.56"
        // Vamos separar unidades e centavos
        $parts = explode('.', $amount);

        $units = (int) $parts[0];
        $cents = 0;
        if (isset($parts[1])) {
            // Se houver parte decimal, pegar os dois primeiros dígitos, completar com zero se necessário
            $decimalStr = str_pad(substr($parts[1], 0, 2), 2, '0');
            $cents = (int) $decimalStr;
        }

        return new self($currency, $units, $cents);
    }

    public static function fromClient(string $currency, int $units, int $cents): self
    {
        return new self($currency, $units, $cents);
    }

    public function setAmount(int $units, int $cents = 0): void
    {
        if ($units < 0 || $cents < 0 || $cents > 99) {
            throw new \InvalidArgumentException("Invalid money amount");
        }
        $this->units = $units;
        $this->cents = $cents;
    }

    public function getAmountInCents(): int
    {
        return $this->units * 100 + $this->cents;
    }

    public static function fromCents(string $currency, int $totalCents): self
    {
        $units = intdiv($totalCents, 100);
        $cents = $totalCents % 100;
        return new self($currency, $units, $cents);
    }


    public function add(Money $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Currencies must match for addition');
        }

        $totalCents = $this->getAmountInCents() + $other->getAmountInCents();

        return self::fromCents($this->currency, $totalCents);
    }

    public function applyRate(float $rate): self
    {
        // multiplicar centavos por taxa, arredondar para int
        $totalCents = (int) round($this->getAmountInCents() * (1 + $rate));
        return self::fromCents($this->currency, $totalCents);
    }


    public function getMoneyFormat(): string
    {
        $unitsFormatted = number_format($this->units, 0, ',', '.');
        $centsFormatted = str_pad((string)$this->cents, 2, '0', STR_PAD_LEFT);

        return match ($this->currency) {
            'BRL' => "{$unitsFormatted},{$centsFormatted} BRL",
            'EUR' => "{$unitsFormatted},{$centsFormatted} €",
            'USD' => '$' . "{$unitsFormatted}.{$centsFormatted}",
            'GBP' => '£' . "{$unitsFormatted}.{$centsFormatted}",
            default => $this->currency . ' ' . $unitsFormatted . ',' . $centsFormatted,
        };
    }
}
