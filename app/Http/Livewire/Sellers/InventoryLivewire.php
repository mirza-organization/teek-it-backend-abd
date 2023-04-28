<?php

namespace App\Http\Livewire\Sellers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Products;
use App\Qty;
use App\User;
use App\Categories;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryLivewire extends Component
{
    use WithPagination;
    public
        $category_id,
        $category,
        $product,
        $productId,
        $quantity=[],
        $product_id,
        $search = '';
    public function updateProductQuantity($product_id)
    {
        Qty::updateChildProductQty(Auth::id(), $product_id, $this->quantity[$product_id]);
        session()->flash('success', 'Product quasntity has been updated!');
    }
    public function render()
    {
        $categories = Categories::all();
        if (Gate::allows('child_seller')) {
            $featured = [];
            $inventory = Products::getChildSellerProducts(Auth::id());
        }
        else {
            $parent_seller_id = Auth::id();
            if(empty($parent_seller)){
                redirect('/');
            }
            $inventory = Products::getParentSellerProductsDesc($parent_seller_id);
            // get parent seller products method should be called here 
            $featured = Products::query()->where('user_id', '=', $parent_seller_id)->where('featured', '=', 1)->orderBy('id', 'DESC')->get();
            // get featured products method should be called here 
        }
        //searching product by name and category
        if ($this->search) $inventory = $inventory->where('product_name', 'LIKE', "%{$this->search}%");
        if ($this->category_id) $inventory = $inventory->where('category_id', '=', $this->category_id);
        Gate::allows('child_seller') ? $inventory = $inventory->paginate(20) : $inventory = $inventory->paginate(9);
        $inventory_p = $inventory;
        $inventories = $inventory;
        foreach ($featured as $item) $featured_products[] = Products::getProductInfo($item->id);
        return view('livewire.sellers.inventory-livewire', compact('inventories', 'inventory_p', 'categories'));
    }
}
