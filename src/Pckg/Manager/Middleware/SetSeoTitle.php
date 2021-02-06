<?php

namespace Pckg\Manager\Middleware;

class SetSeoTitle
{

    public function execute(callable $next)
    {
        /**
         * Set title if not set yet.
         */
        $seoTitle = router()->get('tags')['seo:title'] ?? null;
        seoManager()->setTitle(($seoTitle ? $seoTitle . ' - ' : null) . config('site.title'));

        return $next();
    }
}
