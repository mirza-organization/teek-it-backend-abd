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

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap');

body {
    font-family: "Poppins", sans-serif;
    color: #444444;
}

a,
a:hover {
    text-decoration: none;
    color: inherit;
}

.section-products {
    padding: 0 0 24px;
}

.section-products .header {
    margin-bottom: 50px;
}

.section-products .header h3 {
    font-size: 1rem;
    color: #fe302f;
    font-weight: 500;
}

.section-products .header h2 {
    font-size: 2.2rem;
    font-weight: 400;
    color: #444444; 
}

.section-products .single-product {
    margin-bottom: 26px;
}

.section-products .single-product .part-1 {
    position: relative;
    height: 290px;
    max-height: 290px;
    margin-bottom: 20px;
    overflow: hidden;
}

.section-products .single-product .part-1::before {
		position: absolute;
		content: "";
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		z-index: -1;
		transition: all 0.3s;
}

.section-products .single-product:hover .part-1::before {
		transform: scale(1.2,1.2) rotate(5deg);
}

.section-products #product-1 .part-1::before {
    background: url("https://i.ibb.co/L8Nrb7p/1.jpg") no-repeat center;
    background-size: cover;
		transition: all 0.3s;
}

.section-products #product-2 .part-1::before {
    background: url("https://i.ibb.co/cLnZjnS/2.jpg") no-repeat center;
    background-size: cover;
}

.section-products #product-3 .part-1::before {
    background: url("https://i.ibb.co/L8Nrb7p/1.jpg") no-repeat center;
    background-size: cover;
}

.section-products #product-4 .part-1::before {
    background: url("https://i.ibb.co/cLnZjnS/2.jpg") no-repeat center;
    background-size: cover;
}

.section-products .single-product .part-1 .discount,
.section-products .single-product .part-1 .new {
    position: absolute;
    top: 15px;
    left: 20px;
    color: #ffffff;
    background-color: #fe302f;
    padding: 2px 8px;
    text-transform: uppercase;
    font-size: 0.85rem;
}

.section-products .single-product .part-1 .new {
    left: 0;
    background-color: #444444;
}

.section-products .single-product .part-1 ul {
    position: absolute;
    bottom: -41px;
    left: 20px;
    margin: 0;
    padding: 0;
    list-style: none;
    opacity: 0;
    transition: bottom 0.5s, opacity 0.5s;
}

.section-products .single-product:hover .part-1 ul {
    bottom: 30px;
    opacity: 1;
}

.section-products .single-product .part-1 ul li {
    display: inline-block;
    margin-right: 4px;
}

.section-products .single-product .part-1 ul li a {
    display: inline-block;
    width: 40px;
    height: 40px;
    line-height: 40px;
    background-color: #ffffff;
    color: #444444;
    text-align: center;
    box-shadow: 0 2px 20px rgb(50 50 50 / 10%);
    transition: color 0.2s;
}

.section-products .single-product .part-1 ul li a:hover {
    color: #fe302f;
}

.section-products .single-product .part-2 .product-title {
    font-size: 1rem;
}

.section-products .single-product .part-2 h4 {
    display: inline-block;
    font-size: 1rem;
}

.section-products .single-product .part-2 .product-old-price {
    position: relative;
    padding: 0 7px;
    margin-right: 2px;
    opacity: 0.6;
}

