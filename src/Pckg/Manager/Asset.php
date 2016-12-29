<?php namespace Pckg\Manager;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Pckg\Collection;
use Pckg\Manager\Asset\BaseAssets;
use Pckg\Manager\Asset\LessPckgFilter;
use Pckg\Manager\Asset\PathPckgFilter;
use ReflectionClass;
use Throwable;

class Asset
{

    use BaseAssets;

    protected $collections = [];

    protected $types = [
        "css"  => '<link rel="stylesheet" type="text/css" href="##LINK##" />',
        "less" => '<link rel="stylesheet" type="text/css" href="##LINK##" />',
        "js"   => '<script type="text/javascript" src="##LINK##"></script>',
    ];

    protected $googleFonts = [];

    protected $externals = [];

    protected $assets = [];

    protected $lessVariableFiles = [];

    public function touchCollection($type, $section = 'main', $priority = 0)
    {
        if (!isset($this->collections[$type][$section][$priority])) {
            $this->collections[$type][$section][$priority] = new AssetCollection([], [], path('cache'));
            $this->collections[$type][$section][$priority]->setTargetPath(
                path('storage') . 'cache' . path('ds') . 'www' . path('ds') . $type . path(
                    'ds'
                ) . $priority . '-' . $section . '.' . $type
            );
        }

        return $this->collections[$type][$section][$priority];
    }

    public function touchAssetCollection($section = 'main', $priority = 0)
    {
        if (!isset($this->assets[$section])) {
            $this->assets[$section] = [];
        }
        if (!isset($this->assets[$section][$priority])) {
            $this->assets[$section][$priority] = [];
        }
    }

    public function addWwwAssets($assets, $section, $priority = 0)
    {
        return $this->addAssets($assets, $section, path('www'), $priority);
    }

    public function addAssets($assets, $section = 'main', $path = '', $priority = 0)
    {
        if (!is_array($assets)) {
            $assets = [$assets];
        }

        foreach ($assets as $asset) {
            $collection = null;
            /**
             * Callable asset.
             */
            if (is_callable($asset)) {
                if ($asset = $asset()) {
                    $this->touchAssetCollection($section, $priority);
                    $this->assets[$section][$priority][] = $asset;
                }
                continue;
            }

            /**
             * External asset.
             */
            if (strpos($asset, 'https://') === 0 || strpos($asset, 'http://') === 0 || strpos($asset, '//') === 0) {
                $this->externals[] = $asset;
                continue;
            }

            /**
             * Set default path.
             */
            if (strpos($asset, path('root')) === 0) {
                $path = '';

            } elseif (strpos($asset, '/') === 0) {
                $path = substr(path('root'), 0, -1);

            } elseif (!$path) {
                $path = path('root');

            }

            /**
             * Internal asset.
             */
            if (mb_strrpos($asset, '.js') == strlen($asset) - strlen('.js')) {
                $this->collections['js'][$section][$priority][] = $path . $asset;

            } else if (mb_strrpos($asset, '.css') == strlen($asset) - strlen('.css')) {
                $this->collections['css'][$section][$priority][] = $path . $asset;

            } else if (mb_strrpos($asset, '.less') == strlen($asset) - strlen('.less')) {
                if (strpos($asset, '@') === 0) {
                    $this->lessVariableFiles[] = substr($asset, 1);

                } else {
                    $this->collections['less'][$section][$priority][] = $path . $asset;
                }

            }
        }
    }

    public function getLessVariableFiles()
    {
        return $this->lessVariableFiles;
    }

    public function addAppAssets($assets, $section = 'main', $app, $priority = 90)
    {
        if (is_object($app)) {
            $app = strtolower(get_class($app));
        }

        $appPath = 'app' . path('ds') . $app . path('ds');
        $publicPath = 'public' . path('ds');

        $this->addAssets($assets, $section, $appPath . $publicPath, $priority);
    }

    public function addAppProviderAssets($assets, $section = 'main', $app, $provider, $priority = 80)
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

