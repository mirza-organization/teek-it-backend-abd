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
       $featured_products[]="";
        $categories = Categories::all();
        if (Gate::allows('seller')) {
            $parent_seller_id = Auth::id();
            if (empty($parent_seller)) {
                redirect('/');
            }
            
            $data = Products::where('user_id', '=', $parent_seller_id)->where('status', '=', 1)->orderBy('id', 'Desc')->paginate(9);
            $featured = Products::whereHas('user', function ($query) {
                $query->where('is_active', 1);
                })->where('user_id', '=', $parent_seller_id)
                ->where('featured', '=', 1)
                ->where('status', '=', 1)
                ->orderByDesc('id')
                ->paginate(10);
            foreach ($featured as $in) {
                $featured_products[] = Products::getProductInfo($in->id);
            }
        } elseif (Gate::allows('child_seller')) {
            $parent_seller_id = User::find(Auth::id())->parent_store_id;
        $qty = Qty::where('users_id', Auth::id())->first();
        if (!empty($qty)) {
            $data = Products::where('user_id', $parent_seller_id)
            ->join('qty', 'products.id', '=', 'qty.products_id')
            ->select('products.id as prod_id', 'products.user_id as parent_seller_id','products.category_id','products.product_name','products.price','products.feature_img','qty.id as qty_id', 'qty.users_id as child_seller_id', 'qty.qty')
            ->where('qty.users_id', Auth::id())->paginate(20);
        
        } else {
            $data =  Products::with('quantity')->where('user_id', $parent_seller_id)->paginate(20);

        }
        if ($this->search) $data = $data->where('product_name', 'LIKE', "%{$this->search}%")->paginate(9);
        if ($this->category_id) $data = $data->where('category_id', '=', $this->category_id)->paginate(9);
            // $data = Products::getChildSellerProducts(Auth::id());
        }
        
        $this->quantity = $this->populateQuantityArray($data);
        
        return view('livewire.sellers.inventory-livewire', ['data' => $data, 'categories' => $categories, 'featured_products'=>$featured_products]);
    }
}
