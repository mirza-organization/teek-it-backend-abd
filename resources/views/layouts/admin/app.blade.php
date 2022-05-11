<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta content="ie=edge" http-equiv="x-ua-compatible">
    <link rel="icon" href="{{asset('res/res/img/logo.png')}}" type="image/svg+xml" />
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <!-- Font Awesome Icons -->
    <link href="{{ asset('res/plugins/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <!-- Theme style -->
    <link href="{{ asset('res/dist/css/adminlte.min.css') }}" rel="stylesheet">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    @yield('links')
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <div class="container">
                <!-- Left navbar links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link d-sm-block d-md-block d-lg-none " data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                    </li>
                </ul>

                <!-- SEARCH FORM -->
                <!-- <form class="d-block w-100 ml-3">
                    <div class="input-group input-group-sm" style=" border-bottom: 1px solid #d4d4d4; ">
                        <div class="input-group-append">
                            <button class="bg-transparent  btn btn-navbar" type="submit">
                                <i class="bg-transparent fas fa-search" style="    color: #3a4b83;"></i>
                            </button>
                        </div>
                        <input aria-label="Search" class="bg-transparent  form-control form-control-navbar w-100" placeholder="Search" type="search">
                    </div>
                </form> -->

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
            <a class="nav-link nav-sidebar-arrow" onclick="jQuery('.navbar-nav>.nav-item>.nav-link').click();"> <img alt="" src="{{asset('res/res/img/arrow.png')}}"></a>
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
                            <a class="nav-link {{ (request()->is('/')) ? 'active' : '' }}  " href="/">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p class="ml-2">
                                    Dashboard
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is('stores')) ? 'active' : '' }}  " href="/stores">
                                <i class="nav-icon fas fa-store-alt"></i>
                                <p class="ml-2">
                                    Stores
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link  {{ (request()->is('customers')) ? 'active' : '' }}  " href="/customers">
                                <i class="nav-icon fas fa-users-cog"></i>
                                <p class="ml-2">
                                    Customers
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is('drivers')) ? 'active' : '' }} " href="/drivers">
                                <i class="nav-icon fas fa-biking"></i>
                                <p class="ml-2">
                                    Drivers
                                </p>
                            </a>
                        </li>
                        <li class="nav-item has-treeview {{ (request()->is('orders') || request()->is('complete-orders')) ? 'active' : '' }}">
                            <a href="#" class="nav-link ">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p>
                                    Orders
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="/aorders" class="nav-link">
                                        <i class="fas fa-gears nav-icon"></i>
                                        <p>Orders</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/complete-orders" class="nav-link">
                                        <i class="fas fa-money nav-icon"></i>
                                        <p>Complete Orders</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item has-treeview {{ (request()->is('withdrawals-drivers') || request()->is('withdrawals')) ? 'active' : '' }}">
                            <a href="#" class="nav-link ">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p>
                                    Withdrawals
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="/withdrawals" class="nav-link">
                                        <i class="fas fa-gears nav-icon"></i>
                                        <p>Sellers</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/withdrawals-drivers" class="nav-link">
                                        <i class="fas fa-money nav-icon"></i>
                                        <p>Drivers</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is('acategories')) ? 'active' : '' }} " href="/acategories">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p class="ml-2">
                                    Categories
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is('asetting')) ? 'active' : '' }} " href="/asetting">
                                <i class="nav-icon fa fa-cog"></i>
                                <p class="ml-2">
                                    Settings
                                </p>
                            </a>
                        </li>
                        {{-- <li class="nav-item">--}}
                        {{-- <a class="nav-link {{ (request()->is('queries')) ? 'active' : '' }} " href="/queries">--}}
                        {{-- <i class="nav-icon fas fa-question-circle"></i>--}}
                        {{-- <p class="ml-2">--}}
                        {{-- Queries--}}
                        {{-- </p>--}}
                        {{-- </a>--}}
                        {{-- </li>--}}
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
        .ratting span {
            color: wj
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
            color: #3a4b83 !important;
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

        .navbar-light .navbar-nav .nav-link,
        nav.main-header a {
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

        .sidebar-mini.sidebar-collapse .main-sidebar.sidebar-focused .brand-link,
        .sidebar-mini.sidebar-collapse .main-sidebar:hover .brand-link,
        .sidebar-mini.sidebar-collapse .main-sidebar:hover {
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

        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active,
        .sidebar-light-primary .nav-sidebar>.nav-item>.nav-link.active {
            box-shadow: unset;
            color: #ffcf42;
            background-color: unset;
        }

        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active *,
        .sidebar-light-primary .nav-sidebar>.nav-item>.nav-link.active * {
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

        .img-container img {
            width: auto;
            height: auto;
            max-width: 100%;
            height: auto;
        }

        .pt-30 {
            padding-top: 30px;
        }

        .pb-30 {
            padding-bottom: 30px;
        }

        .card-body {
            padding: 5px 15px;
        }

        .color-circle {
            width: 15px;
            height: 15px;
            display: inline-block;
            border-radius: 100vw;
        }

        .color-red {
            background: red;
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
        input.form-control,
        select.form-control {
            border: 0;
            border-bottom: 1px solid;
            border-radius: 0;
            color: #8aa7d7;
            border-color: #4a7ed6;
            padding-left: 3px;
            background: transparent;

            background-color: transparent !important;
        }

        .form-control:focus {
            color: #495057;
            background-color: transparent;
            border-color: #80bdff;
            color: #8aa7d7 !important;
        }

        .row.mb-2 h1.m-0.text-dark.text-center {}
    </style>
    <!-- Online Source: //cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css -->
    <link rel="stylesheet" href="{{ asset('res/dist/css/jquery.timepicker.min.css') }}">
    <!-- Online Source: //cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js -->
    <script src="{{ asset('res/dist/js/jquery.timepicker.min.js') }}"></script>
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
    <script>
        gpt_box = jQuery('.change-height');
        // console.log(gpt_box);
        max = jQuery(gpt_box[0]).height();
        //console.log(max);
        jQuery.each(gpt_box, function(index, value) {
            if (jQuery(value).height() > max) {
                max = jQuery(value).height();
            }

        });
        jQuery.each(gpt_box, function(index, value) {
            jQuery(value).height(max);
        });
        $('.row.mb-2 h1.m-0.text-dark.text-center').text($('.row.mb-2 h1.m-0.text-dark.text-center').text().replace('Admin Dashboard', ''));

        function selectAll() {
            var checkboxes = document.querySelectorAll('.select-checkbox');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = true;
            }
        }

        function delUsers() {
            var checkboxes = document.querySelectorAll('.select-checkbox');
            var users = [];
            var x = 0;
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    users[x] = checkboxes[i].id;
                    x++;
                }
            }
            if (users.length != 0) {
                Swal.fire({
                    title: 'Warning!',
                    text: 'Are you sure you want to delete the selected users?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{route('admin.del.users')}}",
                            type: "get",
                            data: {
                                "users": users
                            },
                            success: function(response) {
                                if (response == "Users Deleted Successfully") {
                                    window.location.reload();
                                }
                            }
                        });
                    }
                });
            }
        }

        function delOrders() {
            var checkboxes = document.querySelectorAll('.select-checkbox');
            var orders = [];
            var x = 0;
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    orders[x] = checkboxes[i].id;
                    x++;
                }
            }
            if (orders.length != 0) {
                Swal.fire({
                    title: 'Warning!',
                    text: 'Are you sure you want to delete the selected orders?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{route('admin.del.orders')}}",
                            type: "get",
                            data: {
                                "orders": orders
                            },
                            success: function(response) {
                                if (response == "Orders Deleted Successfully") {
                                    window.location.reload();
                                }
                            }
                        });
                    }
                });
            }
        }
    </script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    @yield('scripts')
</body>

</html>