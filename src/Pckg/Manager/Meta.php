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

    public function addCdnPrefetch()
    {
        $host = config('storage.cdn.host', null);

        if (!$host) {
            return;
        }

        $this->add('<link rel="dns-prefetch" href="https://' . $host . '">');

        return $this;
    }

    public function addGoogleAnalytics($trackingId)
    {
        if (!$trackingId) {
            return;
        }

        // window['ga-disable-UA-XXXXX-Y'] = true;
        $this->add(
            '<script>
  (function(i,s,o,g,r,a,m){i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,\'script\',\'//www.google-analytics.com/analytics.js\',\'ga\');

  ga(\'create\', \'' . $trackingId . '\', \'auto\');
  ga(\'set\', \'anonymizeIp\', true);
  ga(\'send\', \'pageview\');

</script>',
            'footer'
        );
    }

    public function addGoogleRecaptcha($siteKey)
    {
        if (!$siteKey) {
            return;
        }

        $this->add('<script src="https://www.google.com/recaptcha/api.js?onload=googleRecaptchaOnload" async defer></script>', 'footer');
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

    public function addFbPages($id)
    {
        if (!$id) {
            return;
        }

        $this->add('<meta property="fb:pages" content="' . $id . '" />', 'headerLast');
    }

    public function addFbChat($id)
    {
        if (!$id) {
            return;
        }

        $this->add('<!-- Load Facebook SDK for JavaScript -->
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = \'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js#xfbml=1&version=v2.12&autoLogAppEvents=1\';
  fjs.parentNode.insertBefore(js, fjs);
}(document, \'script\', \'facebook-jssdk\'));</script>

<!-- Your customer chat code -->
<div class="fb-customerchat"
  attribution=install_email
  page_id="' . $id . '"
  logged_in_greeting="' . htmlentities(config('external.facebookChat.greetingLoggedIn', 'Hi! How can we help you?')) . '"
  logged_out_greeting="' . htmlentities(config('external.facebookChat.greetingLoggedOut', 'Hi! How can we help you?')) . '">
</div>', 'body.first');
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

    public function addGoogleTagManager($id)
    {
        if (!$id) {
            return;
        }

        $this->add(
            '<script>(function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                \'gtm.start\': new Date().getTime(), event: \'gtm.js\'
            });
            var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s), dl = l != \'dataLayer\' ? \'&l=\' + l : \'\';
            j.async = true;
            j.src =
                    \'//www.googletagmanager.com/gtm.js?id=\' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, \'script\', \'dataLayer\', \'' . $id . '\');</script>',
            'header.first'
        );

        $this->add(
            '<noscript>
        <iframe src="//www.googletagmanager.com/ns.html?id=' . $id . '"
                height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>',
            'body.first'
        );
    }

    public function addGoogleRemarketingTag($id)
    {
        if (!$id) {
            return;
        }

        $this->add(
            '<!-- Google Code for Remarketing Tag -->
<script type="text/javascript">
    /* <![CDATA[ */
    var google_conversion_id = ' . $id . ';
    var google_custom_params = window.google_tag_params;
    var google_remarketing_only = true;
    /* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
    <div style="display:inline;">
        <img height="1" width="1" style="border-style:none;" alt=""
             src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/' . $id . '/?value=0&amp;guid=ON&amp;script=0"/>
    </div>
</noscript>
',
            'footer'
        );
    }

    public function addGoogleConversionPage($id)
    {
        if (!$id) {
            return;
        }

        return '<!-- Google Code for rezervacija Conversion Page -->
<script type="text/javascript">
    /* <![CDATA[ */
    var google_conversion_id = ' . $id . ';
    var google_conversion_language = "sl";

    var google_conversion_format = "2";
    var google_conversion_color = "ffffff";
    var google_conversion_label = "Yj6qCJLQtwUQkub-8QM";

    var google_conversion_value = 0;
    /* ]]> */
</script>
<script type="text/javascript"
        src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
    <div style="display:inline;">
        <img height="1" width="1" style="border-style:none;" alt=""
             src="//www.googleadservices.com/pagead/conversion/' . $id . '/?value=0&amp;label=Yj6qCJLQtwUQkub-8QM&amp;guid=ON&amp;script=0"/>
    </div>
</noscript>';
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
