<?php namespace Pckg\Manager;

use Pckg\Database\Helper\Convention;

class Upload
{

    protected $key;

    protected $uploadedFilename = null;

    protected $mime;

    const MIME_IMAGE = ['image/png', 'image/apng', 'image/gif', 'image/jpg', 'image/jpeg', 'image/svg+xml', 'image/webp'];

    const MIME_PDF = ['application/pdf'];

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

    public function save($dir, $name = null)
    {
        $file = $this->getFile();

        if (!$file) {
            return false;
        }

        if (!$name) {
            $name = Convention::url(substr($file['name'], 0, strrpos($file['name'], '.')));
        }
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