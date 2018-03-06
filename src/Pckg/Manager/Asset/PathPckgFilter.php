<?php namespace Pckg\Manager\Asset;

use Assetic\Asset\AssetInterface;
use Assetic\Factory\AssetFactory;
use Assetic\Filter\BaseNodeFilter;
use Assetic\Filter\DependencyExtractorInterface;
use Assetic\Util\LessUtils;

class PathPckgFilter extends BaseNodeFilter implements DependencyExtractorInterface
{

    private $nodeBin;

    /**
     * @var array
     */
    private $treeOptions;

    /**
     * @var array
     */
    private $parserOptions;

    /**
     * Load Paths
     *
     * A list of paths which less will search for includes.
     *
     * @var array
     */
    protected $loadPaths = [];

    /**
     * Constructor.
     *
     * @param string $nodeBin   The path to the node binary
     * @param array  $nodePaths An array of node paths
     */
    public function __construct($nodeBin = '/usr/bin/node', array $nodePaths = [])
    {
        $this->nodeBin = $nodeBin;
        $this->setNodePaths($nodePaths);
        $this->treeOptions = [];
        $this->parserOptions = [];
    }

    /**
     * @param bool $compress
     */
    public function setCompress($compress)
    {
        $this->addTreeOption('compress', $compress);
    }

    public function setLoadPaths(array $loadPaths)
    {
        $this->loadPaths = $loadPaths;
    }

    /**
     * Adds a path where less will search for includes
     *
     * @param string $path Load path (absolute)
     */
    public function addLoadPath($path)
    {
        $this->loadPaths[] = $path;
    }

    /**
     * @param string $code
     * @param string $value
     */
    public function addTreeOption($code, $value)
    {
        $this->treeOptions[$code] = $value;
    }

    /**
     * @param string $code
     * @param string $value
     */
    public function addParserOption($code, $value)
    {
        $this->parserOptions[$code] = $value;
    }

    public function filterLoad(AssetInterface $asset)
    {
        $source = $asset->getSourceDirectory() . path('ds') . $asset->getSourcePath();
        $sourceHash = sha1($source . filemtime($source));
        $input = path('tmp') . $sourceHash . '.pathfilter.tmp';

        if (!is_file($input)) {
            $content = $asset->getContent();

            /**
             * Fix ../ and similar urls.
             */
            $sourceDir = $asset->getSourceDirectory();
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
                     * We have to move $dots times towards root.
                     */
                    if ($dots = substr_count($value, '../')) {
                        /**
                         * We have to move $dots times towards root.
                         */
                        //$resourcePathResolved = realpath($sourceDir . path('ds') . str_repeat('..' . path('ds'), $dots));
                        $resourcePathUnresolved = implode(path('ds'), array_slice(explode(path('ds'), $sourceDir), 0,
                                                                                  substr_count($sourceDir, path('ds')) -
                                                                                  $dots + 1));
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

    /**
     * @todo support for import-once
     * @todo support for import (less) "lib.css"
     */
    public function getChildren(AssetFactory $factory, $content, $loadPath = null)
    {
        $loadPaths = $this->loadPaths;
        if (null !== $loadPath) {
            $loadPaths[] = $loadPath;
        }

        if (empty($loadPaths)) {
            return [];
        }

        $children = [];
        foreach (LessUtils::extractImports($content) as $reference) {
            if ('.css' === substr($reference, -4)) {
                // skip normal css imports
                // todo: skip imports with media queries
                continue;
            }

            if ('.less' !== substr($reference, -5)) {
                $reference .= '.less';
            }

            foreach ($loadPaths as $loadPath) {
                if (file_exists($file = $loadPath . '/' . $reference)) {
                    $coll = $factory->createAsset($file, [], ['root' => $loadPath]);
                    foreach ($coll as $leaf) {
                        $leaf->ensureFilter($this);
                        $children[] = $leaf;
                        goto next_reference;
                    }
                }
            }

            next_reference:
        }

        return $children;
    }
}
