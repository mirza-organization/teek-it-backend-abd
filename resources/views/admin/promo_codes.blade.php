@extends('layouts.admin.app')
@section('content')
<div class="content">
    <!-- Content Header (Page header) -->
    <div class="content-header">
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
                <div class="offset-xl-2 col-lg-12 col-xl-8 pb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-block text-right">
                                <div class="card-text">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <form action="{{route('admin.promocodes.add')}}" method="POST"
                                                enctype="multipart/form-data">
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
                                                                id="expiry_dt" placeholder="expiry date*">
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
                <div class="float-right">
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
                                            <a href="#" data-toggle="modal" data-target="#bankModal"
                                                class="btn btn-xs btn-warning">View Bank Detail</a>
                                            <a href="#" data-toggle="modal" data-target="#transactionModal"
                                                class="btn btn-primary btn-xs">Update Transaction ID</a>
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