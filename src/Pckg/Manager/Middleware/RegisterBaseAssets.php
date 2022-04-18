<?php

namespace Pckg\Manager\Middleware;

use Pckg\Manager\Asset;

class RegisterBaseAssets
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
        $this->asset->execute();

        return $next();
    }
}
