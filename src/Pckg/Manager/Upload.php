<?php namespace Pckg\Manager;

use Pckg\Database\Helper\Convention;

class Upload
{

    protected $key;

    protected $uploadedFilename = null;

    protected $mime;

    const MIME_IMAGE = ['image/png', 'image/apng', 'image/gif', 'image/jpg', 'image/jpeg', 'image/svg+xml', 'image/webp'];

    const MIME_PDF = ['application/pdf'];

    const MIME_ZIP = ['application/zip'];

    const MIME_EXCEL = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

    const MODE_DATE_TREE = 'date:tree';

    public function __construct($key = 'file', $mime = [])
    {
        $this->key = $key ?? 'file';
        $this->mime = $mime;
    }

    public function getFile()
    {
        return $_FILES[$this->key] ?? [];
    }

    public function getContent()
    {
        return file_get_contents($this->getFile()['tmp_name']);
    }

    public function validateUpload()
    {
        $file = $this->getFile();

        if (!$file) {
            return 'No file uploaded ' . $this->key;
        }

        if ($file['error']) {
            return 'Error uploading file ' . $this->key;
        }

        if (!$file['size']) {
            return 'Empty file size ' . $this->key;
        }

        if ($this->mime) {
            $mimetype = mime_content_type($this->getFile()['tmp_name']);
            if (!in_array($mimetype, $this->mime)) {
                return 'Invalid mime type ' . $this->key;
            }
        }

        return true;
    }

    /**
     * @param $file
     * @return false|string
     */
    private function getExtension($file)
    {
        return substr($file, strrpos($file, '.'));
    }

    public function save($dir, $name = null, $mode = null)
    {
        $file = $this->getFile();

        if (!$file) {
            throw new \Exception('No file');
        }

        /**
         * When name is not fixed, convert it to safe name.
         */
        $fixedName = !!$name;
        if (!$name) {
            $name = Convention::url(substr($file['name'], 0, strrpos($file['name'], '.')));
        } else if (strpos($name, '..') !== false) {
            throw new \Exception('Invalid filename');
        } else if ($this->getExtension($file['name']) !== $this->getExtension($name)) {
            throw new \Exception('Invalid extension');
        }

        /**
         * Check for dated mode.
         * Add Y/m/d/ structure.
         */
        $dateTreeDir = '';
        if ($mode === static::MODE_DATE_TREE) {
            $dateTreeDir .= date('Y/m/d/');
            $dir .= $dateTreeDir;
        }

        /**
         * Check that file does not exist.
         */
        if (!$fixedName) {
            $extension = $this->getExtension($file['name']);
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
        return $this->uploadedFilename = $dateTreeDir . $filename;
    }

    public function getUploadedFilename()
    {
        return $this->uploadedFilename;
    }

}