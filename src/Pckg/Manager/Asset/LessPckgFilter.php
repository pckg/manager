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

        $input = path('tmp') . $sourceHash . '.vars.less';

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
        $data = $source . filemtime($source) . $variablesPath;
        $sourceHash = sha1($data);
        $merged = path('tmp') . $sourceHash . '.merged.less';
        $css = path('tmp') . $sourceHash . '.parsed.css';
        $css2 = path('tmp') . $sourceHash . '.output.css';
        $failed = false;

        if (!is_file($css2)) {
            $mergedContent = ($variablesPath ? file_get_contents($variablesPath) : '') . file_get_contents($source);
            file_put_contents($merged, $mergedContent);

            $proc = new Process('lessc --js -x ' . $merged . ' > ' . $css);
            try {
                startMeasure('lessc');
                $code = $proc->run();
                stopMeasure('lessc');

                if (0 !== $code) {
                    throw FilterException::fromProcess($proc)->setInput($merged);
                }
            } catch (Throwable $e) {
                if (dev()) {
                    @rename($css, $css . '.err-less.' . microtime());
                    @rename($merged, $merged . '.err-less.' . microtime());
                    throw $e;
                }
                @unlink($css);
                @unlink($merged);
                $failed = true;
            }

            if (!$failed) {
                if (false) {
                    $proc = new Process('csso -i ' . $css . ' -o ' . $css2);
                    try {
                        startMeasure('css-purge');
                        $code = $proc->run();
                        stopMeasure('css-purge');

                        if (0 !== $code) {
                            throw FilterException::fromProcess($proc)->setInput($css);
                        }
                    } catch (Throwable $e) {
                        if (dev()) {
                            @rename($css, $css . '.err-purge.' . microtime());
                            @rename($css2, $css2 . '.err-purge.' . microtime());
                            @rename($merged, $merged . '.err-purge.' . microtime());
                            throw $e;
                        }
                        @unlink($css);
                        @unlink($css2);
                        @unlink($merged);
                        $failed = true;
                    }
                } else {
                    $css2 = $css;
                }
            }
        }

        $content = $failed ? null : file_get_contents($css2);

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
