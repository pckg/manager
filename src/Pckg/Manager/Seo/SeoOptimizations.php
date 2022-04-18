<?php

namespace Pckg\Manager\Seo;

trait SeoOptimizations
{
    public function getSeoTitle()
    {
        return config('site.title') . ' - ' . $this->title;
    }

    public function getSeoDescription()
    {
        return $this->description;
    }

    public function getSeoImage()
    {
        return $this->image;
    }
}
