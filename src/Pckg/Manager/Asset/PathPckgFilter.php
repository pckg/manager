<?php namespace Pckg\Manager\Asset;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\BaseNodeFilter;
use Assetic\Filter\DependencyExtractorInterface;
use Assetic\Filter\LessFilter;
use Assetic\Util\LessUtils;

class PathPckgFilter extends LessFilter
{

    public function filterLoad(AssetInterface $asset)
    {
        $source = $asset->getSourceRoot() . path('ds') . $asset->getSourcePath();
        $sourceHash = sha1($source . filemtime($source));
        $input = path('tmp') . $sourceHash . '.pathfilter.tmp';

        if (!is_file($input)) {
            $content = $asset->getContent();

            /**
             * Fix ../ and similar urls.
             */
            $sourceDir = $asset->getSourceRoot();
            $rootDir = path('root');

            /**
             * Source dir is always longer than root dir.
             * Dir diff will be prepended to urls.
             */
            $dirDiff = str_replace($rootDir, path('ds'), $sourceDir);

            /**
             * Make replacement.
             */
            $content = preg_replace_callback(
                '/url\(([\s])?([\"|\'])?(.*?)([\"|\'])?([\s])?\)/i',
                function($matches) use ($sourceDir, $rootDir, $dirDiff) {
                    $value = $matches[0];

                    /**
                     * Do not change inline data.
                     */
                    if (strpos($value, 'data:image/') !== false) {
                        return $value;
                    }

                    if (strpos($value, 'https://') !== false) {
                        return $value;
                    }

                    /**
                     * We have to move $dots times towards root.
                     */
                    if ($dots = substr_count($value, '../')) {
                        /**
                         * We have to move $dots times towards root.
                         */
                        //$resourcePathResolved = realpath($sourceDir . path('ds') . str_repeat('..' . path('ds'), $dots));
                        $resourcePathUnresolved = implode(
                            path('ds'), array_slice(
                                          explode(path('ds'), $sourceDir), 0,
                                          substr_count($sourceDir, path('ds')) -
                                          $dots + 1
                                      )
                        );
                        $relativeDir = str_replace($rootDir, path('ds'), $resourcePathUnresolved) . path('ds');

                        $urlPart = strpos($value, 'url("') !== false
                            ? 'url("'
                            : (strpos($value, 'url(\'') !== false
                                ? 'url(\'' : 'url(');
                        $value = str_replace(
                            $urlPart,
                            $urlPart . $relativeDir,
                            str_replace('../', '', $value)
                        );
                    }

                    /**
                     * We have to simply prepend path.
                     */
                    if (
                        (strpos($value, 'url("') !== false && strpos($value, 'url("/') === false) ||
                        (strpos($value, 'url(\'') !== false && strpos($value, 'url(\'/') === false)
                    ) {
                        $value = str_replace(
                            ['url("', 'url(\''],
                            ['url("' . $dirDiff . '/', 'url(\'' . $dirDiff . '/'],
                            $value
                        );
                    }

                    return $value;
                },
                $content
            );
        } else {
            $content = file_get_contents($input);
        }

        $asset->setContent($content);
    }

    public function filterDump(AssetInterface $asset)
    {
    }
}
