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
        $qty = [],
        $product_id,
        $search = '';


    public function updateQuantity($product_id)
    {
        Qty::updateChildProductQty(Auth::id(), $product_id, $this->qty[$product_id]);
    }

    public function render()
    {
        $categories = Categories::all();
        if (Gate::allows('child_seller')) {
            // $child_seller_id = Auth::id();
            // $child_seller = User::where('id', $child_seller_id)->first();
            // $parent_seller_id = $child_seller->parent_store_id;
            // $featured = Products::query()->where('user_id', $parent_seller_id)->where('featured', '=', 1)->orderBy('id', 'DESC')->get();

            // $qty = Qty::where('users_id', $child_seller_id)->first();
            // if (!empty($qty)) {
            //     $inventory = Products::where('user_id', $parent_seller_id)
            //         ->with([
            //             'quantities' => function ($q) use ($child_seller_id) {
            //                 $q->where('users_id', $child_seller_id);
            //             }
            //         ]);
            // } else {
            //     $inventory = Products::with('quantity')->where('user_id', $parent_seller_id);
            // }
            $featured = [];
            $inventory = Products::getChildSellerProducts(Auth::id());
        }
        //if not child seller then this condition will run for parent store
        else {
            $parent_seller_id = Auth::id();
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

        // $featured_products = [];
        // if (Gate::allows('seller')) {
        //     // $featured_products = [];
        //     $inventories = [];
        //     foreach ($inventory as $item) $inventories[] = Products::getProductInfo($item->id);
        // }

        foreach ($featured as $item) $featured_products[] = Products::getProductInfo($item->id);
        return view('livewire.sellers.inventory-livewire', compact('inventories', 'inventory_p', 'categories'));
    }
}
