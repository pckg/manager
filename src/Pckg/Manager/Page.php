<?php namespace Pckg\Manager;

class Page
{

    public function isHomepage()
    {
        return in_array(router()->getUri(), ['/', '/dev.php', '/dev.php/']);
    }

    public function getBreadcrumbs()
    {
        $breadcrumbs = [];

        /**
         * Dashboard is always displayed.
         */
        $breadcrumbs['/maestro'] = __('breadcrumbs.dashboard');

        /**
         * Check for table listing.
         */
        if ($table = router()->resolved('table')) {
            $breadcrumbs['/dynamic/tables/list/' . $table->id] = $table->title ?? $table->table;
        }

        /**
         * Add current page.
         */
        if (!array_key_exists(router()->getUri(), $breadcrumbs)) {
            $breadcrumbs[router()->getUri()] = __('breadcrumbs.current');
        }

        return $breadcrumbs;
    }

}