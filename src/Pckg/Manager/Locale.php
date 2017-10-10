<?php namespace Pckg\Manager;

use Locale as PhpLocale;
use Pckg\Locale\Lang;

class Locale
{

    /**
     * @var PhpLocale
     */
    protected $locale;

    /**
     * @var Lang
     */
    protected $lang;

    public function __construct(Lang $lang)
    {
        $this->lang = $lang;
        $this->locale = new PhpLocale();
    }

    public function setLocale($locale)
    {
        $utf8Suffix = strpos($locale, '.utf8')
            ? ''
            : '.utf8';
        setlocale(LC_ALL, $locale . $utf8Suffix);
        setlocale(LC_TIME, $locale . $utf8Suffix);
        PhpLocale::setDefault($locale);
        $this->lang->setLangId(explode('_', $locale)[0]);
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