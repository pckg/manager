<?php namespace Pckg\Manager;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\StringAsset;
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
        "less" => '<link rel="stylesheet" type="text/css" href="##LINK##" id="##ID##" />',
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

        $i = 0;
        foreach ($assets as $keyPriority => $asset) {
            $realPriority = $priority;
            if ($i === $keyPriority) {
                $i++;
            } else {
                $i = null;
                $realPriority = $keyPriority;
            }

            $collection = null;
            /**
             * Callable asset.
             * Should we execute this at the end of request?
             */
            if (is_only_callable($asset)) {
                //if ($asset = $asset()) {
                    $this->touchAssetCollection($section, $realPriority);
                    $this->assets[$section][$realPriority][] = $asset;
                //}
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
            $tmpPath = $path;
            if (strpos($asset, path('root')) === 0) {
                $tmpPath = '';
            } elseif (strpos($asset, '/') === 0) {
                $tmpPath = substr(path('root'), 0, -1);
            } elseif (!$path) {
                $tmpPath = path('root');
            }

            /**
             * Internal asset.
             */
            $at = strpos($asset, '@');
            if ($at === 0) {
                $this->lessVariableFiles[] = substr($asset, 1);
            } elseif ($at && strpos($asset, '/@') === false) {
                $this->collections['less'][$section][$realPriority][] = $asset;
            } else if (mb_strrpos($asset, '.js') == strlen($asset) - strlen('.js')) {
                $this->collections['js'][$section][$realPriority][] = $tmpPath . $asset;
            } else if (mb_strrpos($asset, '.css') == strlen($asset) - strlen('.css')) {
                $this->collections['less'][$section][$realPriority][] = $tmpPath . $asset;
            } else if (mb_strrpos($asset, '.less') == strlen($asset) - strlen('.less')) {
                $this->collections['less'][$section][$realPriority][] = $tmpPath . $asset;
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

        $providerPath = kaorealpath(substr($file, 0, strrpos($file, path('ds'))) . path('ds') . '..') . path('ds');
        $publicPath = 'public' . path('ds');

        $this->addAssets($assets, $section, $providerPath . $publicPath, $priority);
    }

    public function addRelativeAssets($assets, $section = 'main', $priority = 70)
    {
        $file = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'];

        $providerPath = kaorealpath(substr($file, 0, strrpos($file, path('ds'))) . path('ds') . '..') . path('ds');
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
            $return[] = '<link href="https://fonts.googleapis.com/css?family=' .
                        urlencode($font['family']) . ':' .
                        implode(',', $font['sets']) .
                        ($font['subset'] ? '&subset=' . implode(',', $font['subset']) : '') .
                        '&display=swap' . '" rel="stylesheet" />';
        }

        return collect($return)->unique()->implode("\n");
    }

    public function getExternals()
    {
        $return = [];

        foreach ($this->types as $type => $html) {
            foreach ($this->externals as $external) {
                if (mb_strrpos($external, '.' . $type) == strlen($external) - strlen('.' . $type) ||
                    strpos($external, '.' . $type . '?') || strpos($external, '/' . $type . '?')
                ) {
                    $return[] = str_replace('##LINK##', $external, $html);
                }
            }
        }

        return implode("\n", $return);
    }

    public function getMeta($onlyTypes = [], $onlySections = [])
    {
        return measure('Asset manager: ' . implode(' ', $onlyTypes) . ': ' . implode(' ', $onlySections), function() use ($onlySections, $onlyTypes) {
            return implode("\n", array_merge(
                $this->getAsseticAssets($onlyTypes, $onlySections),
                $this->getAssets($onlySections)));
        });
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

        $lessPckgFilter = config('disabledless') ? null : new LessPckgFilter();
        $pathPckgFilter = new PathPckgFilter();
        // $jsMinFilter = new CssCompressorFilter(path('root') . 'node_modules/.bin/yuicompressor');
        // $cssMinFilter = new JsCompressorFilter(path('root') . 'node_modules/.bin/yuicompressor');

        foreach ($onlyTypes as $type) {
            if (!isset($this->collections[$type])) {
                continue;
            }

            $onlySections = $this->getKeysIfEmpty($this->collections[$type], $onlySections);

            $typePath = path('storage') . 'cache' . path('ds') . 'www' . path('ds') . $type . path('ds');
            $assetCollection = new AssetCollection([], [], $typePath);
            $stringAssets = [];

            foreach ($onlySections as $section) {
                if (!isset($this->collections[$type][$section])) {
                    continue;
                }

                $collections = $this->collections[$type][$section];

                /**
                 * Sort collections by priority.
                 */
                ksort($collections, SORT_NUMERIC);

                foreach ($collections as $priority => $collection) {
                    foreach ($collection as $asset) {
                        //d("asset: " . round(strlen(@file_get_contents($asset))/1000) . "KB " . $asset);
                        $filters = [];
                        if (in_array($type, ['css', 'less'])) {
                            $filters[] = $pathPckgFilter;
                            // $filters[] = $cssMinFilter;
                        } else if (in_array($type, ['js'])) {
                            // $filters[] = $jsMinFilter;
                        }
                        if (strpos($asset, '@') && !strpos($asset, '/@')) {
                            list($class, $method) = explode('@', $asset);
                            $content = resolve($class)->{$method}();
                            $stringAsset = new StringAsset($content, $filters);
                            $stringAssets[] = sha1($content);
                            $assetCollection->add($stringAsset);
                        } else {
                            $assetCollection->add(new FileAsset($asset, $filters));
                        }
                    }
                }
            }

            $lastModified = $assetCollection->getLastModified();
            $hash = sha1((new Collection($assetCollection->all()))->map(
                             function($item) {
                                 return $item->getSourcePath();
                             }
                         )->implode(':') . ':' . ($lessPckgFilter ? $lessPckgFilter->getVarsHash() : null) . ':' . implode($stringAssets));
            $cachePath = $typePath . implode('-', $onlySections) . '-' . $lastModified . '-' . $hash . '.' . $type;
            $assetCollection->setTargetPath($cachePath);

            if (!file_exists($cachePath)) {
                try {
                    $dump = $assetCollection->dump();
                    file_put_contents($cachePath, $dump);
                } catch (Throwable $e) {
                    if (dev()) {
                        throw $e;
                    }
                    unlink($cachePath);
                }
            }

            /**
             * We don't want to process each files separately.
             */
            if ($type == 'less') {
                $lessPath = $cachePath . '.css';
                if (!file_exists($lessPath)) {
                    try {
                        $assetCollection = new AssetCollection([], [], $typePath);
                        $assetCollection->add(new FileAsset($cachePath, $lessPckgFilter ? [$lessPckgFilter] : []));
                        $assetCollection->setTargetPath($lessPath);
                        $dump = $assetCollection->dump();
                        file_put_contents($lessPath, $dump);
                    } catch (Throwable $e) {
                        if (dev()) {
                            throw $e;
                        }
                        unlink($lessPath);
                        unlink($cachePath);
                    }
                }
                $cachePath = $lessPath;
            }

            /**
             * We cannot serve CSS over CDN because of enforced CORS same-origin policy.
             * @T00D00 - we CAN load it over CDN when we know user won't need to change it = most cases.
             */
            $link = str_replace(path('root'), path('ds'), $cachePath);
            if ($type === 'js' && config('pckg.manager.cdnEnabled') && !dotenv('DEV')) {
                $link = cdn($link);
            }

            $return[] = str_replace(['##LINK##', '##ID##'], [
                                                              $link,
                                                              'style-id-' . implode('-', $onlySections ?? ['all']),
                                                          ], $this->types[$type]);
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
                    $return[] = is_only_callable($asset) ? $asset() : $asset;
                }
            }
        }

        return $return;
    }

    /**
     * @param callable $callback
     * @return string
     */
    public function buildAsset(callable $callback, callable $setter = null)
    {
        /**
         * Retrieve content.
         */
        $string = $callback();

        /**
         * Build hash and cache.
         */
        $sha1 = sha1($string);
        $file = path('cache') . 'www/js/php_' . $sha1 . '.js';
        if (!is_file($file)) {
            file_put_contents($file, $string);
        }

        if (!$setter) {
            return str_replace('##LINK##', str_replace('/var/www/html/', '/', $file), $this->types['js']);
        }

        /**
         * Add to manager.
         */
        $setter($this, $file);

        return $this;
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
