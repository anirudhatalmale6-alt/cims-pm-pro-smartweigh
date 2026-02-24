@php
    $controller = 'clientmaster';
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
    <meta name="description" content="@yield('page_description', 'Client Master')" />
    <meta name="format-detection" content="telephone=no">
    <link rel="shortcut icon" type="image/png" href="/public/smartdash/images/favicon.png">

    <title>CIMS | @yield('title', 'Client Master')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- CSS includes -->
    <link href="/public/smartdash/vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="/public/smartdash/vendor/owl-carousel/owl.carousel.css" rel="stylesheet">
    <link href="/public/smartdash/vendor/metismenu/css/metisMenu.min.css" rel="stylesheet">
    <link href="/public/smartdash/vendor/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css" rel="stylesheet">
    <link href="/public/smartdash/vendor/sweetalert2/sweetalert2.min.css" rel="stylesheet">
    <link href="/public/smartdash/vendor/toastr/css/toastr.min.css" rel="stylesheet">
    <link href="/public/smartdash/css/style.css" rel="stylesheet">
    <link href="/public/smartdash/css/smartdash-forms.css" rel="stylesheet">
    <link href="/public/assets/css/style.css" rel="stylesheet">

    <style>
        /* =============================================
           HORIZONTAL MENU STYLES
           Force the menu to display horizontally
        ============================================= */
        [data-layout="horizontal"] .dlabnav {
            width: 100% !important;
            position: relative !important;
            left: 0 !important;
            height: auto !important;
            min-height: auto !important;
        }

        [data-layout="horizontal"] .dlabnav .dlabnav-scroll {
            overflow: visible !important;
        }

        [data-layout="horizontal"] .dlabnav .metismenu {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: wrap !important;
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        [data-layout="horizontal"] .dlabnav .metismenu > li {
            display: inline-block !important;
            position: relative !important;
            margin: 0 !important;
            list-style: none !important;
        }

        [data-layout="horizontal"] .dlabnav .metismenu > li > a {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 15px 18px !important;
            color: rgba(255,255,255,0.9) !important;
            text-decoration: none !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            white-space: nowrap !important;
        }

        [data-layout="horizontal"] .dlabnav .metismenu > li > a i {
            font-size: 16px !important;
        }

        [data-layout="horizontal"] .dlabnav .metismenu > li > a .nav-text {
            color: inherit !important;
        }

        /* Dropdown menus */
        [data-layout="horizontal"] .dlabnav .metismenu .mm-collapse {
            display: none !important;
            position: absolute !important;
            top: 100% !important;
            left: 0 !important;
            background: linear-gradient(135deg, #0d3d56 0%, #17A2B8 100%) !important;
            min-width: 220px !important;
            z-index: 9999 !important;
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
        }

        [data-layout="horizontal"] .dlabnav .metismenu .mm-collapse.mm-show,
        [data-layout="horizontal"] .dlabnav .metismenu li:hover > .mm-collapse {
            display: block !important;
        }

        [data-layout="horizontal"] .dlabnav .metismenu .mm-collapse li {
            list-style: none !important;
            margin: 0 !important;
        }

        [data-layout="horizontal"] .dlabnav .metismenu .mm-collapse li a {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            padding: 12px 20px !important;
            color: rgba(255,255,255,0.85) !important;
            text-decoration: none !important;
            font-size: 13px !important;
            white-space: nowrap !important;
        }

        [data-layout="horizontal"] .dlabnav .metismenu .mm-collapse li a:hover {
            background: linear-gradient(135deg, #0a2d3d 0%, #0d4a5e 100%) !important;
            color: #fff !important;
            border-bottom: 3px solid #20c997 !important;
        }

        /* Third level dropdown */
        [data-layout="horizontal"] .dlabnav .metismenu .mm-collapse .mm-collapse {
            top: 0 !important;
            left: 100% !important;
        }

        /* Hide copyright in horizontal mode */
        [data-layout="horizontal"] .dlabnav .copyright {
            display: none !important;
        }

        /* Hide tooltips by default */
        .sd_tooltip_teal,
        .sd-menu-tooltip,
        .sd-mainmenu-tooltip {
            display: none !important;
        }

        /* Content body adjustment for horizontal layout */
        [data-layout="horizontal"] .content-body {
            margin-left: 0 !important;
            padding: 30px;
        }

        /* Hide preloader */
        #preloader {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>
<body data-layout="horizontal">

<!-- CIMS Notification System -->
<script>
    var CIMS = CIMS || {};
    CIMS.notify = function(message, type, duration) {
        type = type || 'success';
        duration = duration || 3000;
        var existing = document.querySelector('.cims-notify');
        if (existing) existing.remove();
        var icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
        var colors = { success: '#17A2B8', error: '#dc3545', warning: '#ffc107', info: '#0d3d56' };
        var notify = document.createElement('div');
        notify.className = 'cims-notify cims-notify-' + type;
        notify.innerHTML = '<i class="fas ' + icons[type] + '"></i> <span>' + message + '</span>';
        notify.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;padding:15px 25px;border-radius:6px;background:#fff;box-shadow:0 4px 20px rgba(0,0,0,0.15);display:flex;align-items:center;gap:12px;font-size:14px;font-weight:500;color:#333;border-left:4px solid ' + colors[type] + ';animation:cimsSlideIn 0.3s ease;';
        notify.querySelector('i').style.cssText = 'font-size:20px;color:' + colors[type] + ';';
        if (!document.getElementById('cims-notify-styles')) {
            var style = document.createElement('style');
            style.id = 'cims-notify-styles';
            style.textContent = '@keyframes cimsSlideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}@keyframes cimsSlideOut{from{transform:translateX(0);opacity:1}to{transform:translateX(100%);opacity:0}}';
            document.head.appendChild(style);
        }
        document.body.appendChild(notify);
        setTimeout(function() {
            notify.style.animation = 'cimsSlideOut 0.3s ease forwards';
            setTimeout(function() { notify.remove(); }, 300);
        }, duration);
    };
</script>

<!-- CIMS Master Header -->
@include('cimscore::partials.cims_master_header')

<!-- CIMS Master Menu -->
@include('cimscore::partials.cims_master_menu')

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

<!-- CIMS Master Footer -->
@include('cimscore::partials.cims_master_footer')

<!--**********************************
    Scripts
***********************************-->
<!-- Hardcoded JS includes -->
<script src="/public/smartdash/vendor/global/global.min.js"></script>
<script src="/public/smartdash/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script src="/public/smartdash/vendor/owl-carousel/owl.carousel.js"></script>
<script src="/public/smartdash/vendor/metismenu/js/metisMenu.min.js"></script>
<script src="/public/smartdash/vendor/moment/moment.min.js"></script>
<script src="/public/smartdash/vendor/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js"></script>
<script src="/public/smartdash/vendor/sweetalert2/sweetalert2.min.js"></script>
<script src="/public/smartdash/vendor/toastr/js/toastr.min.js"></script>
<script src="/public/smartdash/js/custom.min.js"></script>
<script src="/public/smartdash/js/dlabnav-init.js?v=2"></script>

@stack('scripts')
</body>
</html>
