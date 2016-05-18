<?php namespace Pckg\Manager\Provider;

use Pckg\Framework\Provider;
use Pckg\Manager\Asset;
use Pckg\Manager\Meta;
use Pckg\Manager\Seo;

class Config extends Provider
{

    public function viewObjects()
    {
        return [
            '_assetManager' => Asset::class,
            '_metaManager'  => Meta::class,
            '_seoManager'   => Seo::class,
        ];
    }

}