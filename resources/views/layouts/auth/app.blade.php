<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta content="ie=edge" http-equiv="x-ua-compatible">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('res/res/img/logo.png') }}">
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

                <a class="navbar-brand" target="_blank" href="https://teekit.co.uk/">
                    <img style=" max-height: 50px;" src="{{ asset('res/res/img/logo.png') }}" alt="TeekIt Logo">
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                    data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <!-- <span class="fas fa-bars"></span> -->
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <form class="e my-2 my-lg-0 ml-auto w-lg-50" style="min-width: 45vw;" method="POST"
                        action="{{ route('login') }}">
                        <div class="row">
                            <div class="col-md-5">
                                {{csrf_field()}}
                                <div class="form-group">

                                    <input class="form-control mr-sm-2" type="Email" required autocomplete="off"
                                        name="email" placeholder="email" aria-label="email" value="{{ old('email') }}">
                                    <label for="checkauto">
                                        <input name="remember" id="checkauto" type="checkbox"> Keep me Logged in
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <input class="form-control mr-sm-2" type="password" autocomplete="off"
                                        placeholder="Password" name="password" required>
                                    <p for="">
                                        <a class="text-dark" href="{{ route('password.request') }}">Forgot Password?</a>
                                    </p>
                                </div>
                            </div>

                            <div class="col-md-2">

                                <div class="form-group">

                                    <button class="btn btn-outline-primary my-2 my-sm-0" type="submit"
                                        style=" /* padding: 5px 25px; */ display: block; width: 100%; margin-top: 15px!important; background: #3663ae; border: 0; border-radius: 0; color: white; ">Login</button>
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
                    <img style="max-height: 540px;margin: 0 auto;display: block;width: auto;max-width: 500px;height: 100%;width: 100%;object-fit: contain;"
                        src="{{asset('bike.png')}}" alt="">
                </div>
                <div class="col-md-4">
                    @yield('content')
                </div>
            </div>
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <script
        src="https://maps.googleapis.com/maps/api/js?libraries=geometry,places&key=AIzaSyDS4Nf8Ict_2h4lih9DCIt_EpkkBnVd85A">
    </script>
    <script>
    // Google Map Code - Begins
    var map;
    var marker;

    function initialize() {

        var mapOptions = {
            zoom: 12
        };
        map = new google.maps.Map(document.getElementById('map-canvas'),
            mapOptions);

        // Get GEOLOCATION
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var pos = new google.maps.LatLng(position.coords.latitude,
                    position.coords.longitude);

                map.setCenter(pos);
                // marker = new google.maps.Marker({
                //     position: pos,
                //     map: map,
                //     draggable: true
                // });
            }, function() {
                handleNoGeolocation(true);
            });
        } else {
            // Browser doesn't support Geolocation
            handleNoGeolocation(false);
        }

        function handleNoGeolocation(errorFlag) {
            if (errorFlag) {
                var content = 'Error: The Geolocation service failed.';
            } else {
                var content = 'Error: Your browser doesn\'t support geolocation.';
            }

            var options = {
                map: map,
                position: new google.maps.LatLng(60, 105),
                content: content
            };

            map.setCenter(options.position);
            marker = new google.maps.Marker({
                position: options.position,
                map: map,
                draggable: true
            });


        }

        // get places auto-complete when user type in modal_location_text
        var input = /** @type {HTMLInputElement} */
            (document.getElementById('modal_location_text'));

        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo('bounds', map);

        var infowindow = new google.maps.InfoWindow();
        marker = new google.maps.Marker({
            map: map,
            anchorPoint: new google.maps.Point(0, -29),
            draggable: true
        });
        google.maps.event.addListener(marker, "dragend", function() {
            var lat, long;

            console.log('i am dragged');
            lat = marker.getPosition().lat();
            long = marker.getPosition().lng();

            set_lat_lng(lat, long);

        });

        function set_lat_lng(lat, lng) {
            document.getElementById("ad_lat").value = lat;
            document.getElementById("ad_long").value = lng;
        }

        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            infowindow.close();
            marker.setVisible(true);
            console.log();
            lat = autocomplete.getPlace().geometry.location.lat();
            long = autocomplete.getPlace().geometry.location.lng();
            var place = autocomplete.getPlace();
            set_lat_lng(lat, long);
            if (!place.geometry) {
                return;
            }

            // If the place has a geometry, then present it on a map.
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17); // Why 17? Because it looks good.
            }
            marker.setIcon( /** @type {google.maps.Icon} */ ({
                url: place.icon,
                size: new google.maps.Size(71, 71),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(17, 34),
                scaledSize: new google.maps.Size(35, 35)
            }));
            marker.setPosition(place.geometry.location);
            marker.setVisible(true);
            var address = '';
            if (place.address_components) {
                address = [
                    (place.address_components[0] && place.address_components[0].short_name || ''), (place
                        .address_components[1] && place.address_components[1].short_name || ''), (place
                        .address_components[2] && place.address_components[2].short_name || '')
                ].join(' ');
            }

        });
    }
    google.maps.event.addDomListener(window, 'load', initialize);
    // Google Map Code - Ends

    function closed(day) {
        let listOfClasses = document.getElementById("time[" + day + "][open]").className;
        if (listOfClasses.search("disabled-input-field") < 0) {
            // To disable the input fields
            document.getElementById("time[" + day + "][open]").value = null;
            document.getElementById("time[" + day + "][close]").value = null;
            // To disable the input fields
            document.getElementById("time[" + day + "][open]").classList.add('disabled-input-field');
            document.getElementById("time[" + day + "][close]").classList.add('disabled-input-field');
            // To remove the required attribute from the input fields 
            document.getElementById("time[" + day + "][open]").required = false;
            document.getElementById("time[" + day + "][close]").required = false;
        } else {
            // To enable the input fields
            document.getElementById("time[" + day + "][open]").classList.remove('disabled-input-field');
            document.getElementById("time[" + day + "][close]").classList.remove('disabled-input-field');
            // To add the required attribute from the input fields 
            document.getElementById("time[" + day + "][open]").required = true;
            document.getElementById("time[" + day + "][close]").required = true;
        }
    }

    function submitLocation() {
        var user_address = document.getElementById("modal_location_text").value;
        var user_lat = document.getElementById("ad_lat").value;
        var user_lon = document.getElementById("ad_long").value;
        document.getElementById("user_location").innerHTML = user_address;
        document.getElementById("location_text").value = user_address;
        document.getElementById("Address[lat]").value = user_lat;
        document.getElementById("Address[lon]").value = user_lon;
        $('#map_modal').modal('hide');
    }

    function signUp() {
        var spinner =
            '<div  class="d-flex justify-content-center text-white"><div class="spinner-border myspinner"role="status"><span class="sr-only">Loading...</span></div></div>';
        let name = $('#name').val();
        let email = $('#email').val();
        let password = $('#password').val();
        let phone = $('#phone').val();
        let company_name = $('#company_name').val();
        let company_phone = $('#company_phone').val();
        let location_text = $('#location_text').val();
        let Address = [];
        let lat = $('input[id="Address[lat]"]').val();
        let lon = $('input[id="Address[lon]"]').val();
        $('#signup').html(spinner);
        $.ajax({
            url: "{{route('register')}}",
            type: "post",
            data: {
                _token: "{{ csrf_token() }}",
                name: name,
                email: email,
                password: password,
                phone: phone,
                company_name: company_name,
                company_phone: company_phone,
                location_text: location_text,
                lat: lat,
                lon: lon
            },
            success: function(response) {
                $('#signup').text('sign up');
                if (response == "User Created") {
                    Swal.fire({
                        title: 'Success!',
                        text: 'We have received your store details we will contact you soon to verify your store',
                        icon: 'success',
                        confirmButtonText: 'Ok'
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    console.log(response.errors);
                    if (response.errors.name)
                        console.log(response.errors.name[0]);
                    $('.name').html(response.errors.name[0]);
                    $('.email').html(response.errors.email[0]);
                    $('.password').html(response.errors.password[0]);
                    $('.phone').html(response.errors.phone[0]);
                    $('.company_name').html(response.errors.company_name[0]);
                    $('.company_phone').html(response.errors.company_phone[0]);
                    $('.location').html(response.errors.location_text[0]);
                }
            }
        });
    }
    </script>

    <!-- REQUIRED SCRIPTS -->
    <!-- jQuery -->
    <script src="{{ asset('res/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('res/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('res/dist/js/adminlte.min.js') }}"></script>
    <!-- Sweetalerts for Swal -->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <style>
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

    input.form-control::placeholder {
        color: white;
        font-size: 15px;
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