.section-products .single-product .part-2 .product-old-price::after {
    position: absolute;
    content: "";
    top: 50%;
    left: 0;
    width: 100%;
    height: 1px;
    background-color: #444444;
    transform: translateY(-50%);
}
        </style>
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
                        $("#DisableAll").click();
                    });
            }
        </script>
    
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
                        <button type="button" class="btn btn-warning my-4 p-1 w-100 mx-1 rounded" wire:click="toggleAllProducts(1)" title="Enable All">
                            <i class="fa fa-toggle-on"></i>
                        </button>
                        <a type="button" class="btn btn-danger text-white py-3 my-4 p-1 w-100 mx-1"  onclick="disableAll(event)" title="Disable All">
                            <i class="fas fa-ban"></i>
                        </a>
                        <a type="button"  href="/inventory/add" class="btn btn-primary my-4 py-3 w-100 mx-1 px-0 " title="Add New">
                            <i class="fas fa-plus"></i>
                        </a>
                        <a type="button"  href="/inventory/add_bulk" class="btn btn-primary my-4 py-3 w-100 mx-1 px-0" title="Add Bulk">
                            <i class="fas fa-plus"></i> <i class="fas fa-boxes"></i>
                        </a>
                    </div>
                </div>
            </div>
            
        </div>
        <section class="section-products" >
            <div class="container">
        @php unset($featured_products[0]); @endphp
        @if(count($featured_products) > 0)
        
        <div class="row">
            <div class="col-lg-12 col-sm-12 col-md-12">
                <h4 class="py-4 my-1">Featured</h4>
            </div>
            @foreach ($featured_products as $key => $inventory)
            
                <!-- Single Product -->
                <div class="col-md-6 col-lg-4 col-xl-3 p-2 ">
                        <div id="productItem" class="single-product bg-white p-2" @if ($inventory->status == 1) style="background:#ccc !important;" @endif >
                                <div class="part-1" style=" background:url('{{asset($inventory->feature_img)}}') no-repeat center; ">
                                    {{-- <span class="discount">15% off</span>
                                    <span class="new">new</span> --}}
                                        <ul>
                                            @if ($inventory->status == 1)
                                            <li><a wire:click="toggleProduct('{{ $inventory->id }}', 0)"  title="Enable Product" ><i class="fa fa-ban"></i></a></li>
                                        @elseif($inventory->status == 0)
                                        <li><a  wire:click="toggleProduct('{{ $inventory->id }}', 1)"  title="Disable Product" ><i class="fa fa-toggle-on"></i></a></li>
                                        @endif
                                                
                                                <li><a href="/inventory/edit/{{ $inventory->id }}" title="Edit Product"><i class="fa fa-edit"></i></a></li>
                                                <li><a wire:click="markAsFeatured('{{ $inventory->id }}', '0')"  title="Unmark as Featured">
                                                    <i class="fa fa-undo" aria-hidden="true"></i></a>
                                                </li>
                                                
                                        </ul>
                                </div>
                                <div class="part-2 px-2">
                                        
                                        <div class="col">
                                            <h2 class="product-title" style="color:#3a4b83;" title="{{$inventory->product_name}}">{{ Str::limit($inventory->product_name, 19) }}</h2>
                                            <h5 class="rating">{{$inventory->category['category_name']}}</h5>
                                            <div class="ratting">
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
                                            <h4 class="product-price">SKU: {{$inventory->sku}}</h4> 
                                        <h5 class="product-price">${{$inventory->price}}</h5>
                                        </div>
                                        
                                </div>
                        </div>
                </div>
                
                    
            @endforeach
        </div>
   
@endif
               
                        
                            <div class="row">
                                <div class="col-lg-12 col-sm-12 col-md-12">
                                    
                <h4 class="py-4 my-1">Inventory</h4>
                       </div>
                                @forelse ($data as $key => $inventory)
                                {{-- {{dd($inventory)}} --}}
                                    <!-- Single Product -->
                                    <div class="col-md-6 col-lg-4 col-xl-3 p-2  ">
                                            <div id="productItem" class="single-product bg-white p-2 rounded" @if ($inventory->status == 1) style="background:#ccc !important;" @endif>
                                                    <div class="part-1 rounded" style=" background:url('{{asset($inventory->feature_img)}}') no-repeat center; ">
                                                        {{-- <span class="discount">15% off</span>
                                                        <span class="new">new</span> --}}
                                                            <ul>
                                                                @if ($inventory->status == 1)
                                            <li><a wire:click="toggleProduct('{{ $inventory->id }}', 0)"  title="Enable Product" ><i class="fa fa-ban"></i></a></li>
                                        @elseif($inventory->status == 0)
                                        <li><a wire:click="toggleProduct('{{ $inventory->id }}', 1)"  title="Disable Product" ><i class="fa fa-toggle-on"></i></a></li>
                                        @endif
                                                                    
                                                                    <li><a href="/inventory/edit/{{ $inventory->id }}" title="Edit Product"><i class="fa fa-edit"></i></a></li>
                                                                    <li><a wire:click="markAsFeatured('{{ $inventory->id }}', '1')" title="Mark as Featured"><i class="fa fa-star"></i></a></li>
                                                                    
                                                            </ul>
                                                    </div>
                                                    <div class="part-2 px-2">
                                                        
                                                        <h3 class="product-title" style="color:#3a4b83;" title="{{$inventory->product_name}}">{{ Str::limit($inventory->product_name, 19) }}</h3>

                                                            <h5 class="rating">{{$inventory->category['category_name']}}</h5>
                                                            <h4 class="product-price">SKU: {{$inventory->sku}}</h4> 
                                                            <div class="col-md-6">
                                                                <div class="ratting pl-3">
                                                                    <?php
                                                                    if(!empty($inventory->ratting['average']))
                                                                    {
                                                        $rating = round($inventory->ratting['average']);
                                                        for ($i = 1; $i <= 5; $i++) :
                                                        ?>
                                                                    <span
                                                                        class="fa fa-star 
                                                            <?php if ($i <= $rating) {
                                                                echo 'checked';
                                                            } ?>">
                                                                    </span>
                                                                    <?php endfor; 
                                                                    }
                                                                    ?>
                                                                </div>
                                                            </div>
                                        <h5 class="product-price">${{$inventory->price}}</h5>
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
                                    <input type="number" class="form-control" style="width:80px;" min="0" wire:model.defer="quantity.{{ $key }}.qty">
                                </td>
                                <td class="align-middle fit-content">
                                    <button class="btn btn-success" type="button" data-bs-toggle="tooltip"
                                        title="Update"
                                        wire:click="updateProductQuantity({{ $key }})">
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
<button type="button" id="DisableAll" wire:click="toggleAllProducts(0)" style="display:none; "></button>
</div>


