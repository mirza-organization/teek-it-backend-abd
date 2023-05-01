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
use Livewire\WithPagination;

class InventoryLivewire extends Component
{
    use WithPagination;
    public
        $category_id,
        $category,
        $product,
        $productId,
        $product_id,
        $quantity=[],
        $search = '';

    protected $paginationTheme = 'bootstrap';
   



    public function updateProductQuantity($product_id)
    {
        try {
            /* Perform some operation */
            $updated = Qty::updateChildProductQty(Auth::id(), $product_id, $this->quantity[$product_id]);
            /* Operation finished */
            if ($updated) {
                session()->flash('success', 'Data updated successfully!');
            } else {
                session()->flash('error', 'Data not updated!');
            }
        } catch (Exception $error) {
           
            session()->flash('error', $error);
        }
    }
    public function render()
    {
        $categories = Categories::all();
        if (Gate::allows('child_seller')) {
            $inventories = Products::getChildSellerProducts(Auth::id());
        }
        else {
            $parent_seller_id = Auth::id();
            if(empty($parent_seller)){
                redirect('/');
            }
            $inventories = Products::getParentSellerProductsDesc($parent_seller_id);
            // get parent seller products method should be called here 
            $featured = Products::query()->where('user_id', '=', $parent_seller_id)->where('featured', '=', 1)->orderBy('id', 'DESC')->get();
            // get featured products method should be called here 
        }
        //searching product by name and category
        if ($this->search) $inventories = $inventories->where('product_name', 'LIKE', "%{$this->search}%");
        if ($this->category_id) $inventories = $inventories->where('category_id', '=', $this->category_id);
         Gate::allows('child_seller') ? $inventories = $inventories->paginate(20) : $inventories = $inventories->paginate(9);
         $inventories->map(function($query){
            foreach ($query->quantities as $quantity):
                if ($quantity->users_id == Auth::id() && $quantity->products_id == $query->id){
                    $query->qty = $quantity->qty;
                }
            endforeach;
        });
        return view('livewire.sellers.inventory-livewire', compact('inventories', 'categories'));
    }
}
