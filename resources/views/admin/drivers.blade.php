@extends('layouts.admin.app')
@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-12">
                <h1 class="m-0 text-dark text-center">Drivers</h1>
                <div class="float-right">
                    <!-- <button type="button" class="btn btn-success" onclick="selectAll()">
                        <a class="text-white">Select All</a>
                    </button> -->
                    <button type="button" class="btn btn-danger" onclick="delDrivers()">
                        <a class="text-white">Delete</a>
                    </button>
                </div>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container">

        <form action="" class="w-100 mb-3">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <input type="text" class="form-control" name="search" placeholder="Driver Name">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                </div>
            </div>
        </form>
        <div class="row">
            @foreach($drivers as $driver)
            <!-- Modal -->
            <div class="modal fade" id="exampleModal{{$driver->id}}" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">{{$driver->f_name}} {{$driver->l_name}}</h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <?php
                            $fields = [
                                'is_online',
                                'is_active'
                            ];
                            ?>
                            <div class="row">
                                @foreach(json_decode($driver) as $key=>$u)
                                @if(!empty($u) && !in_array($key,$fields))
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="" class="text-capitalize">{{str_replace('_',' ',$key)}}</label>
                                        <input type="text" disabled class="form-control" value="{{$u}}">
                                    </div>
                                </div>
                                @endif
                                @endforeach
                                <div class="col-md-12">

                                    <div class="form-group">
                                        <label for="" class="mt-5">

                                            @if($driver->is_active==0)
                                            <a
                                                href="{{route('change_user_status',['user_id'=>$driver->id,'status'=>1])}}">
                                                <span class="text-success">Click here to Enable Account</span></a>
                                            @else
                                            <a
                                                href="{{route('change_user_status',['user_id'=>$driver->id,'status'=>0])}}">
                                                <span class="text-danger">Click here to Disable Account </span></a>
                                            @endif
                                        </label>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer hidden d-none">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 pl-4 pr-4 pb-4">
                <div class="card @if($driver->is_active==0)
                       bg-danger
                    @else
                       bg-success
                    @endif">
                    <div class="card-body">
                        <input type="checkbox" class="select-checkbox" title="Select" id="{{$driver->id}}">
                        <a class="d-block text-right float-right" title="Edit"
                            href="{{route('driver_details',['driver_id'=>$driver->id])}}" data-bs-toggle="modal"
                            data-bs-target="#exampleModal{{$driver->id}}">
                            <img class="img-size-16" src="/res/res/img/edit.png">
                        </a>
                        <div class="card-text">
                            <div class="col-md-12">
                                <span class="img-container">
                                    <img class="d-block m-auto" src=@if($driver->profile_img)
                                    "{{config('constants.BUCKET') . $driver->profile_img}}"
                                    @else
                                    "{{asset('/res/res/img/driver.png')}}"
                                    @endif alt="">
                                </span>
                            </div>
                        </div>

                        <div class="">
                            <h3 class="d-block text-center p-3 pb-0 m-0 text-site-primary text-lg">
                                <a href="#" class="d-block text-site-primary">{{$driver->f_name}} {{$driver->l_name}}</a>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            <div class="col-md-12">
                {{$drivers->links()}}
            </div>
        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
</div>


<style>
body .img-container img {
    width: auto;
    height: auto;
    max-width: 100%;
    height: auto;
    height: 160px;
    object-fit: scale-down;
}
</style>
<!-- /.content -->
@endsection