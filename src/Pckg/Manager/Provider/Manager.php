<?php namespace Pckg\Manager\Provider;

use Pckg\Framework\Provider;
use Pckg\Manager\Asset;
use Pckg\Manager\Locale;
use Pckg\Manager\Meta;
use Pckg\Manager\Seo;
use Pckg\Manager\Vue;

class Manager extends Provider
{

    public function viewObjects()
    {
        return [
            '_assetManager'  => Asset::class,
            '_vueManager'    => Vue::class,
            '_metaManager'   => Meta::class,
            '_seoManager'    => Seo::class,
            '_pckg'          => Pckg::class,
            '_localeManager' => Locale::class,
        ];
    }

}

class Pckg
{

    public function chain($chain)
    {
        if (!is_array($chain)) {
            $chain = [$chain];
        }

        return chain($chain);
    }

}