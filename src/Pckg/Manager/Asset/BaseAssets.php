<?php namespace Pckg\Manager\Asset;

trait BaseAssets
{

    public function execute()
    {
        // dependencies
        $this->jQuery();
        $this->vueJSSimple();

        // front framework
        $this->bootstrap();
    }

    public function executeCore()
    {
        // dependencies
        $this->jQuery();
        $this->vueJS();

        // front framework
        $this->bootstrap();
        $this->magnific();

        // file upload
        $this->dropzone();

        // editor
        $this->tinymce();

        // gmaps
        $this->gmaps();
    }

    public function jQuery()
    {
        $this->addAssets(
            [
                "vendor/components/jquery/jquery.min.js",
            ],
            'libraries'
        );

        return $this;
    }

    public function vueJS()
    {
        $this->addAssets(
            [
                "node_modules/vue/dist/vue.min.js",
                //'node_modules/vuex/dist/vuex.min.js',
                // 'node_modules/vue-router/dist/vue-router.min.js',
                'node_modules/sortablejs/Sortable.min.js',
                'node_modules/vuedraggable/dist/vuedraggable.umd.min.js',
            ],
            'libraries'
        );

        return $this;
    }

    public function vueJSSimple()
    {
        $this->addAssets(
            [
                "node_modules/vue/dist/vue.min.js",
            ],
            'libraries'
        );

        return $this;
    }

    public function bootstrap()
    {
        $this->addAssets(
            [
                "vendor/twbs/bootstrap/dist/js/bootstrap.min.js",
                "vendor/twbs/bootstrap/dist/css/bootstrap.min.css",
            ],
            'libraries'
        );

        return $this;
    }

    public function magnific()
    {
        $this->addAssets(
            [
                'vendor/dimsemenov/magnific-popup/dist/jquery.magnific-popup.min.js',
                'vendor/dimsemenov/magnific-popup/dist/magnific-popup.css',
            ],
            'libraries'
        );
    }

    public function dropzone()
    {
        $this->addAssets(
            [
                'node_modules/dropzone/dist/min/dropzone.min.css',
                'node_modules/dropzone/dist/min/dropzone.min.js',
            ],
            'libraries'
        );
    }

    public function tinymce()
    {
        $this->addAssets(
            [
                'node_modules/tinymce/tinymce.min.js',
            ],
            'libraries'
        );
    }

    public function gmaps()
    {
        $this->addAssets(
            [
                'https://maps.googleapis.com/maps/api/js?key=AIzaSyBCHbpY1ILUr8UxuXHVILfXbjXQ1fX7-fA&libraries=places',
            ],
            'libraries'
        );
    }

}