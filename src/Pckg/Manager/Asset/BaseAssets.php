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
                "vendor/components/jquery/jquery.min.js",
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
                "node_modules/vue/dist/vue.min.js",
                'node_modules/vuex/dist/vuex.min.js',
                'node_modules/vue-router/dist/vue-router.min.js',
                'node_modules/sortablejs/Sortable.min.js',
                'node_modules/vuedraggable/dist/vuedraggable.umd.min.js',
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
                "vendor/snapappointments/bootstrap-select/dist/js/bootstrap-select.min.js",
                "vendor/twbs/bootstrap/dist/css/bootstrap.min.css",
                "vendor/snapappointments/bootstrap-select/dist/css/bootstrap-select.min.css",
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
        /**
         * Support Font Awesome PRO.
         */
        $pro = 'node_modules/@fortawesome/fontawesome-pro/css/all.min.css';
        if (is_file(path('root') . $pro)) {
            $this->addAssets([$pro], 'footer');

            return $this;
        }

        /**
         * Use CDN instead.
         */
        $this->addAssets(['https://use.fontawesome.com/releases/v5.8.0/css/all.css'], 'footer');

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
                'https://maps.googleapis.com/maps/api/js?key=AIzaSyBCHbpY1ILUr8UxuXHVILfXbjXQ1fX7-fA&libraries=places',
            ],
            'libraries'
        );
    }

}