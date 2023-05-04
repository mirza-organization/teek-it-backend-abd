<?php

namespace App\Http\Livewire\Sellers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Products;
use App\Qty;
use App\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use App\Categories;
use Illuminate\Contracts\Auth\Access\Gate as AccessGate;
use Livewire\WithPagination;

class InventoryLivewire extends Component
{
    use WithPagination;
    public
        $category_id,
        $category,
        $product,
        $product_id,
        $quantity = [],
        $inventories,
        $search = '';

    protected $paginationTheme = 'bootstrap';

    public function updateProductQuantity($index)
    {
        try {
            /* Perform some operation */
            $updated = Qty::updateChildProductQty($this->quantity[$index]);
            /* Operation finished */
            sleep(1);
            if ($updated) {
                session()->flash('success', 'Data updated successfully!');
            } else {
                session()->flash('error', 'Data not updated!');
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

    public function populateQuantityArray($data){
       return $data->map(function ($product) {
            return [
                'prod_id' => $product->prod_id,
                'parent_seller_id' => $product->parent_seller_id,
                'qty_id' => $product->qty_id,
                'child_seller_id' => $product->child_seller_id,
                'qty' => $product->qty,
            ];
        });
    }

    public function render()
    {
        $categories = Categories::all();
        if (Gate::allows('seller')) {
            $parent_seller_id = Auth::id();
            if (empty($parent_seller)) {
                redirect('/');
            }
            $data = Products::getParentSellerProductsDesc($parent_seller_id);
            $featured = Products::query()->where('user_id', '=', $parent_seller_id)->where('featured', '=', 1)->orderBy('id', 'DESC')->get();
        } elseif (Gate::allows('child_seller')) {
            $data = Products::getChildSellerProducts(Auth::id());
            // $data = $data->paginate(20);
            // dd($data);
        }
        //searching product by name or category
        if ($this->search) $data = $data->where('product_name', 'LIKE', "%{$this->search}%");
        if ($this->category_id) $data = $data->where('category_id', '=', $this->category_id);

        $data =  Gate::allows('child_seller') ? $data->paginate(20) : $data->paginate(9);

        $this->quantity = $this->populateQuantityArray($data);

        return view('livewire.sellers.inventory-livewire2', ['data' => $data, 'categories' => $categories]);
    }
}
