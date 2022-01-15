@extends('layouts.admin.app')
@section('content')
<<<<<<< HEAD
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark text-center">Admin Dashboard</h1>
                    <a href="/customers" class="text-site-primary text-center m-auto d-block" style="width: fit-content;text-decoration: underline; font-size: 3.0em; line-height: 1;">Customers</a>
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
                                <input type="text" class="form-control" name="search" placeholder="Customer Name">
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
            @foreach($users as $user)
                <!-- Modal -->
                    <div class="modal fade" id="exampleModal{{$user->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">{{$user->name}} {{$user->l_name}}</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <?php
                                    $fields = [
                                        'is_online',
                                        'is_active'
//                                        'business_name'
                                    ];

                                    ?>
                                    <div class="row">

@foreach(json_decode($user) as $key=>$u)
    @if(!is_null($u) && !in_array($key,$fields))


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

                                                    @if($user->is_active==0)
                                                        <a href="{{route('change_user_status',['user_id'=>$user->id,'status'=>1])}}"> <span class="text-success">Click here to Enable Account</span></a>
                                                    @else
                                                        <a href="{{route('change_user_status',['user_id'=>$user->id,'status'=>0])}}"> <span class="text-danger">Click here to Disable Account   </span></a>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="modal-footer hidden d-none">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary">Save changes</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 pl-4 pr-4 pb-4">
                        <div class="card @if($user->is_active==0)
=======
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-12">
                <h1 class="m-0 text-dark text-center">Admin Dashboard</h1>
                <a href="/customers" class="text-site-primary text-center m-auto d-block" style="width: fit-content;text-decoration: underline; font-size: 3.0em; line-height: 1;">Customers</a>
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
                        <input type="text" class="form-control" name="search" placeholder="Customer Name">
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
            @foreach($users as $user)
            <!-- Modal -->
            <div class="modal fade" id="exampleModal{{$user->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">{{$user->name}} {{$user->l_name}}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <?php
                            $fields = [
                                'is_online',
                                'is_active'
                                //                                        'business_name'
                            ];

                            ?>
                            <div class="row">

                                @foreach(json_decode($user) as $key=>$u)
                                @if(!is_null($u) && !in_array($key,$fields))


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

                                            @if($user->is_active==0)
                                            <a href="{{route('change_user_status',['user_id'=>$user->id,'status'=>1])}}"> <span class="text-success">Click here to Enable Account</span></a>
                                            @else
                                            <a href="{{route('change_user_status',['user_id'=>$user->id,'status'=>0])}}"> <span class="text-danger">Click here to Disable Account </span></a>
                                            @endif
                                        </label>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer hidden d-none">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 pl-4 pr-4 pb-4">
                <div class="card @if($user->is_active==0)
>>>>>>> bc40bab051467a571c4fee195a934ea1931e57a7
                            bg-danger
@else
                            bg-success
@endif">
<<<<<<< HEAD
                            <div class="card-body">
                                <a href="{{route('customer_details',['user_id'=>$user->id])}}"  pdata-toggle="modal" pdata-target="#exampleModal{{$user->id}}" class=" d-block text-right float-right"><img class="img-size-16" src="/res/res/img/edit.png" alt=""></a>
                                <a href="/aorders?user_id={{$user->id}}" class=" d-block text-right mr-3 float-right mb-3" title="Orders"><i class="fas fa-clipboard-list" style="    font-size: 30px;
    color: #3a4b83;"></i></a>

                                <div class="card-text">
                                    <div class="col-md-12">
                    <span class="img-container">
                    <img class="d-block m-auto" src=@if($user->user_img)
                        "{{asset($user->user_img)}}"
                        @else
                            "{{asset('/res/res/img/customer.png')}}"
                        @endif alt="">
                    </span>
                                    </div>
                                </div>

                                <div class="">
                                    <h3 class="d-block text-center p-3 pb-0 m-0 text-site-primary text-lg">
                                        <a href="#" class="d-block text-site-primary">{{$user->name}} {{$user->l_name}}</a>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="col-md-12">
                    {{$users->links()}}
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->

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
@endsection
=======
                    <div class="card-body">
                        <a href="{{route('customer_details',['user_id'=>$user->id])}}" pdata-toggle="modal" pdata-target="#exampleModal{{$user->id}}" class=" d-block text-right float-right" title="Edit"><img class="img-size-16" src="/res/res/img/edit.png" alt=""></a>
                        <a href="/aorders?user_id={{$user->id}}" class="d-block text-right mr-3 float-right mb-3" title="Orders"><i class="fas fa-clipboard-list" style="    font-size: 30px;
    color: #3a4b83;"></i></a>

                        <div class="card-text">
                            <div class="col-md-12">
                                <span class="img-container">
                                    <img class="d-block m-auto" src=@if($user->user_img)
                                    "{{asset($user->user_img)}}"
                                    @else
                                    "{{asset('/res/res/img/customer.png')}}"
                                    @endif alt="">
                                </span>
                            </div>
                        </div>

                        <div class="">
                            <h3 class="d-block text-center p-3 pb-0 m-0 text-site-primary text-lg">
                                <a href="#" class="d-block text-site-primary">{{$user->name}} {{$user->l_name}}</a>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            <div class="col-md-12">
                {{$users->links()}}
            </div>
        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content -->

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
@endsection
>>>>>>> bc40bab051467a571c4fee195a934ea1931e57a7
