@extends('layouts.shopkeeper.app')
@section('content')
    <div class="content">
        <!-- Content Header (Page header) -->
        @if (Auth::user()->role->name == 'seller')
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <div class="float-right">
                                <button type="button" class="btn btn-warning">
                                    <a class="text-white" href="/inventory/enable_all">Enable All</a>
                                </button>
                                <button type="button" class="btn btn-danger">
                                    <a class="text-white" href="/inventory/disable_all" onclick="disableAll(event)">Disable
                                        All</a>
                                </button>
                                <button type="button" class="btn btn-primary">
                                    <a class="text-white" href="/inventory/add">Add New</a>
                                </button>
                                @if (Auth::id() == 306 || Auth::id() == 365)
                                    <button type="button" class="btn btn-primary">
                                        <a class="text-white" href="/inventory/add_bulk">Add Bulk</a>
                                    </button>
                                @endif
                            </div>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
        @endif
        <!-- /.content-header -->
        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <form action="" class="w-100">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <input type="text" class="form-control" name="search" placeholder="Product Name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <select class="form-control" required name="category" id="">
                                    <option value="">Category*</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <button class="btn btn-primary" type="submit">Search</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            @if (Auth::user()->role->name == 'child_seller')
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-12">
                                <h1 class="m-0 text-dark text-center">Inventory</h1>
                            </div><!-- /.col -->
                        </div><!-- /.row -->
                    </div><!-- /.container-fluid -->
                </div>
                <div class="float-right m-2">
                    <button type="button" class="btn btn-primary">
                        <a class="text-white" href="#">Update Bulk</a>
                    </button>
                </div>
            @endif
            @if (Auth::user()->role->name == 'seller') 
                @if (!empty($featured_products))
                    <div class="content-header">
                        <div class="container-fluid">
                            <div class="row mb-2">
                                <div class="col-sm-12">
                                    <h1 class="m-0 text-dark text-center">Featured</h1>
                                </div><!-- /.col -->
                            </div><!-- /.row -->
                        </div><!-- /.container-fluid -->
                    </div>

                    <!-- /.container-featured-products-begins -->
                    <div class="container-fluid">
                        <div class="row">
                            @foreach ($featured_products as $inventory)
                                <div class="col-md-12 col-lg-6 col-xl-4 pb-4">
                                    <div class="card change-height">
                                        <div class="card-body">
                                            <div class="card-text">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <a href="{{ route('removeFromFeatured', ['product_id' => $inventory->id]) }}"
                                                            title="Remove From Featured"
                                                            class="d-block text-right float-right"><i
                                                                class="far fa-times-circle fa-2x text-danger"></i></a>
                                                        <a href="/inventory/edit/{{ $inventory->id }}" title="Edit"
                                                            class="d-block text-right float-right"><img class="img-size-16"
                                                                src="{{ asset('res/res/img/edit.png') }}"></a>
                                                        @if ($inventory->status == 1)
                                                            <a href="/inventory/disable/{{ $inventory->id }}"
                                                                class=" d-block text-right float-right pr-3 text-danger"><span
                                                                    class="font-weight-bold">Disable</span> (Put Inventory
                                                                0)</a>
                                                        @elseif($inventory->status == 0)
                                                            <a href="/inventory/enable/{{ $inventory->id }}"
                                                                class=" d-block text-right float-right pr-3 text-primary"><span
                                                                    class="font-weight-bold">Enable</span></a>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-9">
                                                        <span class="img-container pt-30 pb-30 mb-3">
                                                            @if (str_contains($inventory->feature_img, 'https://'))
                                                                <img class="d-block m-auto "
                                                                    style="height: 200px;object-fit: contain"
                                                                    src="{{ asset($inventory->feature_img) }}">
                                                            @else
                                                                <img class="d-block m-auto "
                                                                    style="height: 200px;object-fit: contain"
                                                                    src="{{ asset(config('constants.BUCKET') . $inventory->feature_img) }}">
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="col-md-3 mt-1">
                                                        @if ($inventory->images)
                                                            <?php $count = 0; ?>
                                                            @foreach ($inventory->images as $img)
                                                                <?php if ($count == 3) {
                                                                    break;
                                                                } ?>
                                                                <span class="img-container mb-1">
                                                                    @if (str_contains($img->product_image, 'https://'))
                                                                        <img class="d-block m-auto"
                                                                            src="{{ asset($img->product_image) }}">
                                                                    @else
                                                                        <!-- <img class="d-block m-auto" src="{{ asset('user_imgs/' . $img->product_image) }}" > -->
                                                                        <img class="d-block m-auto"
                                                                            src="{{ asset(config('constants.BUCKET') . $img->product_image) }}">
                                                                    @endif
                                                                </span>
                                                                <?php $count++; ?>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xl-6">
                                                    <div class="">
                                                        <h4
                                                            class="d-block text-left p-3 pb-0 m-0 text-site-primary text-lg">
                                                            <a href="/inventory/edit/{{ $inventory->id }}"
                                                                class="d-block text-site-primary">{{ $inventory->product_name }}</a>

                                                        </h4>
                                                    </div>
                                                </div>
                                                <div class="col-xl-1">
                                                    <div class="">
                                                        <h4
                                                            class="d-block text-left p-3 pb-0 m-0 text-site-primary text-lg">
                                                            <?php
                                                if ($inventory->colors) {
                                                    $colors = json_decode($inventory->colors, true);
                                                    foreach ($colors as $c_key => $color) :
                                                ?>
                                                            <span class="color-circle color-{{ $c_key }}"
                                                                style="background: {{ $c_key }}"></span>
                                                            <?php
                                                    endforeach;
                                                }
                                                ?>
                                                        </h4>
                                                    </div>
                                                </div>
                                                <div class="col-xl-5">
                                                    <div class="">
                                                        <h6
                                                            class="d-block text-left p-3 pb-0 m-0 text-site-primary text-lg">
                                                            <a href="#"
                                                                class="d-block text-site-primary">SKU:{{ $inventory->sku }}</a>
                                                        </h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="ratting pl-3">
                                                        <?php
                                            $rating = round($inventory->ratting['average']);
                                            for ($i = 1; $i <= 5; $i++) :
                                            ?>
                                                        <span
                                                            class="fa fa-star 
                                        <?php if ($i <= $rating) {
                                            echo 'checked';
                                        } ?>">
                                                        </span>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                                <!--  -->
                                                <div class="col-md-6">
                                                    <div class="ratting pl-3 text-right text-bold">
                                                        @if ($inventory->discount_percentage == 0.0)
                                                            <span
                                                                class="text-lg text-primary">£{{ $inventory->price }}</span>
                                                        @else
                                                            <del
                                                                class="text-danger d-block">£{{ $inventory->price }}</del>
                                                            <span class="text-lg text-primary">£
                                                                <?php echo $inventory->price - ($inventory->discount_percentage / 100) * $inventory->price; ?>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <!-- /.row -->
                    </div>
                    <!-- /.container-featured-products-ends -->
                @endif
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-12">
                                <h1 class="m-0 text-dark text-center">Inventory</h1>
                            </div><!-- /.col -->
                        </div><!-- /.row -->
                    </div><!-- /.container-fluid -->
                </div>
                <!-- /.container-products-begins -->
               
              
               
               
               
               
               
               
               
               
               
               
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        @foreach ($inventories as $inventory)
                        
                            <!-- Single Product -->
						<div class="col-md-3 col-sm-3 bg-white mx-3 my-3 p-3">
                                    
                                    
                                            <h3 class="product-title">Here Product Title</h3>
                                            <h4 class="product-old-price">$79.99</h4>
                                            <h4 class="product-price">$49.99</h4>
                                    
                            
                    </div>
                    
                        @endforeach
                    </div>
                    <!-- /.row -->
                    <div class="row">
                        <div class="col-md-12">
                            {{ $inventory_p->links() }}
                        </div>
                    </div>
                </div>
                <!-- /.container-products-ends -->
        </div>
    @elseif(Auth::user()->role->name == 'child_seller')
        <table class="table align-middle mb-0 bg-white">
            <thead class="bg-light">
                <tr>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Qty</th>
                    <th>Action</th>
                </tr>
            </thead>
            @foreach ($inventories as $key => $inventory)
                <tbody>
                    <tr>
                        <td class="col-md-2" style="padding:0px;">
                            <div class="d-flex align-items-center img-container pt-30 pb-30 mb-3"
                                style="border-radius:5%">
                                @if (str_contains($inventory->feature_img, 'https://'))
                                    <img class="d-block m-auto "
                                        style="height: 140px;object-fit: contain;border-radius:5% "
                                        src="{{ asset($inventory->feature_img) }}">
                                @else
                                    <img class="d-block m-auto " style="height: 200px;object-fit: contain"
                                        src="{{ asset(config('constants.BUCKET') . $inventory->feature_img) }}">
                                @endif
                            </div>
                        </td>
                        <td>
                            <p class="fw-normal mb-1">{{ $inventory->product_name }}</p>
                        </td>

                        <form action="{{ route('update_child_qty') }}" class="updateQty" method="post">
                            @csrf
                            <td>
                                <?php
                                $q['qty'] = 0;
                                ?>
                                @foreach ($inventory->quantities as $quantity)
                                    {{-- Product id == $inventory->id --}}
                                    @if ($quantity->users_id == Auth::id() && $quantity->products_id == $inventory->id)
                                        <?php
                                        $q['qty'] = $quantity->qty;
                                        ?>
                                    @endif
                                @endforeach
                                <input class="form-control qtyInput" min="0" style="width:80px;" type="number"
                                    name="qty" id="qty" value="<?php echo !empty($q['qty']) ? $q['qty'] : 0; ?>">
                                <input type="hidden" id="product_id" name="product_id" value="{{ $inventory->id }}">
                                {{-- The following code was unnecessary --}}
                                {{-- <input type="hidden" id="parent_id" name="parent_id"
                                    value="{{ $inventory->user_id }}"> --}}
                                {{-- <input type="hidden" id="qty_id" name="qty_id" value="<?php echo $q['qty_id']; ?>"> --}}
                            </td>
                            <td>
                                <button class="btn btn-success" type="submit">Update</button>
                            </td>
                        </form>
                    </tr>
                </tbody>
            @endforeach
        </table>

        <div class="row">
            <div class="col-md-12">
                {{ $inventory_p->links() }}
            </div>
        </div>
        @endif
        <!-- /.content -->

    </div>
    </div>
@endsection
@section('scripts')
    <script>
        function disableAll(ev) {
            ev.preventDefault();
            var urlToRedirect = ev.currentTarget.getAttribute(
                'href'
            ); //use currentTarget because the click may be on the nested i tag and not a tag causing the href to be empty
            Swal.fire({
                title: 'Warning!',
                text: 'Are you sure you want to disable all the products of your store?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed)
                    window.location.href = urlToRedirect
            });
        }
    </script>
@endsection
