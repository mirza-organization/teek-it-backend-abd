@extends('layouts.shopkeeper.app')
@section('content')
<<<<<<< HEAD
    <div class="content">

        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark text-center">Dashboard</h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{$pending_orders}}</h3>

                                <p>Pending Orders</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-bag"></i>
                            </div>
                            <a href="{{route('orders')}}" class="small-box-footer">More info <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{$total_orders}}</h3>

                                <p>Total Orders</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-stats-bars fas fa-status-bar"></i>
                            </div>
                            <a href="{{route('orders')}}" class="small-box-footer">More info <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{$total_products}}</h3>

                                <p>Total Products</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-person-add"></i>
                            </div>
                            <a href="{{route('inventory')}}" class="small-box-footer">More info <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <!-- ./col -->
                    <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>£{{$total_sales}}</h3>

                                <p>Total Sales</p>
                            </div>
                            <a href="{{route('orders')}}" class="small-box-footer">More info <i
                                    class="fas fa-arrow-circle-right"></i></a>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div>

                        </div>
                    </div>
                    <!-- ./col -->
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">All Orders</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body p-0">
                                <table class="table table-striped">
                                    <thead>
=======
<div class="content">

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark text-center">Dashboard</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{$pending_orders}}</h3>

                            <p>Pending Orders</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-bag"></i>
                        </div>
                        <a href="{{route('orders')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{$total_orders}}</h3>

                            <p>Total Orders</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars fas fa-status-bar"></i>
                        </div>
                        <a href="{{route('orders')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{$total_products}}</h3>

                            <p>Total Products</p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-person-add"></i>
                        </div>
                        <a href="{{route('inventory')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>£{{$total_sales}}</h3>

                            <p>Total Sales</p>
                        </div>
                        <a href="{{route('orders')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        <div class="icon">
                            <i class="ion ion-pie-graph"></i>
                        </div>

                    </div>
                </div>
                <!-- ./col -->
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-5">
                                    <h3 class="card-title">All Orders</h3>
                                </div>
                                <div class="col-md-7">
                                    <?php
                                    $settings = json_decode($user_settings[0]->settings);
                                    ?>
                                    @if ($settings->notification_music == 1)
                                    <label class="switch float-right">
                                        <input type="checkbox" checked onclick="window.location.href='{{route('change_settings',['setting_name'=>'notification_music','value'=>0])}}'">
                                        <span class="slider round"></span>
                                    </label>
                                    <h3 class="card-title float-right pr-3">Turn Off New Order Music</h3>
                                    @else
                                    <label class="switch float-right">
                                        <input type="checkbox" onclick="window.location.href='{{route('change_settings',['setting_name'=>'notification_music','value'=>1])}}'">
                                        <span class="slider round"></span>
                                    </label>
                                    <h3 class="card-title float-right pr-3">Turn On New Order Music</h3>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead>
>>>>>>> bc40bab051467a571c4fee195a934ea1931e57a7
                                    <tr>
                                        <th>#</th>
                                        <th>Order No</th>
                                        <th>Status</th>
                                        <th>View</th>
                                    </tr>
<<<<<<< HEAD
                                    </thead>
                                    <tbody>
                                    @forelse($all_orders as $key => $order)
                                        <tr>
                                            <td>{{$all_orders->firstItem() + $key}}</td>
                                            <td>{{$order->id}}</td>
                                            <td>
                                                @if($order->order_status == 'pending')
                                                    <span class="badge bg-danger">Pending</span>
                                                @elseif($order->order_status == 'assigned')
                                                    <span class="badge bg-warning">Assigned</span>
                                                @elseif($order->order_status == 'onTheWay')
                                                    <span class="badge bg-primary">On the Way</span>
                                                @elseif($order->order_status == 'ready')
                                                    <span class="badge bg-purple">Ready</span>
                                                @else
                                                    <span class="badge bg-success">Delivered</span>
                                                @endif
                                            </td>
                                            <td><a href="{{route('orders',['search'=>$order->id])}}"
                                                   class="btn btn-primary">View</a></td>
                                        </tr>
                                    @empty
                                    @endforelse
                                    </tbody>
                                </table>
                                {{$all_orders->links()}}
                            </div>
                            <!-- /.card-body -->
                        </div>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>

@endsection
=======
                                </thead>
                                <tbody>
                                    @forelse($all_orders as $key => $order)
                                    <tr>
                                        <td>{{$all_orders->firstItem() + $key}}</td>
                                        <td>{{$order->id}}</td>
                                        <td>
                                            @if($order->order_status == 'pending')
                                            <span class="badge bg-danger">Pending</span>
                                            @elseif($order->order_status == 'accepted')
                                            <span class="badge bg-info">Accepted</span>
                                            <!-- @elseif($order->order_status == 'assigned')
                                            <span class="badge bg-warning">Assigned</span> -->
                                            @elseif($order->order_status == 'ready')
                                            <span class="badge bg-purple">Ready</span>
                                            @elseif($order->order_status == 'onTheWay')
                                            <span class="badge bg-primary">On the Way</span>
                                            @else
                                            <span class="badge bg-success">Delivered</span>
                                            @endif
                                        </td>
                                        <td><a href="{{route('orders',['search'=>$order->id])}}" class="btn btn-primary">View</a></td>
                                    </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-center" style="padding-top: 10px;">
                                {{$all_orders->links()}}
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
@endsection
>>>>>>> bc40bab051467a571c4fee195a934ea1931e57a7
