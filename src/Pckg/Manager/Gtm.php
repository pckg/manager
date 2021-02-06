<?php

namespace Pckg\Manager;

use Throwable;

class Gtm
{

    protected $dataLayer = [];

    public function addDataLayer($layer)
    {
        $this->dataLayer[] = $layer;
    }

    public function getGtm()
    {
        if (!$this->dataLayer) {
            return '';
        }

        $string = '<script>dataLayer = ' . json_encode($this->dataLayer, JSON_NUMERIC_CHECK) . ';</script>';

        return $string;
    }

    public function __toString()
    {
        try {
            return $this->getGtm();
        } catch (Throwable $e) {
            return '';
        }
    }
}
