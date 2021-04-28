@extends('layouts.admin.app')
@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h2 class="m-0 text-dark text-center">Admin Dashboard</h2>
                    <a class="text-site-primary text-center m-auto d-block" href="/"
                       style="width: fit-content;text-decoration: underline; font-size: 3.0em; line-height: 1;">Settings</a>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <form method="post" action="{{route('update_pages')}}" enctype="multipart/form-data">
        {{csrf_field()}}
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card  pl-4 pr-4 pb-4 p-4" >
                        <div class="card-body">
                            <div class="row">

                                <div class="d-none col-md-3 pl- pr- pb-">
                                    <div class="">
                                        <h5 class="d-block text-center p-3 pb-0 m-0 text-site-primary"><a class="font-weight-bold  text-site-primary"
                                                                                                          href="#">Company Logo</a></h5>
                                    </div>
                                    <div class="card-text">
                                        <div class="col-md-12">
                    <span class="img-container position-relative">

                        <div class="overlay overlay-logo-img-hover big-font-hover">Click to Change Image</div>
                    <img alt="" class="d-block m-auto img-fluid" src="/res/res/img/store_logo.png">
                        <form action="" enctype="multipart/form-data" id="company_logo">
                            <input type="file" name="company_logo">
                        </form>
                    </span>
                                        </div>
                                    </div>


                                </div>
                                <div class="d-none col-md-2" style="margin-top: 9%;">
                                    <div class="">
                                        <h5 class="d-block text-center p-3 pb-0 m-0 text-site-primary"><a class="font-weight-bold  text-site-primary"
                                                                                                          href="#">Favicon</a></h5>
                                    </div>
                                    <div class="card-text">
                                        <div class="col-md-12">
                    <span class="img-container" style="padding: 20px;width: fit-content;margin: 0 auto; position: relative">
                        <div class="overlay overlay-logo-img-hover">Click to Change Image</div>
                    <img alt="" class="d-block m-auto" style="max-width: 50px;" src="/res/res/img/store_logo.png">
                    </span>
                                        </div>
                                    </div>


                                </div>
                                <div class="col-md-12">
{{--                                    <h5 class="d-none font-weight-bold d-block text-center p-3 pb-0 m-0 text-site-primary">--}}
{{--                                        <a class="ml-2 mr-2 text-site-primary text-left float-left"--}}
{{--                                           href="#">Teek IT</a>--}}
{{--                                        <a class="ml-2 mr-2 text-site-primary text-center d-inline-block"--}}
{{--                                           href="#">Slogan</a>--}}
{{--                                        <a class="ml-2 mr-2 text-site-primary text-right float-right"--}}
{{--                                           href="#">Edit</a></h5>--}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h2 class="text-center">Help</h2>
                                            <textarea style="border:0;margin-top: 0px;margin-bottom: 0px;height: 227px;min-height: 227px;max-height: 227px;width: 100%;min-width: 100%;max-width: 100%;background: #f4f6f9;border-radius: 15px;" placeholder="Help" name="help" class="form-control" onresize="return 0;" id="" >{{$help_page->page_content}}</textarea>
                                            <button type="submit" style=" width: fit-content; margin-top: 15px!important; background: #ffcf42; border: 0; padding: 10px 60px; border-radius: 30px!important; color: black; " class="btn btn-secondary rounded mt-3 text-center align-content-center btn-block m-auto">Update</button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <h2 class="m-0 text-dark text-center">Terms & Conditions</h2>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card  pl-4 pr-4 pb-4 p-4" >
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-12">
                                    <textarea style=" border:0;margin-top: 0px;margin-bottom: 0px;height: 127px;min-height: 127px;max-height: 127px;width: 100%;min-width: 100%;max-width: 100%;background: #f4f6f9;border-radius: 15px;" placeholder="3 wolf moon retro jean shorts chambray sustainable roof party. Shoreditch vegan artisan Helvetica. Tattooed Codeply Echo Park Godard kogi, next level irony ennui twee squid fap selvage. Meggings flannel Brooklyn literally small batch, mumblecore PBR try-hard kale chips. Brooklyn vinyl lumbersexual bicycle rights, viral fap cronut leggings squid chillwave pickled gentrify mustache. 3 wolf moon hashtag church-key." name="tos" class="form-control" onresize="return 0;"  >{{$terms_page->page_content}}</textarea>
                                    <button type="submit" style=" width: fit-content; margin-top: 15px!important; background: #ffcf42; border: 0; padding: 10px 60px; border-radius: 30px!important; color: black; " class="btn btn-secondary rounded mt-3 text-center align-content-center btn-block m-auto">Update</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <h2 class="m-0 text-dark text-center">FAQ</h2>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card  pl-4 pr-4 pb-4 p-4" >
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-12">
                                    <textarea style=" border:0;margin-top: 0px;margin-bottom: 0px;height: 127px;min-height: 127px;max-height: 127px;width: 100%;min-width: 100%;max-width: 100%;background: #f4f6f9;border-radius: 15px;" placeholder="3 wolf moon retro jean shorts chambray sustainable roof party. Shoreditch vegan artisan Helvetica. Tattooed Codeply Echo Park Godard kogi, next level irony ennui twee squid fap selvage. Meggings flannel Brooklyn literally small batch, mumblecore PBR try-hard kale chips. Brooklyn vinyl lumbersexual bicycle rights, viral fap cronut leggings squid chillwave pickled gentrify mustache. 3 wolf moon hashtag church-key." name="faq" class="form-control" onresize="return 0;"  >{{$faq_page->page_content}}</textarea>
                                    <button type="submit" style=" width: fit-content; margin-top: 15px!important; background: #ffcf42; border: 0; padding: 10px 60px; border-radius: 30px!important; color: black; " class="btn btn-secondary rounded mt-3 text-center align-content-center btn-block m-auto">Update</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
    </form>
    <style>
        .img-container .overlay-logo-img-hover{
            /*visibility: hidden;*/
            display: none;
            transition: 6s;
        }
        .img-container .overlay-logo-img-hover{
            position: absolute;
            background: #3a4b83ba!important;
            width: 100%;
            height: 100%;
            margin: 0;
            top: 0;
            left: 0;
            color: #fff;
            padding: 10px;
            font-size: 11px;
            cursor: pointer;
            /*visibility: visible;*/
        }
        .img-container:hover .overlay-logo-img-hover{
            /*visibility: visible;*/
            display: block;
        }
        .img-container .overlay-logo-img-hover.big-font-hover{
            font-size: 30px;
        }
    </style>
@endsection