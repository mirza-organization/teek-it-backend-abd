@extends('layouts.admin.app')
@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark text-center">Admin Dashboard</h1>
                    <a href="/orders" class="text-site-primary text-center m-auto d-block" style="width: fit-content;text-decoration: underline; font-size: 3.0em; line-height: 1;">Orders</a>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">

                <form action="" class="w-100 mb-3">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <input type="text" class="form-control" name="search" placeholder="Order #">
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
                @foreach($orders as $order)
                    <div class="col-md-12 p-4 pr-4">
                        <div class="card">
                            <div class="card-body p-2 pl-5 pr-5 pb-5">
                                <div class="p-2 mb-2">Order #{{$order->id}}
                                    @if($order->order_status=='pending')
                                        <a href="{{route('accept_order',['order_id'=>$order->id])}}" class=" d-block float-right">Click here if Order is Ready</a>
                                        <a href="{{route('cancel_order',['order_id'=>$order->id])}}" onclick="cancelOrder(event)" class=" d-block btn btn-danger float-right" style="margin-right: 20px">Cancel Order</a>
                                    @endif
                                </div>

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
                                                        <a href="#" class="d-block text-site-primary">{{$item->product->category->category_name ?? ''}}</a>
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
        function cancelOrder(ev){
            ev.preventDefault();
            var urlToRedirect = ev.currentTarget.getAttribute('href'); //use currentTarget because the click may be on the nested i tag and not a tag causing the href to be empty
            Swal.fire({
                title: 'Warning!',
                text: 'Are you sure want to cancel this order?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed)
                    window.location.href=urlToRedirect
            });
        }
    </script>
@endsection
