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
        <div class="bs-toast toast toast-placement-ex m-2 fade bg-success top-0 end-0 show " role="alert"
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
                                    @foreach ($inventory->quantities as $quantity)
                                        @if ($quantity->users_id == Auth::id() && $quantity->products_id == $inventory->id)
                                            @php  $q['qty'] = $quantity->qty;             @endphp
                                        @endif
                                    @endforeach


                                    <input class="form-control qtyInput" min="0" style="width:80px;"
                                        type="number" id="Qty" value="{{ $q['qty'] }}"
                                        wire:model.defer="quantity.{{ $inventory->id }}">
                                </td>
                                <td class="align-middle fit-content">
                                    <button class="btn btn-success" type="button" data-bs-toggle="tooltip"
                                        title="Update"
                                        wire:click="updateProductQuantity('{{ $inventory->id }}', 'quantity.{{ $inventory->id }}')">
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
