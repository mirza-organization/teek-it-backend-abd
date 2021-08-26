@extends('layouts.admin.app')

@section('links')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@endsection

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark text-center">Admin Dashboard</h1>
                    <a href="/complete-orders" class="text-site-primary text-center m-auto d-block"
                       style="width: fit-content;text-decoration: underline; font-size: 3.0em; line-height: 1;">Complete
                        Orders</a>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <table id="complete-orders-table" class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Order Id</th>
            <th>Total Items</th>
            <th>Delivery Boy Name</th>
            <th>Receiver Name</th>
            <th>Receiver Phone No</th>
            <th>Receiver Complete Address</th>
            <th>Action</th>
        </tr>
        </thead>

        <tbody>
        @foreach($orders as $key=> $order)
        <tr>
            <td>{{$key+1}}</td>
            <td>{{$order->id}}</td>
            <td>{{$order->total_items}}</td>
            <td>{{$order->delivery_boy->name .' '. $order->delivery_boy->l_name}}</td>
            <td>{{$order->user->name .' '. $order->user->l_name}}</td>
            <td>{{$order->phone_number}}</td>
            <td>{{ \Illuminate\Support\Str::limit($order->house_no .' '. $order->address, 50, $end='...') }}</td>
            <td><a href="{{route('mark.complete.order',$order->id)}}" class="btn btn-primary">Mark as complete</a></td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <!-- /.content -->
@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#complete-orders-table').DataTable({
                "pageLength": 20
            });
        });
    </script>
@endsection
