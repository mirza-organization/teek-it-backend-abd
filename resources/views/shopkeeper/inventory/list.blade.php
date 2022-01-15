@extends('layouts.shopkeeper.app')
@section('content')
<<<<<<< HEAD
    <div class="content">

        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark text-center">Inventory</h1>
                        <a class="float-right" href="/inventory/add">Add New</a>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
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
                                    @foreach($categories as $cat)

                                        <option
                                            value="{{$cat->id}}">{{$cat->category_name}}</option>
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
            <div class="container-fluid">
                <div class="row">
                    <?php
                    ?>
                    @foreach($inventories as $inventory)
                        <div class="col-md-12 col-lg-6 col-xl-4  pb-4">
                            <div class="card change-height">
                                <div class="card-body">
                                    <a href="" class=" d-block text-right">
                                        <div class="card-text">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <a href="/inventory/edit/{{$inventory->id}}"
                                                       class=" d-block text-right float-right"><img class="img-size-16"
                                                                                                    src="{{asset('res/res/img/edit.png')}}"
                                                                                                    alt=""></a>
                                                    @if($inventory->status==1)
                                                        <a href="/inventory/disable/{{$inventory->id}}"
                                                           class=" d-block text-right float-right pr-3 text-danger"><span
                                                                class="font-weight-bold">Disable</span> (Put Inventory
                                                            0)</a>
                                                    @elseif($inventory->status==0)
                                                        <a href="/inventory/enable/{{$inventory->id}}"
                                                           class=" d-block text-right float-right pr-3 text-primary"><span
                                                                class="font-weight-bold">Enable</span></a>
                                                    @endif
                                                </div>
                                                <div class="col-md-9">
                                                        <span class="img-container pt-30 pb-30 mb-3">
                    <img class="d-block m-auto " style="height: 200px;object-fit: contain"
                         src="{{asset($inventory->feature_img)}}" alt="">
                    </span>
                                                </div>


                                                <div class="col-md-3 mt-1">
                                                    @if($inventory->images)
                                                        <?php $count = 0; ?>
                                                        @foreach($inventory->images as $img)
                                                            <?php if ($count == 3) break; ?>

                                                            <span class="img-container mb-1">
                    <img class="d-block m-auto" src="{{asset($img->product_image)}}" alt="">
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
                                                    <h4 class="d-block text-left p-3 pb-0 m-0 text-site-primary text-lg">
                                                        <a href="/inventory/edit/{{$inventory->id}}"
                                                           class="d-block text-site-primary">{{$inventory->product_name}}</a>
                                                        <a href=""
                                                           class="d-block text-site-primary">({{$inventory->category->category_name}}
                                                            )</a>
                                                    </h4>
                                                </div>
                                            </div>
                                            <div class="col-xl-1">
                                                <div class="">
                                                    <h4 class="d-block text-left p-3 pb-0 m-0 text-site-primary text-lg">
                                                        <?php

                                                        if ($inventory->colors){
                                                        $colors = json_decode($inventory->colors, true);
                                                        foreach ($colors as $c_key=>$color):
                                                        ?>
                                                        <span class="color-circle color-{{$c_key}}"
                                                              style="background: {{$c_key}}"></span>
                                                        <?php
                                                        endforeach;
                                                        }
                                                        ?>
                                                    </h4>
                                                </div>
                                            </div>
                                            <div class="col-xl-5">
                                                <div class="">
                                                    <h6 class="d-block text-left p-3 pb-0 m-0 text-site-primary text-lg">
                                                        <a href="#"
                                                           class="d-block text-site-primary">{{$inventory->sku}}</a>
                                                    </h6>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="ratting pl-3">
                                                    <?php
                                                    $rating = round($inventory->ratting['average']);
                                                    for ($i = 1;$i <= 5;$i++):
                                                    ?>
                                                    <span class="fa fa-star <?php if ($i <= $rating) {
                                                        echo "checked";
                                                    } ?>"></span>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="ratting pl-3 text-right text-bold">
                                                    <del class="text-danger d-block">£{{$inventory->price}}</del>
                                                    <span
                                                        class="text-lg text-primary">£{{$inventory->sale_price}}</span>
                                                </div>
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
                        {{ $inventory_p->links() }}
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
@endsection
=======
<div class="content">

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark text-center">Inventory</h1>
                    <a class="float-right add-prod-btn" href="/inventory/add_bulk">Add Bulk</a>
                    <a class="float-right add-prod-btn" href="/inventory/add">Add New</a>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
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
                                @foreach($categories as $cat)
                                <option value="{{$cat->id}}">{{$cat->category_name}}</option>
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
        <div class="container-fluid">
            <div class="row">
                @foreach($inventories as $inventory)
                <div class="col-md-12 col-lg-6 col-xl-4  pb-4">
                    <div class="card change-height">
                        <div class="card-body">
                            <a href="" class=" d-block text-right">
                                <div class="card-text">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <a href="/inventory/edit/{{$inventory->id}}" class=" d-block text-right float-right"><img class="img-size-16" src="{{asset('res/res/img/edit.png')}}" alt=""></a>
                                            @if($inventory->status==1)
                                            <a href="/inventory/disable/{{$inventory->id}}" class=" d-block text-right float-right pr-3 text-danger"><span class="font-weight-bold">Disable</span> (Put Inventory
                                                0)</a>
                                            @elseif($inventory->status==0)
                                            <a href="/inventory/enable/{{$inventory->id}}" class=" d-block text-right float-right pr-3 text-primary"><span class="font-weight-bold">Enable</span></a>
                                            @endif
                                        </div>
                                        <div class="col-md-9">
                                            <span class="img-container pt-30 pb-30 mb-3">
                                                <img class="d-block m-auto " style="height: 200px;object-fit: contain" src="{{asset($inventory->feature_img)}}" alt="">
                                            </span>
                                        </div>
                                        <div class="col-md-3 mt-1">
                                            @if($inventory->images)
                                            <?php $count = 0; ?>
                                            @foreach($inventory->images as $img)
                                            <?php if ($count == 3) break; ?>
                                            <span class="img-container mb-1">
                                                <img class="d-block m-auto" src="{{asset($img->product_image)}}" alt="">
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
                                            <h4 class="d-block text-left p-3 pb-0 m-0 text-site-primary text-lg">
                                                <a href="/inventory/edit/{{$inventory->id}}" class="d-block text-site-primary">{{$inventory->product_name}}</a>
                                                <a href="" class="d-block text-site-primary">({{$inventory->category->category_name}}
                                                    )</a>
                                            </h4>
                                        </div>
                                    </div>
                                    <div class="col-xl-1">
                                        <div class="">
                                            <h4 class="d-block text-left p-3 pb-0 m-0 text-site-primary text-lg">
                                                <?php
                                                if ($inventory->colors) {
                                                    $colors = json_decode($inventory->colors, true);
                                                    foreach ($colors as $c_key => $color) :
                                                ?>
                                                        <span class="color-circle color-{{$c_key}}" style="background: {{$c_key}}"></span>
                                                <?php
                                                    endforeach;
                                                }
                                                ?>
                                            </h4>
                                        </div>
                                    </div>
                                    <div class="col-xl-5">
                                        <div class="">
                                            <h6 class="d-block text-left p-3 pb-0 m-0 text-site-primary text-lg">
                                                <a href="#" class="d-block text-site-primary">SKU:{{$inventory->sku}}</a>
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="ratting pl-3">
                                            <?php
                                            $rating = round($inventory->ratting['average']);
                                            for ($i = 1; $i <= 5; $i++) :
                                            ?>
                                                <span class="fa fa-star 
                                                <?php if ($i <= $rating) 
                                                {echo "checked"; } ?>">
                                                </span>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="ratting pl-3 text-right text-bold">
                                            @if ($inventory->discount_percentage == 0.00)
                                            <span class="text-lg text-primary">£{{$inventory->price}}</span>
                                            @else
                                            <del class="text-danger d-block">£{{$inventory->price}}</del>
                                            <span class="text-lg text-primary">£
                                                <?php echo $inventory->price - (($inventory->discount_percentage / 100) * $inventory->price); ?>
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
            <div class="row">
                <div class="col-md-12">
                    {{ $inventory_p->links() }}
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
@endsection
>>>>>>> bc40bab051467a571c4fee195a934ea1931e57a7
