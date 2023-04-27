<div class="container-xxl flex-grow-1 container-p-y">
    @if (session()->has('error'))
        <div class="bs-toast toast toast-placement-ex m-2 fade bg-danger top-0 end-0 show" role="alert"
            aria-live="assertive" aria-atomic="true" data-delay="2000">
            <div class="toast-header">
                <i class="bx bx-bell me-2"></i>
                <div class="me-auto fw-semibold">Error</div>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session()->get('error') }}
            </div>
        </div>
    @endif
    @if (session()->has('success'))
        <div class="bs-toast toast toast-placement-ex m-2 fade bg-success top-0 end-0 show" role="alert"
            aria-live="assertive" aria-atomic="true" data-delay="2000">
            <div class="toast-header">
                <i class="bx bx-bell me-2"></i>
                <div class="me-auto fw-semibold">Success</div>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session()->get('success') }}
            </div>
        </div>
    @endif
    {{-- ************************************ Add Model ************************************ --}}
    {{-- <div wire:ignore.self class="modal fade" id="basicModal" tabindex="-1" aria-labelledby="basicModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title" id="basicModalLabel">Add Employee</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
            wire:click="resetModal"></button>
        </div>
        <form wire:submit.prevent="saveEmployee">
        <div class="modal-body">
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" wire:click="resetModal">
        Close
        </button>
        <button type="submit" class="btn btn-primary">Submit</button>
        </div>
        </form>
        </div>
        </div>
        </div> --}}
    {{-- ************************************ Info Model ************************************ --}}
    <div wire:ignore.self class="modal fade" id="infoModel" tabindex="-1" aria-labelledby="infoModelLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModelLabel">Seller Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="resetModal"></button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="table-responsive text-nowrap">

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                            wire:click="resetModal">
                            Close
                        </button>
                        {{-- <button type="submit" class="btn btn-primary" wire:click.prevent="submitForm('edit')"
                            wire:loading.class="btn-dark" wire:loading.class.remove="btn-primary"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove>Update</span>
                            <span wire:loading>
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </span>
                        </button> --}}
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- ************************************ Delete Model ************************************ --}}
    {{-- <div wire:ignore.self class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="resetModal"></button>
                </div>
                <form wire:submit.prevent="destroy">
                    <div class="modal-body">
                        <h4>Are you sure you want to delete this data?</h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal"
                            wire:click="resetModal">
                            No
                        </button>
                        <button type="submit" class="btn btn-danger" wire:loading.class="btn-dark"
                            wire:loading.class.remove="btn-danger" wire:loading.attr="disabled">
                            <span wire:loading.remove>Delete</span>
                            <span wire:loading>
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div> --}}

    <div class="row">
        <div class="col-12 col-sm-3 col-md-4 col-lg-6">
            <h4 class="py-4 my-1">Inventory</h4>
        </div>
        <div class="col-12 col-sm-6 col-md-6 col-lg-5">
            <div class="row">
                <div class="col-sm-12 col-md-6 py-4 my-2">
                    <input type="text" wire:model.debounce.500ms="search" class="form-control py-3"
                        placeholder="Search here...">
                </div>
                <div class="col-sm-12 col-md-6 py-4 my-2">
                    <select class="form-control" wire:model.debounce.500ms="category_id">
                        <option value="">Select category</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-3 col-md-2 col-lg-1">
            <button type="button" class="btn btn-primary my-3 py-3 w-100" title="Update bulk">
                <i class="fas fa-angle-double-up"></i>
            </button>
        </div>
    </div>

    <div class="container">
        <div class="row">
            @if (Auth::user()->role->name == 'child_seller')
                <table class="table">
                    <thead class="bg-light">
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Qty</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inventories as $key => $inventory)
                            <tr class="bg-white">
                                <td class="align-middle fit-content">
                                    @if (str_contains($inventory->feature_img, 'https://'))
                                        <img class="img-fluid rounded standard-img-size"
                                            src="{{ asset($inventory->feature_img) }}">
                                    @else
                                        <img class="img-fluid rounded-pill standard-img-size "
                                            src="{{ asset(config('constants.BUCKET') . $inventory->feature_img) }}">
                                    @endif
                                </td>
                                <td class="align-middle fit-content">
                                    <p class="fw-normal mb-1">{{ $inventory->product_name }}</p>
                                </td>
                               
                                <td class="align-middle fit-content">

                                    <?php $q['qty'] = 0;
                                    ?>

                                    
                                        {{-- Product id == $inventory->id --}}
                                        
                                            
                                        
                                    @foreach ($inventory->quantities as $quantity)
                                    @if ($quantity->users_id == Auth::id() && $quantity->products_id == $inventory->id)

                                    @endif
                                    @endforeach
                                    <input class="form-control qtyInput" min="0" style="width:80px;"
                                        type="number" wire:model="qty.{{$inventory->id }}" id="qty" value='123'
                                        wire:key="{{$inventory->id }}">
                                        
                                    <input type="hidden" id="product_id" wire:model="product_id"
                                        value="{{$inventory->id }}">
                                </td>
                                <td class="align-middle fit-content">
                                    <button class="btn btn-success" type="button" data-bs-toggle="tooltip" title="Update" wire:click="updateQuantity({{ $inventory->id }})">
                                        <i class="fas fa-sync"></i>
                                    </button>

                                </td>
                          
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <h4 class="text-dark">No Products Found :(</h4>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="row">
                    <div class="col-md-12">
                        {{ $inventory_p->links() }}
                    </div>
                </div>
            @endif
        </div>
        <div class="row">
            <!-- {{ $inventories->links() }} -->
        </div>
    </div>

</div>


{{-- Sabah's Code --}}
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
                                <a class="text-white" href="/inventory/disable_all"
                                    onclick="disableAll(event)">Disable
                                    All</a>
                            </button>
                            <button type="button" class="btn btn-primary">
                                <a class="text-white" href="/inventory/add">Add New</a>
                            </button>
                            <!-- @if (Auth::id() == 306 || Auth::id() == 365)
<button type="button" class="btn btn-primary">
                                    <a class="text-white" href="/inventory/add_bulk">Add Bulk</a>
                                </button>
@endif -->
                        </div>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
    @endif
    <!-- /.content-header -->
    <!-- Main content -->
    <div class="content">
        @if (Auth::user()->role->name == 'child_seller')
            {{-- <div class="content-header">
                <div class="container-fluid">
                    <div class="d-flex align-items-center">
                        <div class="me-auto" style="border:1px solid red;">
                            <h4 class="py-4 my-1">Inventory</h4>
                        </div>
                        <div class="ms-auto col-md-8" style="border:1px solid red;">
                            <form method="post">
                                <div class="input-group py-4 my-2">
                                    <input type="text" wire:model.debounce.500ms="product"
                                        class="form-control py-3" placeholder="Search here...">
                                    &nbsp; &nbsp;
                                    <select class="form-control" required name="category"
                                        wire:model.debounce.500ms="category" id="">
                                        <option value="">Category*</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div>
                            &nbsp; <button class="btn btn-primary" data-bs-toggle="tooltip" title="Update Bulk"><i
                                    class="fa">&#xf021;</i></button>
                        </div>
                    </div>
                </div><!-- /.container-fluid -->
            </div> --}}
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
                        @forelse ($featured_products as $inventory)
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
                                                        class="d-block text-right float-right"><img
                                                            class="img-size-16"
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
                        @empty
                            <h1 class="text-dark">No Products Found :(</h1>
                        @endforelse


                    </div>
                    <!-- /.row -->
                </div>
                <!-- /.container-featured-products-ends -->
            @endif
            {{-- <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <h1 class="m-0 text-dark text-center">Inventory</h1>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div> --}}
            <!-- /.container-products-begins -->
            <div class="container-fluid">
                <div class="row">
                    @foreach ($inventories as $inventory)
                        <div class="col-md-12 col-lg-6 col-xl-4 pb-4">
                            <div class="card change-height">
                                <div class="card-body">
                                    <a href="" class=" d-block text-right">
                                        <div class="card-text">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <a href="{{ route('markAsFeatured', ['product_id' => $inventory->id]) }}"
                                                        title="Mark As Featured"
                                                        class="d-block text-right float-right"><i
                                                            class="fas fa-star fa-2x text-warning"></i></a>
                                                    <a href="/inventory/edit/{{ $inventory->id }}" title="Edit"
                                                        class="d-block text-right float-right"><img
                                                            class="img-size-16"
                                                            src="{{ asset('res/res/img/edit.png') }}"></a>
                                                    @if ($inventory->status == 1)
                                                        <a href="/inventory/disable/{{ $inventory->id }}"
                                                            class="d-block text-right float-right pr-3 text-danger"><span
                                                                class="font-weight-bold">Disable</span> (Put Inventory
                                                            0)</a>
                                                    @elseif($inventory->status == 0)
                                                        <a href="/inventory/enable/{{ $inventory->id }}"
                                                            class="d-block text-right float-right pr-3 text-primary"><span
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
                                                            <!-- <img class="d-block m-auto " style="height: 200px;object-fit: contain" src="{{ asset('user_imgs/' . $inventory->feature_img) }}" > -->
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
                                                        <a href=""
                                                            class="d-block text-site-primary">({{ $inventory->category->category_name }}
                                                            )</a>
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
                <div class="row">
                    <div class="col-md-12">
                        {{ $inventory_p->links() }}
                    </div>
                </div>
            </div>
            <!-- /.container-products-ends -->
    </div>
    @endif
{{--  --}}
    
    <!-- /.content -->
</div>
<style>
    
</style>
