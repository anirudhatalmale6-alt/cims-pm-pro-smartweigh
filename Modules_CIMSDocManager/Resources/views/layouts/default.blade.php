@php
    $controller = 'docmanager';
    $page = $action = 'index';
    $action = $controller.'_'.$action;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="keywords" content="" />
    <meta name="author" content="" />
    <meta name="robots" content="" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('page_description', 'Document Manager')" />
    <meta name="format-detection" content="telephone=no">
    <link rel="shortcut icon" type="image/png" href="/public/smartdash/images/favicon.png">

    <title>CIMS | @yield('title', 'Document Manager')</title>

    <!-- CSS includes -->
    <link href="/public/smartdash/vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="/public/smartdash/vendor/owl-carousel/owl.carousel.css" rel="stylesheet">
    <link href="/public/smartdash/vendor/metismenu/css/metisMenu.min.css" rel="stylesheet">
    <link href="/public/smartdash/css/style.css" rel="stylesheet">
    <link href="/public/smartdash/css/smartdash-forms.css" rel="stylesheet">
    @stack('styles')
</head>
<body data-layout="horizontal">

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div class="lds-ripple">
            <div></div>
            <div></div>
        </div>
    </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">
        <!--**********************************
            CIMS Header (from CIMSCore partials)
        ***********************************-->
        @include('cimscore::partials.cims_header')

        <!--**********************************
            CIMS Horizontal Menu (from CIMSCore partials)
        ***********************************-->
        @include('cimscore::partials.cims_menu_horizontal')

        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body default-height">
           @yield('content')
        </div>
        <!--**********************************
            Content body end
        ***********************************-->

        <!-- Modal -->
        @stack('modal')

        <!--**********************************
            CIMS Footer (from CIMSCore partials)
        ***********************************-->
        @include('cimscore::partials.cims_footer')

    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->
    <script src="/public/smartdash/vendor/global/global.min.js"></script>
    <script src="/public/smartdash/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    <script src="/public/smartdash/vendor/owl-carousel/owl.carousel.js"></script>
    <script src="/public/smartdash/vendor/metismenu/js/metisMenu.min.js"></script>
    <script src="/public/smartdash/js/custom.min.js"></script>
    <script src="/public/smartdash/js/dlabnav-init.js?v=2"></script>

    @stack('scripts')
</body>
</html>
