<?php

namespace Pckg\Manager;

use Pckg\Generic\Controller\Generic;

/**
 * Class Page
 * @package Pckg\Manager
 * @T00D00 - move to pckg/generic
 */
class Page
{

    public function isHomepage()
    {
        return in_array(router()->getUri(), ['/', '/dev.php', '/dev.php/']);
    }

    public function isGeneric()
    {
        /**
         * @phpstan-ignore-next-line
         */
        return request()->getMatch('controller') == Generic::class && request()->getMatch('view') == 'generic';
    }

    public function hasAction(array $actions)
    {
        $genericService = $this->getGenericService();

        return $genericService && $genericService->hasAction($actions);
    }

    /**
     * @return \Pckg\Generic\Service\Generic|null
     * @throws \Exception
     */
    public function getGenericService()
    {
        /**
         * @phpstan-ignore-next-line
         */
        return resolve(\Pckg\Generic\Service\Generic::class);
    }

    public function getBreadcrumbs()
    {
        $breadcrumbs = [];

        /**
         * Dashboard is always displayed.
         */
        $breadcrumbs['/maestro'] = __('breadcrumbs.dashboard');

        /**
         * Check for related table.
         */
        if ($relation = router()->resolved('relation')) {
            $relatedTable = $relation->onTable;
            $breadcrumbs['/dynamic/tables/list/' . $relatedTable->id] = $relatedTable->title ?? $relatedTable->table;

            /**
             * Check for foreign record.
             */
            if ($foreign = router()->resolved('foreign')) {
                $breadcrumbs['/dynamic/records/view/' . $relatedTable->id . '/' . $foreign->id] = ($relatedTable->title
                                                                                                   ??
                                                                                                   $relatedTable->table) .
                                                                                                  ' #' . $foreign->id;
            }
        }

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

    public function getHomepageUrl()
    {
        $url = config('platform.mainUrl', '/');

        return $url;
    }
}
