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
    padding: 80px 0 54px;
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

        {{-- <div class="bs-toast toast toast-placement-ex m-2 fade bg-success top-0 end-0 show" role="alert"
            aria-live="assertive" aria-atomic="true" data-delay="2000">
            <div class="toast-header">
                <i class="bx bx-bell me-2"></i>
                <div class="me-auto fw-semibold">Success</div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
            </div>
            <div class="toast-body">
                
            </div>
        </div> --}}
    @endif

    <div class="row">
        <div class="col-12 col-sm-6 col-md-4">
            <h4 class="py-4 my-1">Inventory</h4>
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
                    <button type="button" class="btn btn-warning my-4 p-1 w-100 mx-1 rounded" title="Enable All">
                        <i class="fa fa-toggle-on"></i>
                    </button>
                    <button type="button" class="btn btn-danger my-4 p-1 w-100 mx-1" title="Disable All">
                        <i class="fas fa-ban"></i>
                    </button>
                    <button type="button" class="btn btn-primary my-4 p-1 w-100 mx-1" title="Add New">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-primary my-4 p-1 w-100 mx-1" title="Add Bulk">
                        <i class="fas fa-plus"></i> <i class="fas fa-boxes"></i>
                    </button>
                </div>
            </div>
        </div>
        
    </div>

    
            <section class="section-products">
                <div class="container">
                        <div class="row">
                                <!-- Single Product -->
                                <div class="col-md-6 col-lg-4 col-xl-3  ">
                                        <div id="product-1" class="single-product bg-white">
                                                <div class="part-1">
                                                        <ul>
                                                                <li><a href="#" ><i class="fa fa-ban"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-edit"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-star"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-toggle-on"></i></a></li>
                                                        </ul>
                                                </div>
                                                <div class="part-2 px-2">
                                                        <h3 class="product-title">Here Product Title</h3>
                                                        <h4 class="product-old-price">SKU</h4>
                                                        <h4 class="product-price">$49.99</h4>
                                                </div>
                                        </div>
                                </div>
                                <!-- Single Product -->
                                <div class="col-md-6 col-lg-4 col-xl-3 ">
                                        <div id="product-2" class="single-product bg-white">
                                                <div class="part-1">
                                                        <span class="discount">15% off</span>
                                                        <ul>
                                                                <li><a href="#"><i class="fa fa-ban"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-edit"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-star"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-toggle-on"></i></a></li>
                                                        </ul>
                                                </div>
                                                <div class="part-2 px-2">
                                                        <h3 class="product-title">Here Product Title</h3>
                                                        <h4 class="product-price">$49.99</h4>
                                                </div>
                                        </div>
                                </div>
                                <!-- Single Product -->
                                <div class="col-md-6 col-lg-4 col-xl-3 ">
                                        <div id="product-3" class="single-product bg-white">
                                                <div class="part-1">
                                                        <ul>
                                                                <li><a href="#"><i class="fa fa-ban"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-edit"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-star"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-toggle-on"></i></a></li>
                                                        </ul>
                                                </div>
                                                <div class="part-2 px-2">
                                                        <h3 class="product-title">Here Product Title</h3>
                                                        <h4 class="product-old-price">SKU: 22424</h4>
                                                        <h4 class="product-price">$49.99</h4>
                                                </div>
                                        </div>
                                </div>
                                <!-- Single Product -->
                                <div class="col-md-6 col-lg-4 col-xl-3">
                                        <div id="product-4" class="single-product bg-white">
                                                <div class="part-1">
                                                        <span class="new">new</span>
                                                        <ul>
                                                                <li><a href="#"><i class="fa fa-ban"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-edit"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-star"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-toggle-on"></i></a></li>
                                                        </ul>
                                                </div>
                                                <div class="part-2 px-2">
                                                        <h3 class="product-title">Here Product Title</h3>
                                                        <h4 class="product-price">$49.99</h4>
                                                </div>
                                        </div>
                                </div>
                                <!-- Single Product -->
                                <div class="col-md-6 col-lg-4 col-xl-3 ">
                                        <div id="product-1" class="single-product bg-white">
                                                <div class="part-1">
                                                        <ul>
                                                                <li><a href="#"><i class="fa fa-ban"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-edit"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-star"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-toggle-on"></i></a></li>
                                                        </ul>
                                                </div>
                                                <div class="part-2 px-2">
                                                        <h3 class="product-title">Here Product Title</h3>
                                                        <h4 class="product-old-price">SKU: 22424</h4>
                                                        <h4 class="product-price">$49.99</h4>
                                                </div>
                                        </div>
                                </div>
                                <!-- Single Product -->
                                <div class="col-md-6 col-lg-4 col-xl-3">
                                        <div id="product-2" class="single-product bg-white">
                                                <div class="part-1">
                                                        <span class="discount">15% off</span>
                                                        <ul>
                                                                <li><a href="#"><i class="fa fa-ban"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-edit"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-star"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-toggle-on"></i></a></li>
                                                        </ul>
                                                </div>
                                                <div class="part-2 px-2">
                                                        <h3 class="product-title">Here Product Title</h3>
                                                        <h4 class="product-price">$49.99</h4>
                                                </div>
                                        </div>
                                </div>
                                <!-- Single Product -->
                                <div class="col-md-6 col-lg-4 col-xl-3">
                                        <div id="product-3" class="single-product bg-white">
                                                <div class="part-1">
                                                        <ul>
                                                                <li><a href="#"><i class="fa fa-ban"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-edit"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-star"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-toggle-on"></i></a></li>
                                                        </ul>
                                                </div>
                                                <div class="part-2 px-2">
                                                        <h3 class="product-title">Here Product Title</h3>
                                                        <h4 class="product-old-price">SKU: 22424</h4>
                                                        <h4 class="product-price">$49.99</h4>
                                                </div>
                                        </div>
                                </div>
                                <!-- Single Product -->
                                <div class="col-md-6 col-lg-4 col-xl-3">
                                        <div id="product-4" class="single-product bg-white">
                                                <div class="part-1">
                                                        <span class="new">new</span>
                                                        <ul>
                                                                <li><a href="#"><i class="fa fa-ban"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-star"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-star"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-toggle-on"></i></a></li>
                                                        </ul>
                                                </div>
                                                <div class="part-2 px-2">
                                                        <h3 class="product-title">Here Product Title</h3>
                                                        <h4 class="product-price">$49.99</h4>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>
        </section>
        
</div>
