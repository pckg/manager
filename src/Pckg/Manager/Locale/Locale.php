<?php

namespace Pckg\Manager\Locale;

use NumberFormatter;

class Locale
{
    protected $locale;

    public function __construct($locale)
    {
        $this->locale = $locale;
    }

    public function decimal($number)
    {
        $formatter = new NumberFormatter($this->locale, NumberFormatter::DECIMAL);
        $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 0);
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 100);

        return $formatter->format($number);
    }
}
