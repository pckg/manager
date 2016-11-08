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

    public function getCurrent()
    {
        return $_GET['locale'] ?? 'en_GB';

        return $this->locale->getDefault();
    }

    public function getDefault()
    {
        return PhpLocale::getDefault();
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