<?php

namespace App\Http\Livewire\Admin;
use App\User;
use Livewire\Component;
use Livewire\WithPagination;
use Exception;
class ReferralCodes extends Component
{
    use WithPagination;
    public function render()
    {
        try{
        $referral_codes = User::select('name as f', 'l_name as l', 'referral_code as rcode')
                          ->paginate(20);
                        }catch (Exception $error) {
                            session()->flash('error', $error);
                        }
        return view('livewire.admin.referral-codes', compact('referral_codes'));
        
    }
}
