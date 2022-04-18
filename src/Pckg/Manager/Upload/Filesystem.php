<?php

namespace Pckg\Manager\Upload;

use Pckg\Manager\Upload;
use Pckg\Storage\Filesystem as FilesystemStorage;

class Filesystem
{
    public function process($dir, $name, $mode, $fixedName, Upload $upload)
    {
        $file = $upload->getFile();

        $dateTreeDir = '';
        if ($mode === Upload::MODE_DATE_TREE) {
            $dateTreeDir = date('Y/m/d/');
            $dir .= $dateTreeDir;
        }

        resolve(FilesystemStorage::class)
            ->detectFileFromPath($dir . $dateTreeDir . $name) // shortcut for ->storage('foo')->dir('bar')->file('baz')
            ->writeFromLocalFile($file['tmp_name']);

        unlink($file['tmp_name']);

        return $dateTreeDir . $name;
    }
}
