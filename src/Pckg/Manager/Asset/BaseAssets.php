<?php namespace Pckg\Manager\Asset;

trait BaseAssets
{

    public function execute()
    {
        // dependencies
        $this->jQuery();
        $this->jQueryDatetimePicker();
        $this->vueJS();

        // front framework
        $this->bootstrap();
        $this->magnific();

        // decoration
        $this->fontAwesome();
    }

    public function executeCore()
    {
        // dependencies
        $this->jQuery();
        $this->jQueryDatetimePicker();
        $this->vueJS();

        // front framework
        $this->bootstrap();
        $this->magnific();

        // decoration
        $this->fontAwesome();

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
                "components/jquery/jquery.min.js",
                'vendor/pckg/framework/src/Pckg/Framework/public/js/serializeObject.jquery.js',
            ],
            'libraries'
        );

        return $this;
    }

    /**
     * @return $this
     *
     * bower install eonasdan-bootstrap-datetimepicker#latest --save
     */
    public function jQueryDatetimePicker()
    {
        $this->addAssets(
            [
                'node_modules/moment/min/moment-with-locales.min.js',
                'node_modules/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
                'node_modules/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',
            ],
            'libraries'
        );

        return $this;
    }

    public function vueJS()
    {
        $this->addAssets(
            [
                "node_modules/vue/dist/vue." . (!dev() ? 'min.' : '') . "js",
                'node_modules/sortablejs/Sortable.js',
                'node_modules/vuedraggable/dist/vuedraggable.js',
                'node_modules/vuex/dist/vuex.js',
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
                "vendor/bootstrap-select/bootstrap-select/js/bootstrap-select.js",
                "vendor/twbs/bootstrap/dist/css/bootstrap.min.css",
                //"vendor/twbs/bootstrap/dist/css/bootstrap-theme.min.css",
                "vendor/bootstrap-select/bootstrap-select/dist/css/bootstrap-select.css",
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

    public function fontAwesome()
    {
        $this->addAssets(
            [
                "vendor/fortawesome/font-awesome/css/font-awesome.min.css",
            ]
        );

        return $this;
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
                'https://maps.googleapis.com/maps/api/js?key=AIzaSyBCHbpY1ILUr8UxuXHVILfXbjXQ1fX7-fA&sensor=false&libraries=places',
            ],
            'libraries'
        );
    }

}