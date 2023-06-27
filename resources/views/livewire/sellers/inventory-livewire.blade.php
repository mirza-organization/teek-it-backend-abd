@php
    use Illuminate\Support\Str;
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong>
            {{ session()->get('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong>
            {{ session()->get('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (Auth::user()->role->name == 'seller')
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-12 col-sm-6 col-md-4">
                    &nbsp;
                </div>
                <div class="col-12 col-sm-6 col-md-8 ">
                    <div class="row">
                        <div class="col-sm-12 col-md-3 py-4 my-2">
                            <input type="text" wire:model.debounce.500ms="search" class="form-control py-3"
                                placeholder="Search here...">
                        </div>
                        <div class="col-sm-12 col-md-3 py-4 my-2">
                            <select class="form-control" wire:model.debounce.500ms="category_id">
                                <option value="">Select category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex ">
                            <button type="button" class="btn btn-warning my-4 p-1 w-100 mx-1 rounded"
                                wire:click="toggleAllProducts(1)" title="Enable All">
                                <i class="fa fa-toggle-on"></i>
                            </button>
                            <a type="button" class="btn btn-danger text-white py-3 my-4 p-1 w-100 mx-1"
                                onclick="disableAll(event)" title="Disable All">
                                <i class="fas fa-ban"></i>
                            </a>
                            <a type="button" href="/inventory/add" class="btn btn-primary my-4 py-3 w-100 mx-1 px-0 "
                                title="Add New">
                                <i class="fas fa-plus"></i>
                            </a>
                            <a type="button" href="/inventory/add_bulk"
                                class="btn btn-primary my-4 py-3 w-100 mx-1 px-0" title="Add Bulk">
                                <i class="fas fa-plus"></i> <i class="fas fa-boxes"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <section class="section-products">
                <div class="container">
                    {{-- Featured Products - Begins --}}
                    @if (count($featured_products) > 0)
                        <div class="row">
                            <div class="col-lg-12 col-sm-12 col-md-12">
                                <h4 class="py-4 my-1">Featured</h4>
                            </div>
                            @foreach ($featured_products as $inventory)
                                <!-- Single Product -->
                                <div class="col-md-6 col-lg-4 col-xl-3 p-2 ">
                                    <div id="productItem" class="single-product bg-white p-2"
                                        @if ($inventory->status == 0) style="background:#ccc !important;" @endif>
                                        <div class="part-1"
                                            style="background:url('{{ asset($inventory->feature_img) }}') no-repeat center; ">
                                            <ul>
                                                @if ($inventory->status == 0)
                                                    <li>
                                                        <a wire:click="toggleProduct('{{ $inventory->id }}', 1)"
                                                            title="Enable Product">
                                                            <i class="fa fa-toggle-on"></i>
                                                        </a>
                                                    </li>
                                                @elseif($inventory->status == 1)
                                                    <li>
                                                        <a wire:click="toggleProduct('{{ $inventory->id }}', 0)"
                                                            title="Disable Product">
                                                            <i class="fa fa-ban"></i>
                                                        </a>
                                                    </li>
                                                @endif
                                                <li>
                                                    <a href="/inventory/edit/{{ $inventory->id }}" title="Edit Product">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a wire:click="markAsFeatured('{{ $inventory->id }}', '0')"
                                                        title="Undo Featured">
                                                        <i class="fa fa-undo" aria-hidden="true"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="part-2 px-2">
                                            <div class="col">
                                                <h2 class="product-title" style="color:#3a4b83;"
                                                    title="{{ $inventory->product_name }}">
                                                    {{ Str::limit($inventory->product_name, 25) }}
                                                </h2>
                                                <h5>{{ $inventory->category->category_name }}</h5>
                                                <h4>SKU: {{ $inventory->sku }}</h4>
                                                <div>
                                                    <?php $rattings = app\Rattings::getRatting($inventory->id); ?>
                                                    @if (!empty($rattings['average']))
                                                        <?php $star = round($rattings['average']); ?>
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <span
                                                                class="fa fa-star @if ($i <= $star) checked @endif">
                                                            </span>
                                                        @endfor
                                                    @endif
                                                </div>
                                                <h5>${{ $inventory->price }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    {{-- Featured Products - Ends --}}

                    <div class="row">
                        <div class="col-lg-12 col-sm-12 col-md-12">
                            <h4 class="py-4 my-1">Inventory</h4>
                        </div>
                        @forelse ($data as $key => $inventory)
                            <!-- Single Product -->
                            <div class="col-md-6 col-lg-4 col-xl-3 p-2  ">
                                <div id="productItem" class="single-product bg-white p-2 rounded"
                                    @if ($inventory->status == 0) style="background:#ccc !important;" @endif>
                                    <div class="part-1 rounded"
                                        style=" background:url('{{ asset($inventory->feature_img) }}') no-repeat center; ">
                                        {{-- <span class="discount">15% off</span>
                                               <span class="new">new</span> --}}
                                        <ul>
                                            @if ($inventory->status == 0)
                                                <li>
                                                    <a wire:click="toggleProduct('{{ $inventory->id }}', 1)"
                                                        title="Enable Product">
                                                        <i class="fa fa-toggle-on"></i>
                                                    </a>
                                                </li>
                                            @elseif($inventory->status == 1)
                                                <li>
                                                    <a wire:click="toggleProduct('{{ $inventory->id }}', 0)"
                                                        title="Disable Product">
                                                        <i class="fa fa-ban"></i>
                                                    </a>
                                                </li>
                                            @endif
                                            <li>
                                                <a href="/inventory/edit/{{ $inventory->id }}" title="Edit Product">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            </li>
                                            <li>
                                                <a wire:click="markAsFeatured('{{ $inventory->id }}', '1')"
                                                    title="Mark as Featured">
                                                    <i class="fa fa-star"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="part-2 px-2">
                                        <h3 class="product-title" style="color:#3a4b83;"
                                            title="{{ $inventory->product_name }}">
                                            {{ Str::limit($inventory->product_name, 25) }}
                                        </h3>
                                        <h5 class="rating">{{ $inventory->category->category_name }}</h5>
                                        <h4>SKU: {{ $inventory->sku }}</h4>
                                        <div class="col-md-6">
                                            <div class="ratting pl-3">
                                                <?php $rattings = app\Rattings::getRatting($inventory->id); ?>
                                                @if (!empty($rattings['average']))
                                                    <?php $star = round($rattings['average']); ?>
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <span
                                                            class="fa fa-star @if ($i <= $star) checked @endif">
                                                        </span>
                                                    @endfor
                                                @endif
                                            </div>
                                        </div>
                                        <h5>${{ $inventory->price }}</h5>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <h4 class="text-dark text-center p-2">No Products Found :(</h4>
                        @endforelse
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            {{ $data->links() }}
                        </div>
                    </div>
                </div>
            </section>
        </div>
    @elseif (Auth::user()->role->name == 'child_seller')
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
                            @forelse ($data as $key => $inventory)
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
                                        <input type="number" class="form-control" style="width:80px;"
                                            min="0" wire:model.defer="quantity.{{ $key }}.qty">
                                    </td>
                                    <td class="align-middle fit-content">
                                        <button class="btn btn-success" type="button" data-bs-toggle="tooltip"
                                            title="Update" wire:click="updateProductQuantity({{ $key }})">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <h4 class="text-dark text-center p-2">No Products Found :(</h4>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-md-12">
                            {{ $data->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
    <button type="button" id="DisableAll" wire:click="toggleAllProducts(0)" style="display:none;"></button>
</div>
