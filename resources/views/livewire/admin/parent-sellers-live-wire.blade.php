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
    {{-- ************************************ Add Employee Model ************************************ --}}
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
                        <div class="row">
                            <div class="col mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" id="name" wire:model.lazy="name" class="form-control"
                                    placeholder="Name" />
                                <small class="text-danger">
                                    @error('name')
                                        {{ $message }}
                                    @enderror
                                </small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" wire:model.lazy="email" class="form-control"
                                    placeholder="Email" />
                                <small class="text-danger">
                                    @error('email')
                                        {{ $message }}
                                    @enderror
                                </small>
                            </div>
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" wire:model.lazy="password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="password" required />
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                            </div>
                            <small class="text-danger">
                                @error('password')
                                    {{ $message }}
                                @enderror
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                            wire:click="resetModal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div> --}}
    {{-- ************************************ Edit Employee Model ************************************ --}}
    <div wire:ignore.self class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="resetModal"></button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" id="name" wire:model.lazy="name" class="form-control"
                                    placeholder="Name" />
                                <small class="text-danger">
                                    @error('name')
                                        {{ $message }}
                                    @enderror
                                </small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" wire:model.lazy="email" class="form-control"
                                    placeholder="Email" />
                                <small class="text-danger">
                                    @error('email')
                                        {{ $message }}
                                    @enderror
                                </small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" wire:model.lazy="password" class="form-control"
                                    placeholder="Enter new password" />
                                <small class="text-danger">
                                    @error('password')
                                        {{ $message }}
                                    @enderror
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                            wire:click="resetModal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary" wire:click.prevent="submitForm('edit')"
                            wire:loading.class="btn-dark" wire:loading.class.remove="btn-primary"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove>Update</span>
                            <span wire:loading>
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- ************************************ Delete Employee Model ************************************ --}}
    <div wire:ignore.self class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel"
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
                                <span class="spinner-border spinner-border-sm" role="status"
                                    aria-hidden="true"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-sm-6 col-md-6">
            <h4 class="py-4 my-1">Parent Sellers</h4>
        </div>
        <div class="col-12 col-sm-6 col-md-5">
            <div class="input-group py-4 my-2">
                <input type="text" wire:model.debounce.500ms="search" class="form-control py-3"
                    placeholder="Search here...">
                {{-- <button class="btn btn-primary" type="button"><i class='bx bx-search-alt'></i></button> --}}
            </div>
        </div>
        <div class="col-12 col-md-1">
            <button type="button" class="btn btn-danger my-3 py-3 w-100" data-bs-toggle="modal"
                data-bs-target="#addModal">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </div>
    <div class="container">
        <div class="row">
            @forelse ($data as $single_index)
                <div class="col-sm-12 col-md-6 col-lg-4 mb-4">
                    <div class="card text-white card-has-bg click-col"
                        @if ($single_index->user_img) style="background-image:url('{{ config('constants.BUCKET') . $single_index->user_img }}');"
                        @else
                        style="background-image:url('{{ asset('/res/res/img/store_logo.png') }}');" @endif>
                        <div class="card-img-overlay d-flex flex-column">
                            <div class="card-body">
                                <button type="button" class="btn btn-primary" title="Show detail information"
                                    data-bs-toggle="modal" data-bs-target="#editModal"
                                    wire:click="renderEditModal({{ $single_index->id }})">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                <a href="{{ route('admin.orders', ['store_id' => $single_index->id]) }}"
                                    class="btn btn-dark" title="Show orders">
                                    <i class="fas fa-luggage-cart"></i>
                                </a>
                                {{-- <h4 class="card-title mt-0 ">
                                    <a class="text-white" herf="#">Grace Technologies</a>
                                </h4> --}}
                            </div>
                            <div class="card-footer">
                                <div class="media">
                                    {{-- <img class="mr-3 rounded-circle" src="{{ asset('storage/images/dummyemp.png') }}"
                                        alt="Generic placeholder image" style="max-width:50px"> --}}
                                    <div class="media-body">
                                        <h6 class="my-0 text-white d-block">{{ $single_index->business_name }}</h6>
                                        <small>{{ $single_index->email }}</small> <br>
                                        <small><i class="far fa-clock"></i> Joining:
                                            {{ $single_index->created_at }}</small>
                                    </div>
                                    <div class="form-check form-switch mt-3">
                                        <input type="checkbox" class="form-check-input"
                                            value="{{ $single_index->id }}"
                                            wire:click="changeStatus({{ $single_index->id }}, {{ $single_index->is_active }})"
                                            role="switch" {{ ($single_index->is_active === 1) ? 'checked' : '' }}>
                                        <label class="form-check-label">
                                            {{ ($single_index->is_active === 1) ? 'Active' : 'Blocked' }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <h1 class="text-dark">No Sellers Found :(</h1>
            @endforelse
        </div>
        <div class="row">
            {{ $data->links() }}
        </div>
    </div>

</div>
