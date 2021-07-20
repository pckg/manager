<?php

namespace Pckg\Manager;

use Derive\Internal\Cookie\Service\Cookie;

/**
 * Class Meta
 *
 * @package Pckg\Manager
 */
class Meta
{

    /**
     * @var array
     */
    protected $metas = [];

    /**
     * @param        $meta
     * @param string $section
     *
     * @return $this
     */
    public function add($meta, $section = 'header')
    {
        $this->metas[$section][] = $meta;

        return $this;
    }

    /**
     * @return $this
     */
    public function addViewport()
    {
        $this->add([
                       'name'    => 'viewport',
                       'content' => 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no',
                   ]);

        return $this;
    }

    public function addNoIndex()
    {
        $this->add(['name' => 'robots', 'content' => 'noindex,nofollow']);

        return $this;
    }

    /**
     *
     */
    public function addInternetExplorer()
    {
        $this->add(['http-equiv' => 'x-ua-compatible', 'content' => 'ie=edge']);

        return $this;
    }

    public function getCsrfValue()
    {
        /**
         * Min cost is 4.
         * Max cost is 31.
         * Default cost is 10.
         */
        return base64_encode(password_hash($this->getKeyForCSRF(), PASSWORD_DEFAULT, ['cost' => 5]));
    }

    public function addCSRF($name = null)
    {
        $value = $this->getCsrfValue();

        foreach (['pckgvdth', $name] as $key) {
            if (!$key) {
                continue;
            }
            $this->add(['name' => $key, 'content' => $value]);
        }

        return $this;
    }

    public function matchesCSRF($posted)
    {
        $decoded = base64_decode($posted);

        return password_verify($this->getKeyForCSRF(), $decoded) || password_verify($this->getKeyForCSRF(true), $decoded);
    }

    private function getKeyForCSRF($withUser = false)
    {
        $sessionId = session_id();
        $host = $_SERVER['HTTP_HOST'] ?? 'no-host';
        $userId = auth()->user('id') ?? 'no-user';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'no-agent';

        return $sessionId . ':' . $host . ':' . ($withUser ? $userId . ':' : '') . $agent;
    }

    /**
     * @return $this
     */
    public function addCharset()
    {
        $this->add([
                       'charset'    => 'utf-8',
                   ]);

        return $this;
    }

    /**
     * @return $this
     */
    public function addContentType()
    {
        $this->add([
            'http-equiv' => 'Content-Type',
            'content'    => 'text/html; charset=utf-8',
        ]);

        return $this;
    }

    /**
     * @return $this
     */
    public function addContentLanguage()
    {
        $this->add([
            'http-equiv' => 'Content-Language',
            'content'    => str_replace('_', '-', strtolower(localeManager()->getCurrent())),
        ]);

        return $this;
    }

    /**
     * Cookies are confirmed when
     *  - confirmed cookie exists
     *  - cookie notice is not enabled
     *  - cookie component is not set
     *
     * @return bool
     */
    public function hasConfirmedCookie()
    {
        return cookie('zekom', null) || !config('pckg.generic.modules.comms-cookie.active');
    }

    /**
     * @param        $script
     * @param string $section
     */
    public function addOnGdprAccept($script, $section = 'header')
    {
        if ($this->hasConfirmedCookie()) {
            $this->add('<script>' . $script . '</script>', $section);

            return;
        }

        $this->add('<script>
$dispatcher.$on(\'pckg-cookie:accepted\', function() {
    ' . $script . '
});
</script>', 'footer');
    }

