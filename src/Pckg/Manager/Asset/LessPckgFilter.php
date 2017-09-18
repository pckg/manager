<?php namespace Pckg\Manager\Asset;

use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;
use Assetic\Factory\AssetFactory;
use Assetic\Filter\BaseNodeFilter;
use Assetic\Filter\DependencyExtractorInterface;
use Assetic\Util\LessUtils;
use Pckg\Concept\Reflect;
use Pckg\Manager\Asset;
use Symfony\Component\Process\Process;
use Throwable;

class LessPckgFilter extends BaseNodeFilter implements DependencyExtractorInterface
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

    protected $varsHash = null;

    protected $varsPath = null;

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

    public function getVarsHash()
    {
        if (!$this->varsHash) {
            $filemtimes = [];
            $lessVars = context()->get(Asset::class);
            $variableFiles = $lessVars->getLessVariableFiles();
            foreach ($variableFiles as $file) {
                if (strpos($file, '@', 1)) {
                    $explode = explode('@', $file);
                    $result = Reflect::method(Reflect::create($explode[0]), $explode[1]);
                    $filemtimes[] = sha1($result);
                } else {
                    $filemtimes[] = filemtime($file);
                }
            }

            $this->varsHash = sha1(json_encode($variableFiles) . json_encode($filemtimes));
        }

        return $this->varsHash;
    }

    private function getVarsPath()
    {
        if ($this->varsPath) {
            return $this->varsPath;
        }

        $lessVars = context()->get(Asset::class);
        $variableFiles = $lessVars->getLessVariableFiles();

        if (!$variableFiles) {
            return null;
        }

        $sourceHash = $this->getVarsHash();

        $input = path('tmp') . $sourceHash . '.lessVars.tmp';

        if (!is_file($input)) {
            $content = '';
            foreach ($variableFiles as $file) {
                if (strpos($file, '@', 1)) {
                    $explode = explode('@', $file);
                    $content .= Reflect::method(Reflect::create($explode[0]), $explode[1]);
                } else {
                    $content .= file_get_contents($file);
                }
            }
            file_put_contents($input, $content);
        }

        $this->varsPath = $input;

        return $this->varsPath;
    }

    public function filterLoad(AssetInterface $asset)
    {
        $source = $asset->getSourceDirectory() . path('ds') . $asset->getSourcePath();
        $variablesPath = $this->getVarsPath();
        $sourceHash = sha1($source . filemtime($source) . $variablesPath);
        $output = path('tmp') . $sourceHash . '.lessVars.tmp';
        $input = path('tmp') . $sourceHash . '.merged.less.tmp';
        $failed = false;

        if (!is_file($output)) {
            file_put_contents(
                $input,
                file_get_contents($source) . ($variablesPath ? file_get_contents($variablesPath) : '')
            );

            $proc = new Process('lessc ' . $input . ' > ' . $output);
            try {
                $code = $proc->run();

                if (0 !== $code) {
                    throw FilterException::fromProcess($proc)->setInput($asset->getContent());
                }
            } catch (Throwable $e) {
                if (dev()) {
                    throw $e;
                }
                unlink($output);
                unlink($input);
                $failed = true;
            }
        }

        $content = $failed ? null : file_get_contents($output);

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
