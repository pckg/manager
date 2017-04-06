<?php namespace Pckg\Manager\Middleware;

use Pckg\Manager\Asset;

class RegisterCoreAssets
{

    /**
     * @var Asset
     */
    protected $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function execute()
    {
        $this->asset->executeCore();
    }

}