<?php namespace Pckg\Manager;

class Meta
{

    protected $metas = [];

    public function addViewport()
    {
        $this->add(
            [
                'name'    => 'viewport',
                'content' => 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no',
            ]
        );

        return $this;
    }

    public function add($meta)
    {
        $this->metas[] = $meta;

        return $this;
    }

    public function addContentType()
    {
        $this->add(
            [
                'http-equiv' => 'Content-Type',
                'content'    => 'text/html; charset=utf-8',
            ]
        );

        return $this;
    }

    public function addGoogleAnalytics($trackingId)
    {
        $this->add(
            '<script>
  (function(i,s,o,g,r,a,m){i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,\'script\',\'//www.google-analytics.com/analytics.js\',\'ga\');

  ga(\'create\', \'' . $trackingId . '\', \'auto\');
  ga(\'send\', \'pageview\');

</script>'
        );
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
