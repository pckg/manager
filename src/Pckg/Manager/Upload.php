<?php

namespace Pckg\Manager;

use Pckg\Database\Helper\Convention;
use Pckg\Manager\Upload\Filesystem;
use Pckg\Manager\Upload\Standard;

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
    public function getExtension($file)
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
            // throw new \Exception('Invalid extension'); // why commented out? lowercase/uppercase?
        }

        return $this->uploadedFilename = $this->getHandler()->process($dir, $name, $mode, $fixedName, $this);
    }

    public function getHandler(): Standard|Filesystem
    {
        return resolve(config('pckg.manager.upload.handler', Standard::class));
    }

    public function getUploadedFilename()
    {
        return $this->uploadedFilename;
    }
}
