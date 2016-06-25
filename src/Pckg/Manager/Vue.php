<?php namespace Pckg\Manager;

class Vue
{

    protected $components = [];

    public function addVueComponent($components) {
        if (!is_array($components)) {
            $components = [$components];
        }

        foreach ($components as $component) {
            $this->components[$component] = $component;
        }

        return $this;
    }

    public function getComponents() {
        $html = [];
        foreach ($this->components as $component) {
            $html[] = view($component);
        }

        return implode($html);
    }

}