        $this->addAssets($assets, $section, $appPath . $providerPath . $publicPath, $priority);
    }

    public function addProviderAssets($assets, $section = 'main', $provider, $priority = 70)
    {
        $reflector = new ReflectionClass(is_object($provider) ? get_class($provider) : $provider);
        $file = $reflector->getFileName();

        $providerPath = realpath(substr($file, 0, strrpos($file, path('ds'))) . path('ds') . '..') . path('ds');
        $publicPath = 'public' . path('ds');

        $this->addAssets($assets, $section, $providerPath . $publicPath, $priority);
    }

    public function addVendorProviderAsset($assets, $section = 'main', $vendor, $relative = '', $priority = 60)
    {
        $vendorPath = 'vendor' . path('ds') . $vendor . path('ds');
        $relativePath = $relative
            ? $relative . path('ds')
            : '';

        $this->addAssets($assets, $section, $vendorPath . $relativePath, $priority);
    }

    public function addGoogleFont($family, $sets = null, $subset = null)
    {
        $this->googleFonts[] = [
            'family' => $family,
            'sets'   => (array)$sets,
            'subset' => (array)$subset,
        ];

        return $this;
    }

    public function getGoogleFonts()
    {
        $return = [];

        foreach ($this->googleFonts as $font) {
            $return[] = '<link href="//fonts.googleapis.com/css?family=' .
                        urlencode($font['family']) . ':' .
                        implode(',', $font['sets']) .
                        ($font['subset'] ? '&subset=' . implode(',', $font['subset']) : '')
                        . '" rel="stylesheet" type="text/css" />';
        }

        return implode("\n", $return);
    }

    public function getExternals()
    {
        $return = [];

        foreach ($this->types as $type => $html) {
            foreach ($this->externals as $external) {
                if (mb_strrpos($external, '.' . $type) == strlen($external) - strlen('.' . $type) || strpos($external, '.' . $type . '?') || strpos($external, '/' . $type . '?')) {
                    $return[] = str_replace('##LINK##', $external, $html);
                }
            }
        }

        return implode("\n", $return);
    }

    public function getMeta($onlyTypes = [], $onlySections = [])
    {
        return implode(
            "\n",
            array_merge($this->getAsseticAssets($onlyTypes, $onlySections), $this->getAssets($onlySections))
        );
    }

    private function getKeysIfEmpty($array, $filled)
    {
        if (!$filled) {
            return array_keys($array);
        }

        return $filled;
    }

    protected function getAsseticAssets($onlyTypes = [], $onlySections = [])
    {
        $return = [];

        $onlyTypes = $this->getKeysIfEmpty($this->collections, $onlyTypes);

        foreach ($onlyTypes as $type) {
            if (!isset($this->collections[$type])) {
                continue;
            }

            $onlySections = $this->getKeysIfEmpty($this->collections[$type], $onlySections);

            foreach ($onlySections as $section) {
                if (!isset($this->collections[$type][$section])) {
                    continue;

                } else {
                    $collections = $this->collections[$type][$section];
                }

                /**
                 * Sort collections by priority.
                 */
                ksort($collections);

                foreach ($collections as $priority => $collection) {
                    $typePath = path('storage') . 'cache' . path('ds') . 'www' . path('ds') . $type . path('ds');
                    $assetCollection = new AssetCollection([], [], $typePath);

                    foreach ($collection as $asset) {
                        $filters = [];
                        if ($type == 'less') {
                            $filters[] = new LessPckgFilter();
                            $filters[] = new PathPckgFilter();
                        }
                        if (in_array($type, ['css', 'less'])) {
                            $filters[] = new PathPckgFilter();
                        }
                        $assetCollection->add(new FileAsset($asset, $filters));
                    }

                    $lastModified = $assetCollection->getLastModified();
                    $hash = sha1((new Collection($assetCollection->all()))->map(
                        function($item) {
                            return $item->getSourcePath();
                        }
                    )->implode(':'));
                    $cachePath = $typePath . $priority . '-' . $section . '-' . $lastModified . '-' . $hash . '.' . $type;
                    $assetCollection->setTargetPath($cachePath);

                    file_put_contents($cachePath, $assetCollection->dump());

                    $return[] = str_replace(
                        '##LINK##',
                        str_replace(path('root'), path('ds'), $cachePath),
                        $this->types[$type]
                    );
                }
            }
        }

        return $return;
    }

    protected function getAssets($onlySections = [])
    {
        $return = [];

        $onlySections = $this->getKeysIfEmpty($this->assets, $onlySections);

        foreach ($onlySections as $section) {
            if (!isset($this->assets[$section])) {
                continue;

            } else {
                $assets = $this->assets[$section];
            }

            ksort($assets);

            foreach ($assets as $priority => $realAssets) {
                foreach ($realAssets as $asset) {
                    $return[] = $asset;
                }
            }
        }

        return $return;
    }

    public function __toString()
    {
        try {
            return $this->getMeta();
        } catch (Throwable $e) {
            return exception($e);
        }
    }

}
