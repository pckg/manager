<?php namespace Pckg\Manager;

use Locale as BaseLocale;

class Locale
{

    /**
     * @var BaseLocale
     */
    protected $locale;

    public function __construct()
    {
        $this->locale = new BaseLocale();
    }

    public function getCurrent()
    {
        return $this->locale->getDefault();
    }

    public function getDefault()
    {
        return BaseLocale::getDefault();
    }

    public function setCurrent($locale)
    {
        $this->locale->setDefault($locale);

        return $this;
    }

    public function getDateFormat()
    {
        return 'd.m.Y';
    }

    public function getTimeFormat()
    {
        return 'H:i';
    }

    public function getDatetimeFormat()
    {
        return $this->getDateFormat() . ' ' . $this->getTimeFormat();
    }

}