<?php namespace Pckg\Manager\Asset;

trait BaseAssets
{

    public function execute()
    {
        // dependencies
        $this->jQuery();
        $this->jQueryDatetimePicker();
        $this->angularJS();
        $this->vueJS();

        // front framework
        $this->bootstrap();
        $this->magnific();

        // needed
        $this->app();
        $this->maestro();
        $this->theme();

        // decoration
        $this->fontAwesome();
        $this->redactor();
        $this->chosen();
        $this->datatables();
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

    public function jQueryDatetimePicker()
    {
        /*$this->addAssets([
            "vendor/lfw/admin/src/Weblab/Admin/public/js/bootstrap-datetimepicker/v1/min.js",
            "vendor/lfw/admin/src/Weblab/Admin/public/css/bootstrap-datetimepicker/v1/dev.css",
        ]);*/

        return $this;
    }

    public function angularJS()
    {
        /*$this->addAssets([
            "https://ajax.googleapis.com/ajax/libs/angularjs/1.4.0-beta.5/angular.min.js",
        ]);*/

        return $this;
    }

    public function vueJS()
    {
        $this->addAssets(
            [
                "vendor/yyx990803/vue/dist/vue.js",
                "vendor/vuejs/vue-resource/dist/vue-resource.js",
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
                "vendor/twbs/bootstrap/dist/css/bootstrap-theme.min.css",
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

    public function app()
    {
        $this->addAssets(['js/app.js']);

        return $this;

    }

    public function maestro()
    {
        $this->addAssets(
            [
                "js/maestro.js", // should it be on the end?
                "css/maestro.css", // should it be on the end?
            ]
        );
    }

    public function theme()
    {
        $this->addAssets(
            [
                // sb-admin-2.js
                "js/sb-admin-2.js",
                "js/plugins/metisMenu/metisMenu.min.js",
                "js/jquery-bootstrap-validation/v1.3.6/min.js",
                // sb-admin2
                "css/sb-admin-2.css",
                "css/plugins/dataTables.bootstrap.css",
                "css/plugins/metisMenu/metisMenu.min.css",
                "css/jquery-file-upload/v8.8.5/jquery.fileupload-ui.css",
            ]
        );

    }

    public function fontAwesome()
    {
        $this->addAssets(
            [
                //'https://use.fontawesome.com/90d4cc6ef0.js',
                //"vendor/fortawesome/font-awesome/css/font-awesome.min.css",
            ]
        );

        return $this;
    }

    public function redactor()
    {
        $this->addAssets(
            [
                "app/door/src/Door/Layout/public/assets/css/style-blue-redactor.css",
            ]
        );

        $this->addAssets(
            [
                "js/redactor/redactor.js",
                "js/redactor/redactor.conf.js",
                "js/redactor/redactor.css",
            ]
        );

        return $this;
    }

    public function chosen()
    {
        /*$this->addAssets([
            "vendor/lfw/admin/src/Weblab/Admin/public/js/chosen/v1.0.0/dev.js",
            "vendor/lfw/admin/src/Weblab/Admin/public/js/chosen/v1.0.0/conf.js",
            "vendor/lfw/admin/src/Weblab/Admin/public/css/chosen/v1.0.0/dev.css",
        ]);*/

        return $this;
    }

    public function datatables()
    {
        $this->addAssets(
            [
                "js/datatables/v1.9.4/dev.js",
                "js/datatables/v1.9.4/conf.js",
                "css/datatables/v1.9.4/conf.css",
            ]
        );

        return $this;
    }

    public function executeCore()
    {
        // dependencies
        $this->jQuery();
        $this->jQueryDatetimePicker();
        $this->angularJS();

        // front framework
        $this->bootstrap();
        $this->magnific();

        // decoration
        $this->fontAwesome();
        $this->chosen();
    }

    public function foundation()
    {
        $this->addAssets(
            [
                "bower_components/modernizr/nodernizr.js",
                "bower_components/fastclick/lib/fastclick.js",
                "bower_components/foundation/js/foundation.min.js",
                "bower_components/foundation/css/foundation.min.css",
            ]
        );
    }

}