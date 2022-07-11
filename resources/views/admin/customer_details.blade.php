@extends('layouts.admin.app')
@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-12">
                <h1 class="m-0 text-dark text-center">Admin Dashboard</h1>
                <a class="text-site-primary text-center m-auto d-block" style="width: fit-content;text-decoration: underline; font-size: 3.0em; line-height: 1;">
                @if($role_id==2)
                Store Details
                @endif
                @if($role_id==3)
                Customer Details
                @endif
                @if($role_id==4)
                Driver Details
                @endif
            </a>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="p-3">
                    <div class="card">
                        <div class="card-body p-2 pl-5 pr-5 pb-5">
                            <div class="row">
                                <div class="col-md-12">

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
                                                        'is_active',
                                                        'updated_at',
                                                        'created_at',
                                                        'email_verified_at'
                                                        //'business_name'
                                                    ];

                                                    ?>

                                                    <div class="row">

                                                        @foreach(json_decode($user) as $key=>$u)
                                                        @if(!is_null($u) && !in_array($key,$fields))
                                                        <?php
                                                        if ($key == 'f_name') {
                                                            $key = "First_name";
                                                        }
                                                        if ($key == 'l_name') {
                                                            $key = "Last_name";
                                                        }
                                                        ?>

                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="" class="text-capitalize">{{str_replace('_',' ',$key)}}</label>
                                                                <input type="text" disabled class="form-control @if($key=='application_fee') application-fee-input-field @endif" value="{{$u}}">
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
                                    <div class="p-2 mb-2">
                                        @if($user->is_active==0)
                                        <label class="switch float-right">
                                            <input type="checkbox" onclick="window.location.href='{{route('change_user_status',['user_id'=>$user->id,'status'=>1])}}'">
                                            <span class="slider round"></span>
                                        </label>

                                        <h4 class="float-right pr-3">Active</h4>
                                        @else

                                        <label class="switch float-right">
                                            <input type="checkbox" checked onclick="window.location.href='{{route('change_user_status',['user_id'=>$user->id,'status'=>0])}}'">
                                            <span class="slider round"></span>
                                        </label>

                                        <h4 class="float-right pr-3">Block</h4>
                                        @endif
                                        <!-- <a href="" data-toggle="modal" data-target="#exampleModal{{$user->id}}" class=" d-block text-right float-right pr-3"><img class="img-size-16" src="/res/res/img/edit.png"></a> -->
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <span class="img-container">
                                                <img style="width: 250px;" class="d-block m-auto" src=@if($user->user_img)
                                                "{{config('constants.BUCKET') . $user->user_img}}"
                                                @else
                                                "{{asset('/res/res/img/customer.png')}}"
                                                @endif alt="">
                                            </span>
                                        </div>
                                        <div class="col-md-9">
                                            <?php
                                            $fields = [
                                                'business_location',
                                                'is_online',
                                                'is_active',
                                                'updated_at',
                                                'created_at',
                                                'email_verified_at',
                                                'settings',
                                                'user_img'
                                            ];
                                            if($role_id == 3) // Customer
                                            array_push($fields,  'lat','lon','pending_withdraw','total_withdraw','application_fee');
                                            if($role_id == 4) // Driver
                                            array_push($fields,  'application_fee');
                                            ?>
                                            @foreach(json_decode($user) as $key=>$u)
                                            @if(!is_null($u) && !in_array($key,$fields))
                                            <?php
                                            if ($key == 'f_name') {
                                                $key = "First_name";
                                            }
                                            if ($key == 'l_name') {
                                                $key = "Last_name";
                                            }
                                            ?>
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th scope="col" class="col-md-3 align-top font-weight-bold text-capitalize">{{str_replace('_',' ',$key)}}</th>
                                                        <th scope="col">
                                                        <span class="font-weight-normal">
                                                                @if($key!='application_fee' && $key!='business_hours' && $key!='bank_details')
                                                                {{$u}}
                                                                @elseif($key=='business_hours')
                                                                <table class="table">
                                                                    <thead>
                                                                        <tr>
                                                                            <th scope="col">Day</th>
                                                                            <th scope="col">Opening Time</th>
                                                                            <th scope="col">Closing Time</th>
                                                                            <th scope="col">Closed</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php $business_hours = json_decode($u); ?>
                                                                        @foreach ($business_hours->time as $day => $time)
                                                                        <tr>
                                                                            <th scope="row">{{$day}}</th>
                                                                            <td>{{$time->open}}</td>
                                                                            <td>{{$time->close}}</td>
                                                                            <td>{{($time->closed == 'on') ? 'Yes' : ''}}</td>
                                                                        </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                                @elseif($key=='bank_details')
                                                                <table class="table">
                                                                    <thead>
                                                                        <tr>
                                                                            <th scope="col">Name</th>
                                                                            <th scope="col">Account #</th>
                                                                            <th scope="col">Branch</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php $bank_details = json_decode($u); ?>
                                                                        @foreach ($bank_details as $k => $v)
                                                                        <tr>
                                                                            <th scope="row">{{$v->bank_name}}</th>
                                                                            <td>{{$v->account_number}}</td>
                                                                            <td>{{$v->branch}}</td>
                                                                        </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                                @elseif($user->roles[0]->name=='seller')
                                                                <input type="number" class="form-control" data-id="{{json_decode($user)->id}}" id="application_fee" name="application_fee" value="{{$u}}" step="0.01">
                                                                @else
                                                                {{$u}}
                                                                @endif
                                                            </span>
                                                            @if($key=='application_fee' && $user->roles[0]->name=='seller')
                                                            <br>
                                                            <button class="btn btn-primary" id="application_fee_update">Update</button>
                                                            @endif
                                                        </th>
                                                    </tr>
                                                </thead>
                                            </table>
                                            @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-center text-secondary">Orders</h1>
            </div>
            @if($orders)
            @foreach($orders as $order)
            <div class="col-md-12 p-4 pr-4">
                <div class="card">
                    <div class="card-body p-2 pl-5 pr-5 pb-5">
                        <div class="p-2 mb-2">Order #{{$order->id}} @if($order->order_status=='pending')<a href="{{route('accept_order',['order_id'=>$order->id])}}" class=" d-block float-right">Click here if Order is Ready</a>@endif</div>

                        <div class="card-text">
                            <div class="row">

                                <div class="col-md-6">
                                    <p>
                                        Placed on {{$order->created_at}}
                                    </p>
                                </div>
                                <div class="col-md-5">
                                    <div class="row">
                                        <div class="col-md-12">
                                            Order Status: <span class="text-warning">{{$order->order_status}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <hr>
                            </div>
                            <div class="col-md-12">
                                @foreach($order->items as $item)
                                <div class="row mb-2">
                                    <div class="col-md-2">
                                        <span class="img-container">
                                            @if(str_contains($item->product->feature_img, 'https://'))
                                            <img class="d-block m-auto " src="{{$item->product->feature_img}}" alt="">
                                            @else
                                            <img class="d-block m-auto " src="{{config('constants.BUCKET') . $item->product->feature_img}}" alt="">
                                            @endif
                                        </span>
                                    </div>
                                    <div class="col-md-4">
                                        <h3 class="d-block text-left p-3 pb-0 m-0 text-site-primary text-lg">
                                            <a href="#" class="d-block text-site-primary">{{$item->product->product_name}}</a>
                                            <a href="#" class="d-block text-site-primary">{{$item->product->category->category_name}}</a>
                                            <a href="#" class="d-block text-site-primary">{{$item->product->sku}}</a>
                                        </h3>
                                    </div>
                                    <div class="col-md-2 mt-5">
                                        QTY: {{$item->product_qty}}
                                    </div>
                                    <div class="col-md-12"><br></div>
                                </div>
                                @endforeach
                            </div>
                        </div>


                    </div>
                </div>
            </div>
            @endforeach
            @else
            <div class="col-md-12">
                <div class="alert alert-secondary">There are no Orders</div>
            </div>
            @endif
        </div>
        <!-- /.row -->
        <div class="row">
            <div class="col-md-12">
                {{ $orders_p->links() }}
            </div>
        </div>
    </div><!-- /.container-fluid -->
</div>
<!-- /.content -->
@endsection

@section('scripts')
<script>
    $('#application_fee_update').click(function() {
        event.preventDefault();
        var id = $('#application_fee').attr('data-id');
        var val = $('#application_fee').val();
        var url = "/store/application-fee/" + id + "/" + val;
        $.ajax({
            url: url,
            type: "GET",
            'success': function(response) {
                $('.application-fee-input-field').val(val);
                swal({
                    title: "Application fee is successfully updated.",
                    icon: "success"
                })
            }
        });
    })
</script>
@endsection