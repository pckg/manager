<?php namespace Pckg\Manager;

use Locale as PhpLocale;
use Pckg\Collection;
use Pckg\Locale\Entity\Languages;
use Pckg\Locale\LangInterface;
use Pckg\Locale\Record\Language;

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

        $this->fetchLanguages();
    }

    public function fetchLanguages()
    {
        $languagesEntity = new Languages();
        if (!$languagesEntity->getRepository()->getCache()->hasTable($languagesEntity->getTable())) {
            $this->languages = $this->frontendLanguages = $this->backendLanguages = new Collection();

            return;
        }
        $this->languages = $languagesEntity->orderBy('`default` DESC')
                                           ->cache('1hour', 'app', Locale::class . ':' . Languages::class)
                                           ->all();
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

    public function getDefaultFrontendLanguage()
    {
        $this->prepareLanguages();

        return ($this->frontendLanguages->first(function(Language $language) {
                    return $language->frontend;
                }) ?? $this->frontendLanguages->first()) ?? $this->languages->first();
    }

    public function getLanguages()
    {
        $this->prepareLanguages();

        return $this->languages;
    }

    public function getLanguageBy($key, $val)
    {
        return $this->languages->first(function(Language $language) use ($key, $val) {
            return $language->{$key} == $val;
        });
    }

}