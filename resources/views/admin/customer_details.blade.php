@extends('layouts.admin.app')
@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark text-center">Admin Dashboard</h1>
                    <a  class="text-site-primary text-center m-auto d-block" style="width: fit-content;text-decoration: underline; font-size: 3.0em; line-height: 1;">Customer Details</a>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #2196F3;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #3a4b83;
        }
    </style>
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
//                                        'business_name'
                                              ];

                                              ?>

                                                      <div class="row">

                                                          @foreach(json_decode($user) as $key=>$u)
                                                              @if(!empty($u) && !in_array($key,$fields))
<?php

                                                                  if ($key=='f_name'){
                                                                      $key = "First_name";
                                                                  }
                                                                  if ($key=='l_name'){
                                                                      $key = "Last_name";
                                                                  }
                                                                  ?>

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
                                              <a href=""  data-toggle="modal" data-target="#exampleModal{{$user->id}}" class=" d-block text-right float-right pr-3"><img class="img-size-16" src="/res/res/img/edit.png" alt=""></a>

                                      </div>
                                  </div>
                                  <div class="col-md-12">
                                      <div class="row">
                                          <div class="col-md-3">
                                                   <span class="img-container">
                    <img style="    height: 250px;" class="d-block m-auto" src=@if($user->user_img)
                        "{{asset($user->user_img)}}"
                        @else
                                                           "{{asset('/res/res/img/customer.png')}}"
                                                       @endif alt="">
                    </span>
                                          </div>
                                          <div class="col-md-9">
                                              <?php
                                              $fields = [
                                                  'is_online',
                                                  'is_active',
                                                  'updated_at',
                                                  'created_at',
                                                  'email_verified_at'
//                                        'business_name'
                                              ];

                                              ?>

                                                  @foreach(json_decode($user) as $key=>$u)
                                                      @if(!empty($u) && !in_array($key,$fields))
<?php
                                                          if ($key=='f_name'){
                                                              $key = "First_name";
                                                          }
                                                          if ($key=='l_name'){
                                                              $key = "Last_name";
                                                          }
                                                          ?>
                                                          <h5 class="text-primary font-weight-bold text-capitalize">{{str_replace('_',' ',$key)}}: <span class="font-weight-normal ">{{$u}}</span></h5>
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
                                <div class="p-2 mb-2">Order #{{$order->id}}  @if($order->order_status=='pending')<a href="{{route('accept_order',['order_id'=>$order->id])}}" class=" d-block float-right">Click here if Order is Ready</a>@endif</div>

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
                    <img class="d-block m-auto" src="{{asset($item->product->feature_img)}}" alt="">
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
