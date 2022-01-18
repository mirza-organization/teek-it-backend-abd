<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta content="ie=edge" http-equiv="x-ua-compatible">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <!-- Font Awesome Icons -->
    <link href="{{ asset('res/plugins/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <!-- Theme style -->
    <link href="{{ asset('res/dist/css/adminlte.min.css') }}" rel="stylesheet">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body class="hold-transition" style="background: url('/bg.png')">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg" style="background: white">
            <div class="container-fluid">

                <a class="navbar-brand" href="/"><img style=" max-height: 50px; " src="{{ asset('res/res/img/logo.png') }}" alt=""></a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <form class="e my-2 my-lg-0 ml-auto w-lg-50" style="min-width: 45vw;" method="POST" action="{{ route('login') }}">
                        <div class="row">
                            <div class="col-md-5">
                                {{csrf_field()}}
                                <div class="form-group">

                                    <input class="form-control mr-sm-2" type="Email" required autocomplete="off" name="email" placeholder="email" aria-label="email" value="{{ old('email') }}">
                                    <label for="checkauto">
                                        <input name="remember" id="checkauto" type="checkbox"> Keep me Logged in
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-5">

                                <div class="form-group">

                                    <input class="form-control mr-sm-2" type="password" autocomplete="off" placeholder="Password" name="password" required>
                                    <p for="">
                                        <a class="text-dark" href="{{ route('password.request') }}">Forgot Password?</a>
                                    </p>
                                </div>
                            </div>

                            <div class="col-md-2">

                                <div class="form-group">

                                    <button class="btn btn-outline-primary my-2 my-sm-0" type="submit" style=" /* padding: 5px 25px; */ display: block; width: 100%; margin-top: 15px!important; background: #3663ae; border: 0; border-radius: 0; color: white; ">Login</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </nav>
        <!-- /.navbar -->
        <div class="container">
            @include('flash::message')
            @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
            @endif
            <div class="row mtd" style="margin-top: 20vh">
                <div class="col-md-8">
                    <img style="max-height: 540px;margin: 0 auto;display: block;width: auto;max-width: 500px;height: 100%;width: 100%;object-fit: contain;" src="{{asset('bike.png')}}" alt="">
                </div>
                <div class="col-md-4">

                    @yield('content')
                </div>
            </div>
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
        /*input.form-control{*/
        /*    border: 0;*/
        /*    border-bottom: 1px solid;*/
        /*    border-radius: 0;*/
        /*    color: #8aa7d7;*/
        /*    border-color: #3663ae;*/
        /*    padding-left: 3px;*/
        /*}*/
        input.form-control {
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
    </style>
</body>

</html>