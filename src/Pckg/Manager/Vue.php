<?php namespace Pckg\Manager;

class Vue
{

    protected $components = [];

    protected $views = [];

    public function addView($view, $data = [], $unique = false)
    {
        return $this->addStringView(view($view, $data)->autoparse(), $view . ($unique ? microtime() : ''));
    }

    public function addStringView($html, $index = null)
    {
        if ($index) {
            $this->views[$index] = $html;
        } else {
            $this->views[] = $html;
        }

        return $this;
    }

    public function addComponent($components)
    {
        if (!is_array($components)) {
            $components = [$components];
        }

        foreach ($components as $component) {
            $this->components[$component] = $component;
        }

        return $this;
    }

    public function getComponents()
    {
        $html = [];
        foreach ($this->components as $component) {
            $html[] = view($component)->autoparse();
        }

        $html = implode($html);

        /**
         * @T00D00 - we should parse output and cache javascript.
         */

        return $html;
    }

    public function getViews()
    {
        $html = implode($this->views);

        return $html;
    }

    public function getFilters()
    {
        return '';
    }

}