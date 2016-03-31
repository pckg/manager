<?php namespace Pckg\Manager;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Exception;
use Pckg\Manager\Asset\BaseAssets;
use ReflectionClass;

class Asset
{

    use BaseAssets;

    protected $collections = [];

    protected $types = [
        "css" => '<link rel="stylesheet" type="text/css" href="##LINK##" />',
        "js"  => '<script type="text/javascript" src="##LINK##"></script>',

    ];

    public function touchCollection($type, $section = 'main')
    {
        if (!isset($this->collections[$type][$section])) {
            if (!isset($this->collections[$type])) {
                $this->collections[$type] = [];
            }

            $this->collections[$type][$section] = new AssetCollection([], [], path('cache'));
            $this->collections[$type][$section]->setTargetPath(path('www') . 'cache/' . $type . '/' . $section . '.' . $type);
        }

        return $this->collections[$type][$section];
    }

    public function addAssets($assets, $section = 'main', $path = '')
    {
        if (!is_array($assets)) {
            $assets = [$assets];
        }

        foreach ($assets as $asset) {
            $collection = null;
            if (mb_strrpos($asset, '.js') == strlen($asset) - strlen('.js')) {
                $collection = $this->touchCollection('js', $section);

            } else if (mb_strrpos($asset, '.css') == strlen($asset) - strlen('.css')) {
                $collection = $this->touchCollection('css', $section);

            }

            if (!$collection) {
                throw new Exception('Cannot touch collection');
            }

            if (!$path) {
                $path = path('root');
            }

            $collection->add(new FileAsset($path . $asset));
        }
    }

    public function addAppAssets($assets, $section = 'main', $app)
    {
        if (is_object($app)) {
            $app = strtolower(get_class($app));
        }

        $appPath = 'app' . path('ds') . $app . path('ds');
        $publicPath = 'public' . path('ds');

        $this->addAssets($assets, $section, $appPath . $publicPath);
    }

    public function addProviderAssets($assets, $section = 'main', $provider)
    {
        $reflector = new ReflectionClass(is_object($provider) ? get_class($provider) : $provider);
        $file = $reflector->getFileName();

        $providerPath = realpath(substr($file, 0, strrpos($file, path('ds'))) . path('ds') . '..') . path('ds');
        $publicPath = 'public' . path('ds');

        $this->addAssets($assets, $section, $providerPath . $publicPath);
    }

    public function addAppProviderAssets($assets, $section = 'main', $app, $provider)
    {
        if (is_object($app)) {
            $app = strtolower(get_class($app));
        }

        if (is_object($provider)) {
            $provider = get_class($provider);
        }

        $appPath = 'app' . path('ds') . $app . path('ds') . 'src' . path('ds');
        $providerPath = implode(path('ds'), array_slice(explode('\\', $provider), 0, -2)) . path('ds');
        $publicPath = 'public' . path('ds');

        $this->addAssets($assets, $section, $appPath . $providerPath . $publicPath);
    }

    public function addVendorProviderAsset($assets, $section = 'main', $vendor, $relative = '')
    {
        $vendorPath = 'vendor' . path('ds') . $vendor . path('ds');
        $relativePath = $relative
            ? $relative . path('ds')
            : '';

        $this->addAssets($assets, $section, $vendorPath . $relativePath);
    }

    public function getMeta($onlyTypes = [], $onlySections = [])
    {
        $return = [];

        if (!$onlyTypes) {
            $onlyTypes = array_keys($this->collections);
        }

        foreach ($onlyTypes as $type) {
            if (!isset($this->collections[$type])) {
                continue;
            }

            if (!$onlySections) {
                $onlySections = array_keys($this->collections[$type]);
            }

            foreach ($onlySections as $section) {
                if (!isset($this->collections[$type][$section])) {
                    continue;

                } else {
                    $collections = $this->collections[$type][$section];
                }

                foreach ($collections as $collection) {
                    $lastModified = $collection->getLastModified();
                    $targetPath = $collection->getTargetPath();
                    $cachePath = str_lreplace('.', '-' . $lastModified . '.', $targetPath);

                    if (!is_file($cachePath)) {
                        $collection->setTargetPath($cachePath);
                        file_put_contents($cachePath, $collection->dump());
                    }

                    $return[] = str_replace('##LINK##', str_replace(path('www'), '/', $cachePath), $this->types[$type]);
                }
            }
        }

        return implode("\n", $return);
    }

    public function __toString()
    {
        try {
            return $this->getMeta();
        } catch (Exception $e) {
            return exception($e);
        }
    }

}
