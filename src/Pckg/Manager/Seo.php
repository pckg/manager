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
        $this->setTitle(config('site.contact.name'));
    }

    public function __toString()
    {
        return (
               $this->title
                   ? str_replace('##', trim(strip_tags($this->title)), $this->templates['title']) . "\n"
                   : ''
               ) . (
               $this->description
                   ? str_replace('##', trim(strip_tags($this->description)), $this->templates['description']) . "\n"
                   : ''
               ) . (
               $this->keywords
                   ? str_replace('##', trim(strip_tags($this->keywords)), $this->templates['keywords']) . "\n"
                   : ''
               )
               . $this->getOgTags()
               . $this->getFaviconTags();
    }

    public function getFaviconTags()
    {
        if (!$this->favicon) {
            return null;
        }

        return '<link rel="icon" type="image/png" href="' . $this->favicon . '" />
    <link rel="shortcut icon" href="' . $this->favicon . '" />';
    }

    public function getOgTags()
    {
        $image = $this->image
            ? htmlspecialchars(config('url') . str_replace(' ', '%20', $this->image))
            : '';
        $title = trim(strip_tags($this->title));
        $description = trim(strip_tags($this->description));

        return '<meta property="og:title" content="' . $title . '" />
		<meta property="og:site_name" content="' . $title . '" />
		<meta property="og:description" content="' . $description . '" />
		<meta property="og:type" content="website" />
		<meta property="og:url" content="' . router()->getUri(false) . '" />
		<meta property="fb:admins" content="1197210626" />
		<meta property="fb:app_id" content="' . config('pckg.auth.provider.facebook.config.app_id') . '" />
		' . ($image ? '<meta property="og:image" content="' . $image . '" />' : '');
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function setDescription($description)
    {
        $this->description = trim(strip_tags($description));

        return $this;
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
