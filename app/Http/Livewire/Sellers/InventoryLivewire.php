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
        $category,
        $product,
        $productId,
       $updatedQuantity;
       
    
        public function updateQuantity($id, $qty)
        {
            Qty::where('products_id', $id)
            ->update(['qty' => $qty]);
        }
   
    public function render()
    {
        if (Gate::allows('seller') || Gate::allows('child_seller')) {
            if (Gate::allows('child_seller')) {
                $child_seller_id = Auth::id();
                $qty = Qty::where('users_id', $child_seller_id)->first();
                $child_seller = User::where('id', $child_seller_id)->first();
                $parent_seller_id = $child_seller->parent_store_id;
                $featured = Products::query()->where('user_id', $parent_seller_id)->where('featured', '=', 1)->orderBy('id', 'DESC')->get();
                if (!empty($qty)) {
                    $inventory = Products::where('user_id', $parent_seller_id)
                        ->with([
                            'quantities' => function ($q) use ($child_seller_id) {
                                $q->where('users_id', $child_seller_id);
                            }
                        ]);
                } else {
                    $inventory = Products::with('quantity')->where('user_id', $parent_seller_id);
                }
            }
            //if not child seller then this condition will run for parent store
            else {
                $parent_seller_id = Auth::id();
                $inventory = Products::query()->where('user_id', '=', $parent_seller_id)->orderBy('id', 'DESC');
                $featured = Products::query()->where('user_id', '=', $parent_seller_id)->where('featured', '=', 1)->orderBy('id', 'DESC')->get();
            }
            //searching product by name and category
            if ($this->product) $inventory = $inventory->where('product_name', 'LIKE', "%{$this->product}%");
            if ($this->category) $inventory = $inventory->where('category_id', '=', $this->category);
            $categories = Categories::all();
            Gate::allows('child_seller') ? $inventory = $inventory->paginate(20) : $inventory = $inventory->paginate(9);
            $inventory_p = $inventory;
            $inventories = $inventory;
            $featured_products = [];
            if (Gate::allows('seller')) {
                $featured_products = [];
                $inventories = [];
                foreach ($inventory as $in) {
                    $inventories[] = Products::getProductInfo($in->id);
                }
            }
            foreach ($featured as $in) {
                $featured_products[] = Products::getProductInfo($in->id);
            }
            return view('livewire.sellers.inventory-livewire', compact('inventories', 'featured_products', 'inventory_p', 'categories'));
        } else {
            abort(404);
        }
       // return view('livewire.sellers.inventory-livewire');
    }
}
