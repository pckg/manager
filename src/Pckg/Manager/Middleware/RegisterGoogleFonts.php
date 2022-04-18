<?php

namespace Pckg\Manager\Middleware;

use Pckg\Manager\Asset;

class RegisterGoogleFonts
{
    /**
     * @var Asset
     */
    protected $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function execute(callable $next)
    {
        foreach (config('pckg.manager.asset.googleFonts', []) as $font) {
            $this->asset->addGoogleFont($font['font'], $font['weight'] ?? '400,700', $font['set'] ?? 'latin,latin-ext');
        }

        return $next();
    }
}
