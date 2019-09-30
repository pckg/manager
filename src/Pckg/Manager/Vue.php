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

    private function parseComponent($component)
    {
        $parsed = view($component)->autoparse();

        return $parsed;

        $exploded = explode('<script type="text/javascript">', $parsed);
        if (count($exploded) === 2) {
            /**
             * We cannot dynamically load component, but we can cache them in separate js file for logic
             * and manually resolve template via xhr request.
             */
            $js = substr($exploded[1], 0, strpos($exploded[1], '</script>'));
            $explodedJs = explode("\n", trim($js));
            $explodedJs[0] = preg_replace('/{/', 'function(resolve) {
            http.getJSON(\'something.twig.xtemplate\', function (data) {
             * $(\'body\').append(data.vue);
             resolve({', $explodedJs[0], 1);
            foreach ($explodedJs as &$e) {
                if (strpos($e, 'template:') === false) {
                    continue;
                }
                $e = 'template: data.html, /* Pckg Vue Manager */';
            }
            $last = $explodedJs[count($exploded) - 1];
            $explodedJs[count($exploded) - 1] = '});}' . "\n" . $last;
            $js = implode("\n", $explodedJs);

            return $exploded[0];
        }

        return $parsed;
    }

    public function getViews()
    {
        $html = implode($this->views);

        return $html;
    }

    public function getLayout()
    {
        return '<keep-alive><router-view></router-view></keep-alive>';
    }

    public function getLayoutCallback()
    {
        return function() {
            return $this->getLayout();
        };
    }

}