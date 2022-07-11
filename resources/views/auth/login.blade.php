@extends('layouts.auth.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1 class="text-white text-center">{{ __('Sign Up') }}</h1>

            <form id="sign_up_form" style="margin-bottom: 100px;" onsubmit="return false">
               
                <div class="form-group row">
                    <div class="col-md-12">
                        <input id="name" type="text" placeholder="Name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" required autofocus>

                        @if ($errors->has('name'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('name') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12">
                        <input id="email" type="email" placeholder="Email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>

                        @if ($errors->has('email'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12">
                        <input id="password" placeholder="Password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" minlength="8" required>

                        @if ($errors->has('password'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12 input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">+44</span>
                        </div>
                        <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" maxlength="10" placeholder="Phone Number" class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" id="phone" name="phone" value="{{ old('phone') }}" required autofocus>

                        @if ($errors->has('phone'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('phone') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12">
                        <input id="company_name" type="text" placeholder="Company Name" class="form-control{{ $errors->has('company_name') ? ' is-invalid' : '' }}" name="company_name" value="{{ old('company_name') }}" required autofocus>

                        @if ($errors->has('company_name'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('company_name') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12 input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">+44</span>
                        </div>
                        <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" maxlength="10" placeholder="Company Phone" class="form-control{{ $errors->has('company_phone') ? ' is-invalid' : '' }}" id="company_phone" name="company_phone" value="{{ old('company_phone') }}" required autofocus>

                        @if ($errors->has('company_phone'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('company_phone') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12">
                        <div class="form-group" data-toggle="modal" data-target="#map_modal" style="cursor: pointer;">
                            <i class="fas fa-map-marked-alt text-light fa-2x"></i>
                            &nbsp;&nbsp;&nbsp;
                            <span class="text-light" id="user_location">Set Location</span>
                            <input type="hidden" id="location_text" name="location_text">
                            <input type="hidden" id="Address[lat]" name="Address[lat]">
                            <input type="hidden" id="Address[lon]" name="Address[lon]">
                        </div>
                    </div>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-md-12">
                        <button class="btn btn-outline-primary my-2 my-sm-0" type="submit" style="padding: 5px 25px; display: block;width: 100%;background: #ffec00;border: 0;border-radius: 0;color: #000100;font-weight: 600;border: 0;" onclick="signUp()">Sign up</button>
                    </div>
                </div>
            </form>

            <!-- <form style="display: none" method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group row">
                    <div class="col-md-12">
                        <input id="email" type="email" placeholder="Email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>

                        @if ($errors->has('email'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">

                    <div class="col-md-12">
                        <input id="password" placeholder="Password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                        @if ($errors->has('password'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                            <label class="form-check-label te" for="remember">
                                {{ __('Remember Me') }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-md-12">
                        <button class="btn btn-outline-primary my-2 my-sm-0" type="submit" style="/* padding: 5px 25px; */display: block;width: 100%;margin-top: 15px!important;background: #ffec00;border: 0;border-radius: 0;color: #000100;font-weight: 600;border: 0; ">Login</button>

                        @if (Route::has('password.request'))
                        <a class="btn btn-link text-white" href="{{ route('password.request') }}">
                            {{ __('Forgot Your Password?') }}
                        </a>
                        @endif
                        <a class="btn btn-link text-white" href="{{ route('register') }}">
                            {{ __('Create a New Account?') }}
                        </a>
                    </div>
                </div>
            </form> -->
        </div>
    </div>
</div>
<!-- Google Map Modal - Begins -->
<div class="modal fade" id="map_modal">
    <div class="modal-dialog modal-lg  modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Add Location</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <!-- Modal body -->
            <div class="modal-body">
                <div class="row">
                    <div class="card">
                        <div class="card-body-custom">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12 mt-3 mb-3">
                                        <div class="form-group" style="height:100%; width:100%">
                                            <input type="text" class="form-control" name="modal_location_text" id="modal_location_text" />
                                            <div class="mt-3 mb-3" style="height: 100%; width: 100%; margin: 0px; padding: 0px;min-height: 200px;" id="map-canvas"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <br>
                                        <br>
                                        <br>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="Address">Lat</label>
                                        <input type="text" name="ad_lat" id="ad_lat" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="Address">Long</label>
                                        <input type="text" name="ad_long" id="ad_long" class="form-control">
                                    </div>
                                    <button type="submit" onclick="submitLocation()" class="d-no mt-3 btn btn-submit btn-block btn-outline-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>
<!-- Google Map Modal - Ends -->
@endsection