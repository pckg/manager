<?php namespace Pckg\Manager;

use Pckg\Database\Helper\Convention;

class Upload
{

    protected $key;

    protected $uploadedFilename = null;

    public function __construct($key = 'file')
    {
        $this->key = $key;
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

        return true;
    }

    public function save($dir)
    {
        $file = $this->getFile();

        if (!$file) {
            return false;
        }

        $name = Convention::url(substr($file['name'], 0, strrpos($file['name'], '.')));
        $extension = substr($file['name'], strrpos($file['name'], '.'));
        $i = 0;
        do {
            $filename = $name . ($i ? '_' . $i : '') . $extension;
            $i++;
        } while (is_file($dir . $filename));

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        move_uploaded_file($file['tmp_name'], $dir . $filename);

        return $this->uploadedFilename = $filename;
    }

    public function getUploadedFilename()
    {
        return $this->uploadedFilename;
    }

}