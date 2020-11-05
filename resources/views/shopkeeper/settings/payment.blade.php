@extends('layouts.shopkeeper.app')
@section('content')
    <div class="content">

        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark text-center">Payment Settings</h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">

                    <div class="offset-md-2 col-md-8 pl-4 pr-4 pb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class=" d-block text-right">
                                    <div class="card-text">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4 class="text-center text-primary">Bank Details</h4>
                                            </div>
                                            <div class="col-md-12">
                                                <form action="{{route('payment_settings_update')}}" method="POST" enctype="multipart/form-data">
                                                    {{csrf_field()}}
                                                    <?php
                                                    $ps = json_decode($payment_settings,true);
                                                    if (!isset($ps[2])){
                                                        $ps[2]['bank_name']="";
                                                        $ps[2]['branch']="";
                                                        $ps[2]['account_number']="";
                                                    }
                                                    if (!isset($ps[1])){
                                                        $ps[1]['bank_name']="";
                                                        $ps[1]['branch']="";
                                                        $ps[1]['account_number']="";
                                                    }
                                                    ?>
                                                    <div class="bank-info">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <select class="form-control" name="bank[1][bank_name]" required>
                                                                        <option value="">Bank Name</option>
                                                                        <?php
                                                                        $myarr=['Al Baraka Bank (Pakistan) Limited.',
'Allied Bank Limited.',
'Askari Bank Limited.',
'Askari Islamic Bank.',
'Bank Alfalah Limited.',
'Bank Al-Habib Limited.',
'BankIslami Pakistan Limited.',
'Citi Bank',
'Deutsche Bank A.G.',
'The Bank of Tokyo-Mitsubishi UFJ',
'Dubai Islamic Bank Pakistan Limited.',
'Faysal Bank Limited.',
'First Women Bank Limited.',
'Habib Bank Limited.',
'Standard Chartered Bank (Pakistan) Limited.',
'Habib Metropolitan Bank Limited.',
'Industrial and Commercial Bank of China',
'Industrial Development Bank of Pakistan',
'JS Bank Limited.',
'MCB Bank Limited.',
'MCB Islamic Bank Limited.',
'Meezan Bank Limited.',
'National Bank of Pakistan',
'S.M.E. Bank Limited.',
'Samba Bank Limited.',
'Silk Bank Limited',
'Sindh Bank Limited.',
'Soneri Bank Limited.',
'Summit Bank Limited.',
'The Bank of Khyber.',
'The Bank of Punjab.',
'The Punjab Provincial Cooperative Bank Limited.',
'United Bank Limited.',
'Zarai Taraqiati Bank Limited.'];
                                                                        ?>
                                                                        @foreach($myarr as $m)
                                                                        <option @if($m==$ps[1]['bank_name']) selected @endif value="{{$m}}">{{$m}}</option>
                                                                            @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <input type="text" required class="form-control" name="bank[1][account_number]" placeholder="Account Number" value="{{$ps[1]['account_number']}}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <input type="text" required class="form-control" name="bank[1][branch]" placeholder="Branch" value="{{$ps[1]['branch']}}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <select class="form-control" name="bank[2][bank_name]" >
                                                                        <option value="">Bank Name</option>
                                                                    @foreach($myarr as $m)

                                                                            <option @if($m==$ps[2]['bank_name']) selected @endif value="{{$m}}">{{$m}}</option>
                                                                    @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <input type="text" class="form-control" name="bank[2][account_number]" placeholder="Account Number" value="{{$ps[2]['account_number']}}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="form-group">
                                                                    <input type="text" class="form-control" name="bank[2][branch]" placeholder="Branch"  value="{{$ps[2]['branch']}}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 offset-md-3 text-center mt-3">
                                                                <button style="background: #ffcf42;color:black;font-weight: 600" class="pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill" type="submit" >{{__('Update')}}</button>
                                                            </div>
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
                </div>
                <!-- /.row -->

            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
    <style>
        .card-body {
            padding: 30px 50px!important;
        }
    </style>
@endsection
