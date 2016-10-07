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
        $this->summernote();
    }

    public function jQuery()
    {
        $this->addAssets(
            [
                "components/jquery/jquery.min.js",
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
                'bower_components/moment/min/moment-with-locales.min.js',
                'bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
                'bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',
            ],
            'libraries'
        );

        return $this;
    }

    public function vueJS()
    {
        $this->addAssets(
            [
                "bower_components/vue/dist/vue." . (dev() ? 'min.' : '') . "js",
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
                // "vendor/twbs/bootstrap/dist/css/bootstrap-theme.min.css",
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
                'bower_components/dropzone/dist/min/dropzone.min.css',
                'bower_components/dropzone/dist/min/dropzone.min.js',
            ],
            'libraries'
        );
    }

    public function summernote()
    {
        $this->addAssets(
            [
                'bower_components/summernote/dist/summernote.min.js',
                'bower_components/summernote/dist/summernote.css',
            ],
            'libraries'
        );
    }

}