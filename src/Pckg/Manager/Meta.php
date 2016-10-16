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

    public function add($meta, $section = 'header')
    {
        $this->metas[$section][] = $meta;

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
        if (!$trackingId) {
            return;
        }

        $this->add(
            '<script>
  (function(i,s,o,g,r,a,m){i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,\'script\',\'//www.google-analytics.com/analytics.js\',\'ga\');

  ga(\'create\', \'' . $trackingId . '\', \'auto\');
  ga(\'send\', \'pageview\');

</script>',
            'footer'
        );
    }

    public function addTawkTo($id)
    {
        if (!$id) {
            return;
        }

        $this->add(
            '<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src=\'https://embed.tawk.to/' . $id . '/default\';
s1.charset=\'UTF-8\';
s1.setAttribute(\'crossorigin\',\'*\');
s0.parentNode.insertBefore(s1,s0);
})();
</script>',
            'footer'
        );
    }

    public function addSumoMe($id)
    {
        if (!$id) {
            return;
        }

        $this->add(
            '<script src="//load.sumome.com/" data-sumo-site-id="' . $id . '" async="async"></script>',
            'footer'
        );
    }

    public function addFbConversionPixel($id)
    {
        if (!$id) {
            return;
        }

        $this->add(
            '<!-- Facebook Pixel Code -->
        <script>
            !function (f, b, e, v, n, t, s) {
                if (f.fbq)return;
                n = f.fbq = function () {
                    n.callMethod ?
                            n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                };
                if (!f._fbq)f._fbq = n;
                n.push = n;
                n.loaded = !0;
                n.version = \'2.0\';
                n.queue = [];
                t = b.createElement(e);
                t.async = !0;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s)
            }(window,
                    document, \'script\', \'//connect.facebook.net/en_US/fbevents.js\');

            fbq(\'init\', \'' . $id . '\');
            fbq(\'track\', "PageView");
        </script>
        <noscript><img height="1" width="1" style="display:none"
                       src="https://www.facebook.com/tr?id=' . $id . '&ev=PageView&noscript=1"
            /></noscript>
        <!-- End Facebook Pixel Code -->',
            'headerLast'
        );
    }

    public function getMeta($onlySections = [])
    {
        if (!is_array($onlySections)) {
            $onlySections = [$onlySections];
        }

        $onlySections = $onlySections ?? array_keys($this->metas);
        $build = [];
        foreach ($onlySections as $section) {
            foreach ($this->metas[$section] ?? [] as $meta) {
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
        }

        return implode("\n", $build);
    }

    public function __toString()
    {
        return $this->getMeta();
    }

}
