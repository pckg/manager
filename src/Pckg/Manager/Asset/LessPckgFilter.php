<?php namespace Pckg\Manager\Asset;

use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;
use Assetic\Filter\LessFilter;
use Pckg\Concept\Reflect;
use Pckg\Manager\Asset;
use Symfony\Component\Process\Process;
use Throwable;

class LessPckgFilter extends LessFilter
{

    protected $varsHash = null;

    protected $varsPath = null;

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
        $source = $asset->getSourceRoot() . path('ds') . $asset->getSourcePath();
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

            $command = 'lessc' . config('lessc', null) . ' ' . $merged . ' > ' . $css;
            //$proc = new Process($command);
            try {
                startMeasure('lessc');
                exec($command);
                // $code = $proc->run();
                stopMeasure('lessc');

                /*if (0 !== $code) {
                    throw FilterException::fromProcess($proc)->setInput($merged);
                }*/
            } catch (Throwable $e) {
                if (dev()) {
                    @rename($css, $css . '.err-less');
                    @rename($merged, $merged . '.err-less');
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
}
