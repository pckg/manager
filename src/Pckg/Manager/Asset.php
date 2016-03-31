<?php namespace Pckg\Manager;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Exception;
use Pckg\Manager\Asset\BaseAssets;

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

    public function addAssets($assets, $section = 'main')
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
                throw new Exception('Cannot find asset ' . $asset);
            }

            $collection->add(new FileAsset(path('root') . $asset));
        }
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
