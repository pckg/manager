<?php namespace Pckg\Manager;

use Pckg\Manager\Seo\SeoOptimized;

class Seo
{

    protected $templates = [
        'title'       => '<title>##</title>',
        'description' => '<meta name="description" content="##" />',
        'keywords'    => '<meta name="keywords" content="##" />',
    ];

    protected $title;

    protected $description;

    protected $keywords;

    protected $image;

    protected $favicon;

    protected $objects = [];

    public function __construct()
    {
        $this->setTitle(config('site.title'));
    }

    public function __toString()
    {
        return $this->getSeoTags()
               . $this->getOgTags()
               . $this->getFaviconTags();
    }

    public function getSeoTags() {
        return (
            $this->title
                ? str_replace('##', $this->title, $this->templates['title']) . "\n"
                : ''
            ) . (
            $this->description
                ? str_replace('##', $this->description, $this->templates['description']) . "\n"
                : ''
            ) . (
            $this->keywords
                ? str_replace('##', $this->keywords, $this->templates['keywords']) . "\n"
                : ''
            );
    }

    public function getFaviconTags()
    {
        if (!$this->favicon) {
            return null;
        }

        return '<link defer rel="icon" type="image/png" href="' . $this->favicon . '" />
<link defer rel="shortcut icon" href="' . $this->favicon . '" />';
    }

    public function getOgTags()
    {
        $image = cdn($this->image);
        $title = $this->title; // already encoded
        $description = $this->description; // already encoded
        $appId = config('pckg.auth.provider.facebook.config.app_id');
        $siteName = htmlspecialchars(config('site.contact.name'));

        $og = '<meta property="og:title" content="' . $title . '" />
<meta property="og:description" content="' . $description . '" />
<meta property="og:site_name" content="' . $siteName . '" />
<meta property="og:type" content="website" />
<meta property="og:url" content="' . router()->getUri(false) . '" />
<meta property="og:locale" content="' . localeManager()->getCurrent() . '" />' .
            ($appId ? "\n" . '<meta property="fb:app_id" content="' . $appId . '" />' : '') .
            ($image ? "\n" . '<meta property="og:image" content="' . $image . '" />' : '');

        $twitter = '<meta property="twitter:title" content="' . $title . '" />
<meta property="twitter:description" content="' . $description . '" />' .
            ($image ? "\n" . '<meta property="twitter:image:src" content="' . $image . '" />' : '');

        return $og . $twitter;
    }

    public function setTitle($title)
    {
        $this->title = trim(htmlspecialchars(strip_tags($title)));

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setDescription($description)
    {
        $this->description = trim(htmlspecialchars(strip_tags($description)));

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setFavicon($favicon)
    {
        $this->favicon = $favicon;

        return $this;
    }

    public function addObject(SeoOptimized $object)
    {
        $this->objects[] = $object;

        $data = ['title', 'description', 'image'];
        foreach ($data as $key) {
            $value = $object->{'getSeo' . ucfirst($key)}();
            if ($value) {
                $this->{'set' . ucfirst($key)}($value);
            }
        }

        return $this;
    }

}
