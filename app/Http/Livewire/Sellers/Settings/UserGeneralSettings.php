<?php

namespace App\Http\Livewire\Sellers\Settings;
use App\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UserGeneralSettings extends Component
{
    public function render()
    {
        $user = User::find(Auth::id());
        $business_hours = $user->business_hours;
        $address = $user->address_1;
        $business_location = $user->business_location;
        return view('livewire.sellers.settings.user-general-settings', compact('business_hours', 'address', 'business_location'));
        
    }
}
