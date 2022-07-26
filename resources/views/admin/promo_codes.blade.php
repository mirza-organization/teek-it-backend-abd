@extends('layouts.admin.app')
@section('content')
<div class="content">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        @if(session()->has('message'))
        <div class="alert alert-success">
            <div class="rmv_msg"> {{ session()->get('message') }}</div>
        </div>
        @endif
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark text-center">Promo Codes</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- modal start -->
                @foreach($promo_codes as $promo_code)
                <div class="modal fade" id="promo_codeModal{{$promo_code->id}}" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" style="display: none;" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form method="post" action="{{route('admin.promocodes.update',['id'=>$promo_code->id])}}"
                                enctype="multipart/form-data">
                                {{csrf_field()}}
                                <div class="modal-header">
                                    <h5 class="modal-title display-center" id="exampleModalLabel">Promo code</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="">Promo code</label>
                                                <input type="text" name="promo_code" class="form-control"
                                                    value="{{$promo_code->promo_code}}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="">Discount</label>
                                                <input type="number" name="discount_percentage" class="form-control"
                                                    value="{{$promo_code->discount_percentage}}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="">Order</label>
                                                <input type="number" name="order_number" class="form-control"
                                                    value="{{$promo_code->order_number}}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="">Expiry date</label>
                                                <input type="date" name="expiry_dt" class="form-control"
                                                    value="{{$promo_code->expiry_dt}}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer hidden ">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
                <!-- modal end -->
                <div class="offset-xl-2 col-lg-12 col-xl-8 pb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-block text-right">
                                <div class="card-text">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <form action="{{route('admin.promocodes.add')}}" method="POST">
                                                {{csrf_field()}}
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="promo_code"
                                                                id="promo_code" placeholder="Promo Code*" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <input type="number" class="form-control"
                                                                name="discount_percentage" id="discount_percentage"
                                                                placeholder="Discount %*" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <input type="number" class="form-control"
                                                                name="order_number" id="order_number"
                                                                placeholder="Order#">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <input type="date" class="form-control" name="expiry_dt"
                                                                id="expiry_dt" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 offset-md-3 text-center">
                                                        <button style="background: #ffcf42;color:black;font-weight: 600"
                                                            class="pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill"
                                                            type="submit">Add</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="float-left">
                    <button type="button" class="btn btn-success" onclick="selectAll()">
                        <a class="text-white">Select All</a>
                    </button>
                    <button type="button" class="btn btn-danger" onclick="return delPromoCodes()">
                        <a class="text-white">Delete</a>
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table text-center table-hover table-responsive-sm border-bottom">
                                <thead>
                                    <tr class="bg-primary text-white">
                                        <th scope="col">#</th>
                                        <th></th>
                                        <th scope="col">Promo Code</th>
                                        <th scope="col">Discount</th>
                                        <th scope="col">Order#</th>
                                        <th scope="col">Expiry Date</th>
                                        <th scope="col">Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($promo_codes as $promo_code)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>
                                            <input type="checkbox" class="select-checkbox" title="Select"
                                                id="{{$promo_code->id}}">
                                        </td>
                                        <td>{{$promo_code->promo_code}}</td>
                                        <td>{{$promo_code->discount_percentage}}%</td>
                                        <td>{{$promo_code->order_number}}</td>
                                        <td>{{$promo_code->expiry_dt}}</td>
                                        <td>
                                            <a data-toggle="modal" data-target="#promo_codeModal{{$promo_code->id}}"
                                                class="btn btn-primary btn-xs">Edit Promo Code</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-center" style="padding-top: 10px;">
                                {{$promo_codes->links()}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
@endsection