    /**
     * @param        $html
     * @param string $section
     */
    public function addHtmlOnGdprAccept($html, $section = 'header')
    {
        if ($this->hasConfirmedCookie()) {
            $this->add($html, $section);

            return;
        }

        $this->add('<script>
$dispatcher.$on(\'pckg-cookie:accepted\', function() {
    $(\'body\').append(' . json_encode($html) . ');
});
</script>', 'footer');
    }

    /**
     * @param $script
     * @param $attributes
     * @param $section
     */
    public function addExternalScriptOnGdprAccept($script, $attributes = ['async' => true], $section = 'footer')
    {
        $finalAttrs = [];
        foreach ($attributes as $k => $v) {
            if ($k === strtolower($k)) {
                $finalAttrs[] = 's1.' . $k . ' = ' . json_encode($v) . ';';
                continue;
            }

            $finalAttrs[] = 's1.' . $k . '(' . $v . ');';
        }

        $script = '
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.src = ' . json_encode($script) . ';
' . implode("\n", $finalAttrs) . '
s0.parentNode.insertBefore(s1,s0);
})();';
        $this->addOnGdprAccept($script, $section);
    }

    /**
     * @param $trackingId
     */
    public function addCdnPrefetch()
    {
        $host = config('storage.cdn.host', null);

        /**
         * CDN host.
         */
        if ($host) {
            // $this->add('<link rel="dns-prefetch" href="https://' . $host . '" crossorigin>');
            $this->add('<link rel="preconnect" href="https://' . $host . '" crossorigin>');
        }

        /**
         * Google fonts, maps and other APIs.
         */
        $this->addPreconnect('https://fonts.googleapis.com');
        $this->addPreconnect('https://fonts.gstatic.com');

        return $this;
    }

    public function addPreconnect($url)
    {
        $this->add('<link rel="preconnect" href="' . $url . '" crossorigin>');
        return $this;
    }

    public function addGoogleAnalytics($trackingId)
    {
        if (!$trackingId) {
            return;
        }

        /**
         * New GA4.
         */
        if (strpos($trackingId, 'G-') === 0) {
            $this->addExternalScriptOnGdprAccept('https://www.googletagmanager.com/gtag/js?id=' . $trackingId);
            $this->addOnGdprAccept('window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);};
  
  window.oldGa = function() {
    console.log(\'Backwards compatibility GA layer activated\'); 
    if (arguments[0] === \'send\') {
        gtag(\'event\', arguments[1]);
    } else if (arguments[0] === \'set\') {
    gtag(\'event\', \'page_view\', {
  page_title: \'\',
  page_location: location.origin + arguments[2],
  page_path: arguments[2]
});
    } else { console.log(\'Missing gtag-ga\', arguments) }
  }
  
  gtag(\'js\', new Date());
  gtag(\'config\', ' . json_encode($trackingId) . ', { \'anonymize_ip\': true });
  gtag(\'set\', \'allow_google_signals\', false);
  gtag(\'set\', \'allow_ad_personalization_signals\', false);');

            $this->addOnGdprAccept('(function(){
        $dispatcher.$on(\'extension:third-party:google:analytics\', function(payload) {
            gtag(...payload);
        });
        })()', 'footer');
            return;
        }

        /**
         * We want to load this only if cookie policy is disabled or confirmed.
         * When cookie notice is enabled and not accepted we add this to cookie callback.
         */
        $this->addOnGdprAccept('(function(i,s,o,g,r,a,m){i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,\'script\',\'https://www.google-analytics.com/analytics.js\',\'ga\');

  ga(\'create\', \'' . $trackingId . '\', \'auto\');
  ga(\'set\', \'allowAdFeatures\');
  ga(\'set\', \'anonymizeIp\', true);
  ga(\'send\', \'pageview\');', 'footer');

        $this->addOnGdprAccept('(function(){
        $dispatcher.$on(\'extension:third-party:google:analytics\', function(payload) {
            ga(...payload);
        });
        })()', 'footer');
    }

    /**
     * @param $siteKey
     */
    public function addGoogleRecaptcha($siteKey)
    {
        if (!$siteKey) {
            return;
        }

        $this->add(
            '<script src="https://www.google.com/recaptcha/api.js?onload=googleRecaptchaOnload&render=explicit" async defer></script>',
            'footer'
        );
    }

    /**
     * @param $id
     */
    public function addTawkTo($id)
    {
        if (!$id) {
            return;
        }

        $this->addOnGdprAccept('if (typeof Tawk_API === \'undefined\') { window.Tawk_API = {}; window.Tawk_LoadStart = new Date(); }');
        $this->addExternalScriptOnGdprAccept('https://embed.tawk.to/' . $id . '/default', [
            'async'        => true,
            'charset'      => 'UTF-8',
            'setAttribute' => '\'crossorigin\', \'*\'',
        ]);
    }

    /**
     * @param $id
     */
    public function addHotJar($id)
    {
        if (!$id) {
            return;
        }

        $code = '(function(h,o,t,j,a,r){
        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
        h._hjSettings={hjid:' . $id . ',hjsv:6};
        a=o.getElementsByTagName(\'head\')[0];
        r=o.createElement(\'script\');r.async=1;
        r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
        a.appendChild(r);
    })(window,document,\'https://static.hotjar.com/c/hotjar-\',\'.js?sv=\');';

        $this->addOnGdprAccept($code, 'footer');
    }

    /**
     * @param $id
     */
    public function addSumoMe($id)
    {
        if (!$id) {
            return;
        }

        $this->addExternalScriptOnGdprAccept('https://load.sumome.com/', [
            'setAttribute' => '\'data-sumo-site-id\', \'' . $id . '\'',
            'async'        => true,
        ]);
    }

    /**
     * @param $id
     */
    public function addFbPages($id)
    {
        if (!$id) {
            return;
        }

        $this->add('<meta property="fb:pages" content="' . $id . '">', 'headerLast');
    }

    /**
     * @param $id
     */
    public function addFbChat($id)
    {
        if (!$id) {
            return;
        }

        $this->addHtmlOnGdprAccept('<div id="fb-root"></div>', 'body.first');
        $this->addHtmlOnGdprAccept('<div class="fb-customerchat"
  attribution="biz_inbox"
  page_id="' . $id . '"
  logged_in_greeting="' . htmlentities(config('external.facebookChat.greetingLoggedIn', 'Hi! How can we help you?')) . '"
  logged_out_greeting="' . htmlentities(config('external.facebookChat.greetingLoggedOut', 'Hi! How can we help you?')) . '">
</div>', 'body.first');

        $this->addOnGdprAccept('
      window.fbAsyncInit = function() {
        FB.init({
          xfbml            : true,
          version          : \'v11.0\'
        });
      };
        
        (function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = \'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js\';
  fjs.parentNode.insertBefore(js, fjs);
}(document, \'script\', \'facebook-jssdk\'));', 'body.first');
    }

    /**
     * @param $id
     */
    public function addFbConversionPixel($id)
    {
        if (!$id) {
            return;
        }

        $this->addOnGdprAccept('!function (f, b, e, v, n, t, s) {
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
                    document, \'script\', \'https://connect.facebook.net/en_US/fbevents.js\');

            fbq(\'init\', \'' . $id . '\');
            fbq(\'track\', "PageView");', 'headerLast');

        $this->addHtmlOnGdprAccept('<noscript><img height="1" width="1" style="display:none"
                       src="https://www.facebook.com/tr?id=' . $id . '&ev=PageView&noscript=1"
            /></noscript>', 'headerLast');
    }

    /**
     * @param $id
     */
    public function addGoogleTagManager($id)
    {
        if (!$id) {
            return;
        }

        $this->addOnGdprAccept('(function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                \'gtm.start\': new Date().getTime(), event: \'gtm.js\'
            });
            var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s), dl = l != \'dataLayer\' ? \'&l=\' + l : \'\';
            j.async = true;
            j.src =
                    \'https://www.googletagmanager.com/gtm.js?id=\' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, \'script\', \'dataLayer\', \'' . $id . '\');', 'header');

        $this->addHtmlOnGdprAccept('<noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=' . $id . '"
                height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>', 'body.first');
    }

    /**
     * @param $id
     */
    public function addGoogleRemarketingTag($id)
    {
        if (!$id) {
            return;
        }

        $this->addOnGdprAccept('var google_conversion_id = window.google_conversion_id = ' . $id . ';
    var google_custom_params = window.google_custom_params = window.google_tag_params;
    var google_remarketing_only = window.google_remarketing_only = true;', 'footer');

        $this->addExternalScriptOnGdprAccept('https://www.googleadservices.com/pagead/conversion.js');

        $this->addHtmlOnGdprAccept('<noscript>
    <div style="display:inline;">
        <img height="1" width="1" style="border-style:none;" alt=""
             src="https://googleads.g.doubleclick.net/pagead/viewthroughconversion/' . $id . '/?value=0&amp;guid=ON&amp;script=0"/>
    </div>
</noscript>', 'footer');
    }

    /**
     * @param $id
     */
    public function addGoogleConversionPage($id)
    {
        if (!$id) {
            return;
        }

        $this->addOnGdprAccept('var google_conversion_id = window.google_conversion_id = ' . $id . ';
    var google_conversion_language = window.google_conversion_language = "sl";
    var google_conversion_format = window.google_conversion_format = "2";
    var google_conversion_color = window.google_conversion_color = "ffffff";
    var google_conversion_label = window.google_conversion_label = "Yj6qCJLQtwUQkub-8QM";
    var google_conversion_value = window.google_conversion_value = 0;', 'footer');

        $this->addExternalScriptOnGdprAccept('https://www.googleadservices.com/pagead/conversion.js');

        $this->addHtmlOnGdprAccept('<noscript>
    <div style="display:inline;">
        <img height="1" width="1" style="border-style: none;" alt=""
             src="//www.googleadservices.com/pagead/conversion/' . $id . '/?value=0&amp;label=Yj6qCJLQtwUQkub-8QM&amp;guid=ON&amp;script=0"/>
    </div>
</noscript>', 'footer');
    }

    /**
     * @param array $onlySections
     *
     * @return string
     */
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
                        if ($key === '__tag') {
                            continue;
                        }
                        $partial[] = $key . '="' . htmlspecialchars($value) . '"';
                    }
                    $build[] = '<' . ($meta['__tag'] ?? 'meta') . ' ' . implode(' ', $partial) . '>';
                }
            }
        }

        return implode("\n", $build);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getMeta();
    }
}
