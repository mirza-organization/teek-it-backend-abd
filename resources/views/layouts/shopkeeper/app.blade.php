<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta content="ie=edge" http-equiv="x-ua-compatible">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <!-- Font Awesome Icons -->
    <link href="{{ asset('res/plugins/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <!-- Theme style -->
    <link href="{{ asset('res/dist/css/adminlte.min.css') }}" rel="stylesheet">
    <link href="{{ asset('res/plugins/select2/css/select2.min.css') }}" rel="stylesheet">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    @yield('styles')
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <div class="container">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link d-sm-block d-md-block d-lg-none " data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
            </ul>

            <!-- SEARCH FORM -->
            <form class="d-block w-100 ml-3">
                <div class="input-group input-group-sm" style=" border-bottom: 1px solid #d4d4d4; ">
                    <div class="input-group-append">
                        <button class="bg-transparent  btn btn-navbar" type="submit">
                            <i class="bg-transparent fas fa-search" style="    color: #3a4b83;"></i>
                        </button>
                    </div>
                    <input aria-label="Search" class="bg-transparent  form-control form-control-navbar w-100" placeholder="Search"
                           type="search">

                </div>
            </form>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-sm-5">
                <li class="nav-item d-block">
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <a class="" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary" style=" overflow: initial; ">
        <!-- Brand Logo -->
        <a class="nav-link nav-sidebar-arrow" onclick="jQuery('.navbar-nav>.nav-item>.nav-link').click();"> <img
                alt="" src="{{asset('res/res/img/arrow.png')}}"></a>
        <a class="brand-link" href="/" style="display:block;opacity: 1">
            <img alt="AdminLTE Logo" class="brand-image" src="{{asset('res/res/img/logo.png')}}" style="display: block; opacity: 1">
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->


            <!-- Sidebar Menu -->
            <nav class="mt-5">
                <ul class="nav nav-pills nav-sidebar flex-column" data-accordion="false" data-widget="treeview" role="menu">
                    <!-- Add icons to the links using the .nav-icon class
                         with font-awesome or any other icon font library -->
                    <li class="nav-item">
                        <a class="nav-link {{ (request()->is('/')) ? 'active' : '' }} " href="/">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p class="ml-2">
                                Dashboard
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ (request()->is('inventory')) ? 'active' : '' }} " href="/inventory">
                            <i class="nav-icon fas fa-boxes"></i>
                            <p class="ml-2">
                                Inventory
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ (request()->is('orders')) ? 'active' : '' }}" href="/orders">
                            <i class="nav-icon fas fa-clipboard-list"></i>
                            <p class="ml-2">
                                Orders
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ (request()->is('withdrawals')) ? 'active' : '' }}" href="/withdrawals">
                            <i class="nav-icon fas fa-clipboard-list"></i>
                            <p class="ml-2">
                                Withdrawals
                            </p>
                        </a>
                    </li>
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link ">
                            <i class="nav-icon fas fa-user-secret"></i>
                            <p>
                                Admin
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="/settings/general" class="nav-link">
                                    <i class="fas fa-gears nav-icon"></i>
                                    <p>General Settings</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/settings/payment" class="nav-link">
                                    <i class="fas fa-money nav-icon"></i>
                                    <p>Payment Settings</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
<div class="row">
    <div class="col-md-12">
        @include('flash::message')
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
    </div>
</div>
        @yield('content')
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->



