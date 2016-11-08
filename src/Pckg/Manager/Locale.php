<?php namespace Pckg\Manager;

use Locale as PhpLocale;

class Locale
{

    /**
     * @var PhpLocale
     */
    protected $locale;

    public function __construct()
    {
        $this->locale = new PhpLocale();
    }

    public function setLocale($locale)
    {
        setlocale(LC_ALL, $locale);
        setlocale(LC_TIME, $locale);
        PhpLocale::setDefault($locale);
    }

    public function setTimezone($timezone)
    {
        date_default_timezone_set($timezone);
    }

    public function getCurrent()
    {
        return PhpLocale::getDefault();
    }

    public function getDefault()
    {
        return PhpLocale::getDefault();
    }

    public function setCurrent($locale)
    {
        $this->setLocale($locale);

        return $this;
    }

    public function getDateFormat()
    {
        return config('pckg.locale.format.date');
    }

    public function getTimeFormat()
    {
        return config('pckg.locale.format.time');
    }

    public function getDecimalPoint()
    {
        return config('pckg.locale.decimal');
    }

    public function getThousandSeparator()
    {
        return config('pckg.locale.thousand');
    }

    public function getDatetimeFormat()
    {
        return $this->getDateFormat() . ' ' . $this->getTimeFormat();
    }

}