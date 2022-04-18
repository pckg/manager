<?php

namespace Pckg\Manager\Upload;

use Pckg\Manager\Upload;

class Standard
{
    public function process($dir, $name, $mode, $fixedName, Upload $upload)
    {
        $file = $upload->getFile();

        /**
         * Check for dated mode.
         * Add Y/m/d/ structure.
         */
        $dateTreeDir = '';
        if ($mode === Upload::MODE_DATE_TREE) {
            $dateTreeDir = date('Y/m/d/');
            $dir .= $dateTreeDir;
        }

        /**
         * Check that file does not exist.
         */
        if (!$fixedName) {
            $extension = $upload->getExtension($file['name']);
            $i = 0;
            do {
                $filename = $name . ($i ? '_' . $i : '') . $extension;
                $i++;
            } while (is_file($dir . $filename));
        } else {
            $filename = $name;
        }

        /**
         * Make sure that uploads dir exists.
         */
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        /**
         * Make sure that custom dir exists.
         */
        if (strpos($filename, '/') !== false) {
            $exploded = explode('/', $filename);
            $dirs = implode('/', array_slice($exploded, 0, -1));
            $realdir = $dir . $dirs;
            if (!is_dir($realdir)) {
                mkdir($realdir, 0777, true);
            }
        }

        /**
         * Save file to final dir.
         */
        if (!file_exists($dir . $filename)) {
            $okay = move_uploaded_file($file['tmp_name'], $dir . $filename);
            if (!$okay) {
                throw new \Exception('Error moving uploaded file');
            }
        } elseif (false && sha1(file_get_contents($dir . $filename)) !== sha1(file_get_contents($file['tmp_name']))) {
            throw new \Exception('Delete existing file first');
        }

        /**
         * Set and return uploaded filename.
         */
        return $dateTreeDir . $filename;
    }
}