<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="{{ asset('res/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('res/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('res/dist/js/adminlte.min.js') }}"></script>
<style>
    .ratting span{
        color:wj
    }
    table tr:first-of-type td {
        border-top: 0;
    }

    .brand-link {
        background-color: white;
    }

    [class*=sidebar-dark-] {
        background-color: #3a4b83;
    }

    [class*=sidebar-dark-] * {
        color: #fff;
    }
    .text-primary {
        color: #3a4b83!important;
    }
    nav.main-header {
        box-shadow: 0 0px 1px rgba(0, 0, 0, .25), 0 4px 15px rgba(0, 0, 0, .22) !important;
    }

    .brand-link .brand-image {
        max-height: 90px;
    }

    nav.main-header {
        min-height: 120px;
    }

    .navbar-light .navbar-nav .nav-link, nav.main-header a {
        color: #3a4b83;
        font-weight: 600;
    }

    .brand-link {
        min-height: 120px;
    }

    .brand-link .brand-image {
        float: unset;
        margin: 0 auto;
        display: block;
    }

    [class*=sidebar-dark] .brand-link {
        margin: 0;
        padding: 0;
    }

    .brand-link .brand-image {
        padding-top: 25px;
    }

    .sidebar-mini.sidebar-collapse .main-sidebar.sidebar-focused .brand-link, .sidebar-mini.sidebar-collapse .main-sidebar:hover .brand-link, .sidebar-mini.sidebar-collapse .main-sidebar:hover {
        width: 4.6rem;
    }
    .checked {
        color: orange;
    }
    .nav-sidebar-arrow {
        position: absolute;
        top: 55%;
        right: -17px;
        cursor: pointer;
        background: #ffcf42;
        border-radius: 100% 100%;
        padding: 0;
    }

    .nav-sidebar-arrow img {
        max-width: inherit;
        max-height: 40px;
    }

    .sidebar-collapse .nav-sidebar-arrow img {
        transform: rotate(178deg);
    }

    .content-header h1 {
        font-size: 3.5rem;
    }

    .text-site-primary {
        color: #3a4b83;
    }


    .card-body {
        padding: 5px 5px;
    }

    .img-container {
        background: #f4f6f9;
        display: block;
        margin-left: 30px;
        margin-right: 30px;
        padding-top: 25px;
        padding-bottom: 25px;
        border-radius: 20px;
    }

    .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active, .sidebar-light-primary .nav-sidebar > .nav-item > .nav-link.active {
        box-shadow: unset;
        color: #ffcf42;
        background-color: unset;
    }

    .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active *, .sidebar-light-primary .nav-sidebar > .nav-item > .nav-link.active * {
        color: #ffcf42;
    }
    .img-container {
        background: #f4f6f9;
        display: block;
        margin-left: 30px;
        margin-right: 30px;
        padding-top: 25px;
        padding-bottom: 25px;
        border-radius: 20px;
        width: 100%;
        height: auto;
        margin: 0;
        padding: 10px;
        border-radius: 7px;
    }
    .img-container img{
        width: auto;
        height: auto;
        max-width: 100%;
        height: auto;
    }
    .pt-30{
        padding-top: 30px;
    }
    .pb-30{
        padding-bottom: 30px;
    }
    .card-body {
        padding: 5px 15px;
    }
    .color-circle{
        width: 15px;
        height: 15px;
        display: inline-block;
        border-radius: 100vw;
    }
    .color-red{
        background: red;
    }
    .ui-timepicker-standard {
        margin-top: -242px!important;
    }
</style>
<style>
    /*input.form-control{*/
    /*    border: 0;*/
    /*    border-bottom: 1px solid;*/
    /*    border-radius: 0;*/
    /*    color: #8aa7d7;*/
    /*    border-color: #3663ae;*/
    /*    padding-left: 3px;*/
    /*}*/
    input.form-control,select.form-control {
        border: 0;
        border-bottom: 1px solid;
        border-radius: 0;
        color: #8aa7d7;
        border-color: #4a7ed6;
        padding-left: 3px;
        background: transparent;

        background-color: transparent!important;
    }
    .form-control:focus {
        color: #495057;
        background-color: transparent;
        border-color: #80bdff;
        color: #8aa7d7!important;
    }
</style>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
<script !src="">
    $('.stimepicker').timepicker({
        timeFormat: 'h:mm p',
        interval: 30,
        startTime: '10:00',
        dynamic: true,
        dropdown: true,
        scrollbar: true
    });
    $('.etimepicker').timepicker({
        timeFormat: 'h:mm p',
        interval: 30,
        startTime: '10:00',
        dynamic: true,
        dropdown: true,
        scrollbar: true
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<script>

    function change_height(){
        gpt_box = jQuery('.change-height');
        jQuery('.change-height').height('auto');
        // console.log(gpt_box);
        max = jQuery(gpt_box[0]).height();
        //console.log(max);
        jQuery.each(gpt_box,function(index,value) {
            if (jQuery(value).height() > max)
            {
                max = jQuery(value).height();
            }

        });
        jQuery.each(gpt_box,function(index,value) {
            jQuery(value).height(max);
        });
        console.log('works;');
        setTimeout(change_height,600);

        // change_height();
    }
    change_height();
    var my_order_countvr = 0;
    $.ajax({
        url: "/my_order_count",
        // type: "POST",
        // data: "urut=" + $(".urut").val(),
        success: function(data) {
            my_order_countvr = data;
            my_order_count();
            // $("#result").html(data);
        }
    });
    function my_order_count(){
        $.ajax({
            url: "/my_order_count",
            // type: "POST",
            // data: "urut=" + $(".urut").val(),
            success: function(data) {
                if (data>my_order_countvr){
                    // alert("Maybe You Got New Order")
                    Swal.fire(
                        'Be Alert!',
                        'Maybe You Got New Order',
                        'success'
                    )
                }
                my_order_countvr = data;

                setTimeout(my_order_count,2000);
                // $("#result").html(data);
            }
        });
    }

</script>
<script src="{{ asset('res/plugins/select2/js/select2.min.js') }}"></script>
@yield('scripts')
</body>
</html>
