<?php namespace Pckg\Manager\Seo;

trait SeoOptimizations
{

    public function getSeoTitle()
    {
        return $this->title . ' @ ' . config('site.contact.name');
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