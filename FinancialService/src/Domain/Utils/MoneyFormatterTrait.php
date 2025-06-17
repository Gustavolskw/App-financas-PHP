<?php

namespace App\Domain\Utils;

use NumberFormatter;

trait MoneyFormatterTrait
{
    /**
     * @var NumberFormatter
     */
    private NumberFormatter $formatter;

    public function formatValue($value, $currency): false|string
    {
        return $this->formatter->formatCurrency($value, $currency ?? 'BRL');
    }
}
