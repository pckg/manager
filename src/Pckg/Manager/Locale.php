<?php namespace Pckg\Manager;

use Locale as PhpLocale;
use Pckg\Collection;
use Pckg\Locale\Entity\Languages;
use Pckg\Locale\LangInterface;

class Locale
{

    /**
     * @var PhpLocale
     */
    protected $locale;

    /**
     * @var LangInterface
     */
    protected $lang;

    /**
     * @var Collection
     */
    protected $languages;

    /**
     * @var Collection
     */
    protected $frontendLanguages;

    /**
     * @var Collection
     */
    protected $backendLanguages;

    public function __construct(LangInterface $lang)
    {
        $this->lang = $lang;
        $this->locale = new PhpLocale();
    }

    public function setLocale($locale, $language = null)
    {
        $langId = $language;
        if (!$language) {
            list($langId) = explode('_', $locale);
        }
        config()->set('pckg.locale.language', $langId);
        config()->set('pckg.locale.default', $locale);

        $utf8Suffix = strpos($locale, '.utf8')
            ? ''
            : '.utf8';
        setlocale(LC_ALL, $locale . $utf8Suffix);
        setlocale(LC_TIME, $locale . $utf8Suffix);
        PhpLocale::setDefault($locale);
        $this->lang->setLangId($langId);
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

    public function setCurrent($locale, $language = null)
    {
        $this->setLocale($locale, $language);

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

    public function prepareLanguages()
    {
        if ($this->languages) {
            return $this;
        }

        $this->languages = (new Languages())->all();
        $this->frontendLanguages = $this->languages->filter('frontend');
        $this->backendLanguages = $this->languages->filter('backend');
    }

    public function getFrontendLanguages()
    {
        $this->prepareLanguages();

        return $this->frontendLanguages;
    }

    public function isMultilingual()
    {
        $this->prepareLanguages();

        return $this->frontendLanguages->count() > 1;
    }

}