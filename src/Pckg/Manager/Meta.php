<?php namespace Pckg\Manager;

class Meta
{

    protected $metas = [];

    public function addViewport()
    {
        $this->add([
            'name'    => 'viewport',
            'content' => 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no',
        ]);

        return $this;
    }

    public function add($meta)
    {
        $this->metas[] = $meta;

        return $this;
    }

    public function addContentType()
    {
        $this->add([
            'http-equiv' => 'Content-Type',
            'content'    => 'text/html; charset=utf-8',
        ]);

        return $this;
    }

    public function __toString()
    {
        $build = [];
        foreach ($this->metas as $meta) {
            if (is_string($meta)) {
                $build[] = $meta;

            } else {
                $partial = [];
                foreach ($meta as $key => $value) {
                    $partial[] = $key . '="' . htmlspecialchars($value) . '"';
                }
                $build[] = '<meta ' . implode(' ', $partial) . ' />';

            }
        }

        return implode("\n", $build);
    }

}
